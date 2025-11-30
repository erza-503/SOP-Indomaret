<?php
require_once '../../config/connect.php'; // koneksi database

$editCashier = null;

// Handle Create / Update
if (isset($_POST['submit_cashier'])) {
    $cashierId = $_POST['cashier_id'] ?? null;
    $cashierName = $_POST['cashier_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($cashierId) {
        // Update data
        $stmt = $conn->prepare("UPDATE cashiers SET cashier_name=?, username=?, password=?, updated_at=NOW() WHERE cashier_id=?");
        $stmt->bind_param("sssi", $cashierName, $username, $password, $cashierId);
    } else {
        // Insert baru
        $stmt = $conn->prepare("INSERT INTO cashiers (cashier_name, username, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sss", $cashierName, $username, $password);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $cashierId = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM cashiers WHERE cashier_id=?");
    $stmt->bind_param("i", $cashierId);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Handle Edit
if (isset($_GET['edit_id'])) {
    $cashierId = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM cashiers WHERE cashier_id=?");
    $stmt->bind_param("i", $cashierId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editCashier = $result->fetch_assoc();
    $stmt->close();
}

// Ambil data semua kasir
$result = $conn->query("SELECT * FROM cashiers ORDER BY cashier_id DESC");
$cashiers = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Indomaret POS</title>
  <link href="../../output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <style>body{font-family:'Poppins',sans-serif;}</style>
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
        <a href="../admin/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-teal-500 text-white font-medium shadow-md transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
          </svg>
          <span>Admin</span>
        </a>
      </nav>
    </div>
    <div class="text-xs text-center text-gray-400 mt-10">© <?= date('Y') ?> Indomaret POS</div>
  </aside>

  <!-- Main -->
  <div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-sm p-5 shadow-sm border-b border-gray-200/50">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Cashier Management</h2>
          <p class="text-sm text-gray-500 mt-1">Manage user accounts and permissions</p>
        </div>
        <div class="flex items-center gap-4">
          <div class="hidden sm:block text-right">
            <div class="text-xs text-gray-400">Administrator</div>
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
          
          <!-- Form -->
          <section class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
            <div class="flex items-center gap-2 mb-6">
              <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                </svg>
              </div>
              <h3 class="font-semibold text-gray-800 text-lg">
                <?= $editCashier ? 'Edit Cashier' : 'Add New Cashier' ?>
              </h3>
            </div>

            <form method="POST" class="space-y-4">
              <?php if ($editCashier): ?>
                <input type="hidden" name="cashier_id" value="<?= $editCashier['cashier_id'] ?>">
              <?php endif; ?>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                <input 
                  type="text" 
                  name="cashier_name" 
                  value="<?= htmlspecialchars($editCashier['cashier_name'] ?? '') ?>" 
                  placeholder="Enter cashier name"
                  required 
                  class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
                >
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input 
                  type="text" 
                  name="username" 
                  value="<?= htmlspecialchars($editCashier['username'] ?? '') ?>" 
                  placeholder="Enter username"
                  required 
                  class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
                >
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <input 
                  type="password" 
                  name="password" 
                  value="<?= htmlspecialchars($editCashier['password'] ?? '') ?>" 
                  placeholder="Enter password"
                  required 
                  class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
                >
                <p class="text-xs text-gray-500 mt-1">Must be at least 6 characters</p>
              </div>

              <button 
                type="submit" 
                name="submit_cashier" 
                class="w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white py-3 rounded-xl font-semibold hover:shadow-lg transition-all transform hover:-translate-y-0.5"
              >
                <?= $editCashier ? '✓ Update Cashier' : '+ Add Cashier' ?>
              </button>

              <?php if ($editCashier): ?>
                <a 
                  href="index.php" 
                  class="block w-full text-center bg-gray-200 text-gray-700 font-medium py-3 rounded-xl hover:bg-gray-300 transition-all"
                >
                  Cancel
                </a>
              <?php endif; ?>
            </form>
          </section>

          <!-- Cashier Table Section -->
          <section class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
            <div class="flex items-center justify-between mb-5">
              <h3 class="font-semibold text-gray-800 text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                </svg>
                Cashier List
              </h3>
              <span class="text-xs bg-teal-100 text-teal-700 px-3 py-1.5 rounded-full font-medium"><?= count($cashiers) ?> users</span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="border-b-2 border-gray-200">
                  <tr class="text-gray-600 text-xs uppercase tracking-wider">
                    <th class="pb-3 text-left font-semibold">ID</th>
                    <th class="pb-3 text-left font-semibold">Name</th>
                    <th class="pb-3 text-left font-semibold">Username</th>
                    <th class="pb-3 text-left font-semibold">Created</th>
                    <th class="pb-3 text-center font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($cashiers)): ?>
                    <tr>
                      <td colspan="5" class="py-12 text-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        No cashiers found
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($cashiers as $c): ?>
                      <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all">
                        <td class="py-3 font-mono text-xs text-gray-700">#<?= $c['cashier_id'] ?></td>
                        <td class="py-3">
                          <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-teal-500 text-white flex items-center justify-center font-semibold text-xs">
                              <?= strtoupper(substr($c['cashier_name'], 0, 1)) ?>
                            </div>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($c['cashier_name']) ?></span>
                          </div>
                        </td>
                        <td class="py-3">
                          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            <?= htmlspecialchars($c['username']) ?>
                          </span>
                        </td>
                        <td class="py-3 text-xs text-gray-600">
                          <?= date('d M Y', strtotime($c['created_at'])) ?><br>
                          <span class="text-gray-400"><?= date('H:i', strtotime($c['created_at'])) ?></span>
                        </td>
                        <td class="py-3 text-center">
                          <div class="flex items-center justify-center gap-2">
                            <a 
                              href="?edit_id=<?= $c['cashier_id'] ?>" 
                              class="inline-flex items-center gap-1 bg-yellow-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-yellow-600 transition-all"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                              </svg>
                              Edit
                            </a>
                            <a 
                              href="?delete_id=<?= $c['cashier_id'] ?>" 
                              class="inline-flex items-center gap-1 bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-600 transition-all" 
                              onclick="return confirm('Are you sure you want to delete this cashier?')"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                              </svg>
                              Delete
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
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