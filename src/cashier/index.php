<?php
session_start();
require_once '../../config/connect.php';

// Inisialisasi keranjang
$cart = $_SESSION['cart'] ?? [];
$cartTotal = 0;

// --- Fungsi format rupiah ---
function idr($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

// --- Cari Produk ---
$products = [];
$searchTerm = $_GET['search_product'] ?? '';
$displaySearchTerm = $searchTerm; // Untuk ditampilkan di input
if ($searchTerm !== '') {
    $searchPattern = '%' . $searchTerm . '%';
    $stmt = $conn->prepare("SELECT product_id, product_name, price, stock FROM products WHERE product_name LIKE ? AND stock > 0 LIMIT 15");
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query("SELECT product_id, product_name, price, stock FROM products WHERE stock > 0 ORDER BY product_name ASC LIMIT 15");
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

// --- Tambah ke Cart ---
if (isset($_POST['add_to_cart'])) {
    $productId = (int)$_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = (float)$_POST['product_price'];

    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($stock);
    $stmt->fetch();
    $stmt->close();

    if ($stock <= 0) {
        echo "<script>alert('Produk habis stok!');</script>";
    } else {
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                if ($item['quantity'] < $stock) {
                    $item['quantity']++;
                    $item['subtotal'] = $item['quantity'] * $item['price'];
                } else {
                    echo "<script>alert('Jumlah melebihi stok tersedia!');</script>";
                }
                $found = true;
                break;
            }
        }
        unset($item);
        if (!$found) {
            $cart[] = [
                'product_id' => $productId,
                'product_name' => $productName,
                'price' => $productPrice,
                'quantity' => 1,
                'subtotal' => $productPrice
            ];
        }
        $_SESSION['cart'] = $cart;
    }
}

// --- Update Quantity ---
if (isset($_POST['update_quantity'])) {
    $productId = (int)$_POST['product_id'];
    $newQuantity = max(0, (int)$_POST['quantity']);

    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($stock);
    $stmt->fetch();
    $stmt->close();

    foreach ($cart as &$item) {
        if ($item['product_id'] == $productId) {
            if ($newQuantity == 0) {
                $cart = array_filter($cart, fn($i) => $i['product_id'] != $productId);
            } elseif ($newQuantity > $stock) {
                echo "<script>alert('Jumlah melebihi stok tersedia ($stock)!');</script>";
            } else {
                $item['quantity'] = $newQuantity;
                $item['subtotal'] = $item['quantity'] * $item['price'];
            }
            break;
        }
    }
    unset($item);
    $_SESSION['cart'] = array_values($cart); // Re-index array
}

// --- Hapus dari Cart ---
if (isset($_POST['remove_from_cart'])) {
    $productId = (int)$_POST['product_id'];
    $cart = array_filter($cart, fn($i) => $i['product_id'] != $productId);
    $_SESSION['cart'] = array_values($cart); // Re-index array
}

// --- Hitung Total Keranjang ---
$cartTotal = array_sum(array_column($cart, 'subtotal'));

// --- Terapkan Voucher ---
$voucherUsed = null;
$discountAmount = 0;
$finalTotal = $cartTotal;

if (isset($_POST['apply_voucher'])) {
    $voucherCode = trim($_POST['voucher_code']);
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE voucher_code = ? AND expiration_date >= CURDATE()");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();

    if ($voucher) {
        $_SESSION['voucher'] = $voucher;
        echo "<script>alert('Voucher berhasil diterapkan!');</script>";
    } else {
        unset($_SESSION['voucher']);
        echo "<script>alert('Voucher tidak valid atau sudah kadaluarsa!');</script>";
    }
}

// Jika ada voucher aktif di session
if (isset($_SESSION['voucher'])) {
    $voucherUsed = $_SESSION['voucher'];
    if ($voucherUsed['discount_type'] === 'percentage') {
        $discountAmount = $cartTotal * ($voucherUsed['discount_value'] / 100);
    } elseif ($voucherUsed['discount_type'] === 'fixed') {
        $discountAmount = $voucherUsed['discount_value'];
    }
    $finalTotal = max(0, $cartTotal - $discountAmount);
}

// --- Proses Transaksi ---
if (isset($_POST['process_transaction']) && !empty($cart)) {
    $conn->begin_transaction();
    try {
        $cashierId = 1; // Ganti dengan session user login
        $transactionDate = date('Y-m-d H:i:s');
        $totalAmount = $cartTotal;
        $finalAmount = $finalTotal;
        $voucherId = $voucherUsed['voucher_id'] ?? null;
        $paymentMethod = 'cash';

        $stmt = $conn->prepare("
            INSERT INTO transactions (cashier_id, voucher_id, transaction_date, total_amount, final_amount, payment_method, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iisdds", $cashierId, $voucherId, $transactionDate, $totalAmount, $finalAmount, $paymentMethod);
        $stmt->execute();
        $transactionId = $stmt->insert_id;
        $stmt->close();

        // Detail transaksi + update stok
        foreach ($cart as $item) {
            $stmt = $conn->prepare("
                INSERT INTO transaction_details (transaction_id, product_id, quantity, subtotal, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("iiid", $transactionId, $item['product_id'], $item['quantity'], $item['subtotal']);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $_SESSION['cart'] = [];
        unset($_SESSION['voucher']);
        echo "<script>alert('✅ Transaksi berhasil disimpan!');window.location.href='index.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('❌ Gagal: " . addslashes($e->getMessage()) . "');</script>";
    }
}

$conn->close();
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cashier - Indomaret POS</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link href="../../output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen text-gray-800">
  <div class="flex h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-white to-slate-50 p-6 hidden md:flex flex-col justify-between shadow-xl">
      <div>
        <a href="#" class="flex items-center gap-3 mb-10">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-500 text-white font-bold text-lg flex items-center justify-center shadow-lg">IM</div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Indomaret</h1>
          </div>
        </a>

        <nav class="space-y-1 text-sm">
          <a href="../../index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
            </svg>
            <span>Dashboard</span>
          </a>
          <a href="../cashier/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-teal-500 text-white font-medium shadow-md transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
            </svg>
            <span>Cashier</span>
          </a>
          <a href="../products/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" />
            </svg>
            <span>Products</span>
          </a>
          <a href="../transactions/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
            </svg>
            <span>Transactions</span>
          </a>
          <a href="../admin/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
            <span>Admin</span>
          </a>
        </nav>
      </div>

      <div class="text-xs text-center text-gray-400 mt-10">
        © <?= date('Y') ?> Indomaret POS
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white/80 backdrop-blur-sm p-5 shadow-sm border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Cashier System</h2>
            <p class="text-sm text-gray-500 mt-1">Process customer transactions</p>
          </div>
          <div class="flex items-center gap-4">
            <div class="hidden sm:block text-right">
              <div class="text-xs text-gray-400">Operator</div>
              <div class="text-sm font-medium text-gray-700">Admin Toko</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-teal-500 text-white flex items-center justify-center font-semibold shadow-md">A</div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-auto p-6">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Product Search Section -->
            <section class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-lg flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                  </svg>
                  Product Search
                </h3>
                <span class="text-xs bg-teal-100 text-teal-700 px-3 py-1 rounded-full font-medium"><?= count($products) ?> items</span>
              </div>

              <!-- Search Form -->
              <form action="" method="GET" class="mb-6">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <input 
                    type="text" 
                    name="search_product" 
                    placeholder="Search product by name..." 
                    value="<?= htmlspecialchars($displaySearchTerm) ?>"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
                  >
                </div>
              </form>

              <!-- Product Table -->
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="border-b border-gray-200">
                    <tr class="text-gray-600 text-xs uppercase tracking-wider">
                      <th class="pb-3 text-left font-semibold">Product Name</th>
                      <th class="pb-3 text-right font-semibold">Price</th>
                      <th class="pb-3 text-right font-semibold">Stock</th>
                      <th class="pb-3 text-center font-semibold">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($products)): ?>
                      <tr>
                        <td colspan="4" class="py-12 text-center text-gray-400">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                          </svg>
                          No products found
                        </td>
                      </tr>
                    <?php else: foreach ($products as $p): ?>
                      <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all">
                        <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($p['product_name']) ?></td>
                        <td class="py-3 text-right text-teal-600 font-semibold"><?= idr($p['price']) ?></td>
                        <td class="py-3 text-right">
                          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $p['stock'] > 10 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' ?>">
                            <?= $p['stock'] ?>
                          </span>
                        </td>
                        <td class="py-3 text-center">
                          <form action="" method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="product_name" value="<?= $p['product_name'] ?>">
                            <input type="hidden" name="product_price" value="<?= $p['price'] ?>">
                            <button 
                              type="submit" 
                              name="add_to_cart" 
                              class="inline-flex items-center gap-1 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-4 py-2 rounded-lg text-xs font-medium hover:shadow-lg transition-all"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                              </svg>
                              Add
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>
            </section>

            <!-- Cart Section -->
            <aside class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
              <h3 class="font-semibold mb-4 text-gray-800 text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                </svg>
                Shopping Cart
                <span class="ml-auto text-xs bg-teal-100 text-teal-700 px-2.5 py-1 rounded-full font-medium"><?= count($cart) ?></span>
              </h3>

              <!-- Cart Items -->
              <div class="space-y-3 mb-6 max-h-60 overflow-y-auto">
                <?php if (empty($cart)): ?>
                  <div class="text-center py-8 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-sm">Cart is empty</p>
                  </div>
                <?php else: foreach ($cart as $item): ?>
                  <div class="bg-gray-50 rounded-xl p-3 border border-gray-200">
                    <div class="flex items-start justify-between mb-2">
                      <div class="flex-1">
                        <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div class="text-xs text-teal-600 font-medium"><?= idr($item['price']) ?></div>
                      </div>
                      <div class="flex items-center gap-2">
                        <div class="text-sm font-bold text-gray-900"><?= idr($item['subtotal']) ?></div>
                        <form action="" method="POST" class="inline">
                          <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                          <button 
                            type="submit" 
                            name="remove_from_cart"
                            onclick="return confirm('Remove this item from cart?')"
                            class="text-red-500 hover:text-red-700 transition-all"
                            title="Remove from cart"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                          </button>
                        </form>
                      </div>
                    </div>
                    <form action="" method="POST" class="flex items-center gap-2">
                      <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                      <div class="flex items-center gap-2 flex-1">
                        <label class="text-xs text-gray-600">Qty:</label>
                        <input 
                          type="number" 
                          name="quantity" 
                          value="<?= $item['quantity'] ?>" 
                          min="1" 
                          class="w-16 p-1.5 border border-gray-300 rounded-lg text-center text-sm focus:ring-2 focus:ring-teal-500 outline-none"
                        >
                      </div>
                      <button 
                        type="submit" 
                        name="update_quantity" 
                        class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                      >
                        Update
                      </button>
                    </form>
                  </div>
                <?php endforeach; endif; ?>
              </div>

              <!-- Voucher Section -->
              <div class="border-t border-gray-200 pt-4 mb-4">
                <form method="POST" class="space-y-2">
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide">Promo Code</label>
                  <div class="flex gap-2">
                    <input 
                      type="text" 
                      name="voucher_code" 
                      placeholder="Enter code" 
                      value="<?= htmlspecialchars($voucherUsed['voucher_code'] ?? '') ?>" 
                      class="flex-1 p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none"
                    >
                    <button 
                      type="submit" 
                      name="apply_voucher" 
                      class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-2.5 rounded-lg hover:shadow-lg transition-all text-sm font-medium"
                    >
                      Apply
                    </button>
                  </div>
                </form>
              </div>

              <!-- Total Summary -->
              <div class="border-t border-gray-200 pt-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Subtotal</span>
                  <span class="font-medium"><?= idr($cartTotal) ?></span>
                </div>
                <?php if ($voucherUsed): ?>
                <div class="flex justify-between text-sm">
                  <span class="text-green-600 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                    </svg>
                    Discount (<?= htmlspecialchars($voucherUsed['voucher_code']) ?>)
                  </span>
                  <span class="text-green-600 font-semibold">-<?= idr($discountAmount) ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-200">
                  <span>Total</span>
                  <span class="text-teal-600"><?= idr($finalTotal) ?></span>
                </div>
              </div>

              <!-- Process Transaction Button -->
              <form action="" method="POST" class="mt-6">
                <button 
                  type="submit" 
                  name="process_transaction"
                  <?= empty($cart) ? 'disabled' : '' ?>
                  class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-xl text-base font-bold hover:shadow-xl transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Process Transaction
                </button>
              </form>

              <!-- Additional Info -->
              <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-100">
                <div class="flex items-start gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  <div class="text-xs text-blue-700">
                    <p class="font-semibold mb-1">Payment Method: Cash</p>
                    <p class="text-blue-600">Stock will be automatically updated after transaction</p>
                  </div>
                </div>
              </div>
            </aside>

          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>