<?php
require_once '../../config/connect.php';

// === PRODUCT CRUD ===
$products = [];
$editProduct = null;

if (isset($_POST['submit_product'])) {
    $productName = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $productId = $_POST['product_id'] ?? null;

    if ($productId) {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, category=?, price=?, stock=?, updated_at=NOW() WHERE product_id=?");
        $stmt->bind_param("ssdis", $productName, $category, $price, $stock, $productId);
    } else {
        $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, stock, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssdi", $productName, $category, $price, $stock);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $_GET['delete_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->bind_param("i", $_GET['edit_id']);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// === VOUCHER CRUD ===
$vouchers = [];
$editVoucher = null;

if (isset($_POST['submit_voucher'])) {
    $voucherCode = strtoupper(trim($_POST['voucher_code']));
    $discountType = $_POST['discount_type'];
    $discountValue = $_POST['discount_value'];
    $expirationDate = $_POST['expiration_date'];
    $voucherId = $_POST['voucher_id'] ?? null;

    if ($voucherId) {
        $stmt = $conn->prepare("UPDATE vouchers SET voucher_code=?, discount_type=?, discount_value=?, expiration_date=?, updated_at=NOW() WHERE voucher_id=?");
        $stmt->bind_param("ssdsi", $voucherCode, $discountType, $discountValue, $expirationDate, $voucherId);
    } else {
        $stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, discount_type, discount_value, expiration_date, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssds", $voucherCode, $discountType, $discountValue, $expirationDate);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

if (isset($_GET['delete_voucher'])) {
    $stmt = $conn->prepare("UPDATE vouchers SET deleted_at=NOW() WHERE voucher_id=?");
    $stmt->bind_param("i", $_GET['delete_voucher']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

if (isset($_GET['edit_voucher'])) {
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE voucher_id=?");
    $stmt->bind_param("i", $_GET['edit_voucher']);
    $stmt->execute();
    $editVoucher = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$products = $conn->query("SELECT * FROM products ORDER BY product_id DESC")->fetch_all(MYSQLI_ASSOC);
$vouchers = $conn->query("SELECT * FROM vouchers WHERE deleted_at IS NULL ORDER BY voucher_id DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();

function idr($value) { return 'Rp ' . number_format($value, 0, ',', '.'); }
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products & Vouchers - Indomaret POS</title>
    <link href="../../output.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
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
                <a href="../cashier/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                    </svg>
                    <span>Cashier</span>
                </a>
                <a href="../products/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-teal-500 text-white font-medium shadow-md transition-all">
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
                    <h2 class="text-2xl font-bold text-gray-900">Product & Voucher Management</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage inventory and promotions</p>
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
            <div class="max-w-7xl mx-auto space-y-8">

                <!-- PRODUCT SECTION -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Product Form -->
                    <section class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 text-lg">
                                <?= $editProduct ? 'Edit Product' : 'Add New Product' ?>
                            </h3>
                        </div>
                        <form action="" method="POST" class="space-y-4">
                            <?php if ($editProduct): ?>
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($editProduct['product_id']) ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name</label>
                                <input type="text" name="product_name" value="<?= htmlspecialchars($editProduct['product_name'] ?? '') ?>" placeholder="Enter product name" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                <input type="text" name="category" value="<?= htmlspecialchars($editProduct['category'] ?? '') ?>" placeholder="e.g., Snacks, Beverages" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Price (Rp)</label>
                                <input type="number" name="price" value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" step="0.01" placeholder="0" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Stock</label>
                                <input type="number" name="stock" value="<?= htmlspecialchars($editProduct['stock'] ?? '') ?>" placeholder="0" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>

                            <button type="submit" name="submit_product" class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold py-3 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                <?= $editProduct ? '✓ Update Product' : '+ Add Product' ?>
                            </button>

                            <?php if ($editProduct): ?>
                                <a href="index.php" class="block w-full text-center bg-gray-200 text-gray-700 font-medium py-3 rounded-xl hover:bg-gray-300 transition-all">
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </form>
                    </section>

                    <!-- Product List -->
                    <section class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-800 text-lg flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                                </svg>
                                Product List
                            </h3>
                            <span class="text-xs bg-teal-100 text-teal-700 px-3 py-1 rounded-full font-medium"><?= count($products) ?> items</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200">
                                    <tr class="text-gray-600 text-xs uppercase tracking-wider">
                                        <th class="pb-3 text-left font-semibold">ID</th>
                                        <th class="pb-3 text-left font-semibold">Product Name</th>
                                        <th class="pb-3 text-left font-semibold">Category</th>
                                        <th class="pb-3 text-right font-semibold">Price</th>
                                        <th class="pb-3 text-right font-semibold">Stock</th>
                                        <th class="pb-3 text-center font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="6" class="py-12 text-center text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                                No products found
                                            </td>
                                        </tr>
                                    <?php else: foreach ($products as $product): ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all">
                                            <td class="py-3 text-xs font-mono text-gray-600">#<?= $product['product_id'] ?></td>
                                            <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($product['product_name']) ?></td>
                                            <td class="py-3">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                    <?= htmlspecialchars($product['category']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 text-right text-teal-600 font-semibold"><?= idr($product['price']) ?></td>
                                            <td class="py-3 text-right">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $product['stock'] > 10 ? 'bg-green-100 text-green-700' : ($product['stock'] > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') ?>">
                                                    <?= htmlspecialchars($product['stock']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="?edit_id=<?= $product['product_id'] ?>" class="inline-flex items-center gap-1 bg-yellow-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-yellow-600 transition-all">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <a href="?delete_id=<?= $product['product_id'] ?>" onclick="return confirm('Delete this product?');" class="inline-flex items-center gap-1 bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-600 transition-all">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                        Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- VOUCHER SECTION -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Voucher Form -->
                    <section class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 text-lg">
                                <?= $editVoucher ? 'Edit Voucher' : 'Add New Voucher' ?>
                            </h3>
                        </div>
                        <form method="POST" class="space-y-4">
                            <?php if ($editVoucher): ?>
                                <input type="hidden" name="voucher_id" value="<?= $editVoucher['voucher_id'] ?>">
                            <?php endif; ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Voucher Code</label>
                                <input type="text" name="voucher_code" value="<?= htmlspecialchars($editVoucher['voucher_code'] ?? '') ?>" placeholder="e.g., DISKON10" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none uppercase">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Discount Type</label>
                                <select name="discount_type" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                                    <option value="percentage" <?= isset($editVoucher) && $editVoucher['discount_type']=='percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                    <option value="fixed" <?= isset($editVoucher) && $editVoucher['discount_type']=='fixed' ? 'selected' : '' ?>>Fixed Amount (Rp)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Discount Value</label>
                                <input type="number" step="0.01" name="discount_value" value="<?= htmlspecialchars($editVoucher['discount_value'] ?? '') ?>" placeholder="0" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Expiration Date</label>
                                <input type="date" name="expiration_date" value="<?= htmlspecialchars($editVoucher['expiration_date'] ?? '') ?>" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>
                            <button type="submit" name="submit_voucher" class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold py-3 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                <?= $editVoucher ? '✓ Update Voucher' : '+ Add Voucher' ?>
                            </button>

                            <?php if ($editVoucher): ?>
                                <a href="index.php" class="block w-full text-center bg-gray-200 text-gray-700 font-medium py-3 rounded-xl hover:bg-gray-300 transition-all">
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </form>
                    </section>

                    <!-- Voucher List -->
                    <section class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-800 text-lg flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 100 4v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 100-4V6z" />
                                </svg>
                                Voucher List
                            </h3>
                            <span class="text-xs bg-pink-100 text-pink-700 px-3 py-1 rounded-full font-medium"><?= count($vouchers) ?> active</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200">
                                    <tr class="text-gray-600 text-xs uppercase tracking-wider">
                                        <th class="pb-3 text-left font-semibold">ID</th>
                                        <th class="pb-3 text-left font-semibold">Code</th>
                                        <th class="pb-3 text-center font-semibold">Type</th>
                                        <th class="pb-3 text-right font-semibold">Value</th>
                                        <th class="pb-3 text-center font-semibold">Expiration</th>
                                        <th class="pb-3 text-center font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($vouchers)): ?>
                                        <tr>
                                            <td colspan="6" class="py-12 text-center text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                                </svg>
                                                No vouchers available
                                            </td>
                                        </tr>
                                    <?php else: foreach ($vouchers as $v): 
                                        $isExpired = strtotime($v['expiration_date']) < time();
                                    ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all <?= $isExpired ? 'opacity-50' : '' ?>">
                                            <td class="py-3 text-xs font-mono text-gray-600">#<?= $v['voucher_id'] ?></td>
                                            <td class="py-3">
                                                <span class="inline-flex items-center gap-1 font-bold text-gray-800 bg-gradient-to-r from-pink-100 to-purple-100 px-3 py-1 rounded-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-600" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                                                    </svg>
                                                    <?= htmlspecialchars($v['voucher_code']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $v['discount_type']=='percentage' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                                                    <?= $v['discount_type']=='percentage' ? 'Percentage' : 'Fixed' ?>
                                                </span>
                                            </td>
                                            <td class="py-3 text-right font-bold text-pink-600">
                                                <?= $v['discount_type']=='percentage' ? $v['discount_value'].'%' : idr($v['discount_value']) ?>
                                            </td>
                                            <td class="py-3 text-center">
                                                <div class="text-xs <?= $isExpired ? 'text-red-600' : 'text-gray-600' ?>">
                                                    <?= date('d M Y', strtotime($v['expiration_date'])) ?>
                                                </div>
                                                <?php if ($isExpired): ?>
                                                    <span class="inline-block text-xs text-red-600 font-medium mt-1">Expired</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="?edit_voucher=<?= $v['voucher_id'] ?>" class="inline-flex items-center gap-1 bg-yellow-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-yellow-600 transition-all">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <a href="?delete_voucher=<?= $v['voucher_id'] ?>" onclick="return confirm('Delete this voucher?');" class="inline-flex items-center gap-1 bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-600 transition-all">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                        Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>
        </main>
    </div>
</div>
</body>
</html>