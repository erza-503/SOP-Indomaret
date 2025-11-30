<?php
session_start();
require_once 'config/connect.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['cashier_id'])) {
  header("Location: auth/login.php");
  exit();
}

$cashierId = $_SESSION['cashier_id'];
$cashierName = $_SESSION['cashier_name'] ?? 'Kasir';

$totalSales = 0;
$totalTransactions = 0;
$totalProducts = 0;
$totalCustomers = 0;
$recentTransactions = [];

// Fetch Total Sales (sum of final_amount from transactions for the current month)
$currentMonth = date('Y-m');
$stmt = $conn->prepare("SELECT SUM(final_amount) AS total_sales FROM transactions WHERE DATE_FORMAT(transaction_date, '%Y-%m') = ?");
$stmt->bind_param("s", $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $totalSales = $row['total_sales'] ?? 0;
}
$stmt->close();

// Fetch Total Transactions (for today)
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) AS total_transactions FROM transactions WHERE DATE(transaction_date) = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $totalTransactions = $row['total_transactions'] ?? 0;
}
$stmt->close();

// Fetch Total Products
$result = $conn->query("SELECT COUNT(*) AS total_products FROM products");
if ($row = $result->fetch_assoc()) {
    $totalProducts = $row['total_products'] ?? 0;
}

// Fetch Total Customers
$result = $conn->query("SELECT COUNT(*) AS total_customers FROM cashiers");
if ($row = $result->fetch_assoc()) {
    $totalCustomers = $row['total_customers'] ?? 0;
}

// Fetch Recent Transactions
$stmt = $conn->prepare("SELECT t.transaction_id AS id, t.transaction_date AS time, c.cashier_name AS cashier, SUM(td.quantity) AS items, t.final_amount AS amount
                        FROM transactions t
                        JOIN cashiers c ON t.cashier_id = c.cashier_id
                        JOIN transaction_details td ON t.transaction_id = td.transaction_id
                        GROUP BY t.transaction_id, t.transaction_date, c.cashier_name, t.final_amount
                        ORDER BY t.transaction_date DESC
                        LIMIT 5");
$stmt->execute();
$recentTransactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

function idr($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Indomaret POS - Dashboard</title>
  <link href="./output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
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
          <a href="./index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-teal-500 text-white font-medium transition-all shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
            </svg>
            <span>Dashboard</span>
          </a>
          <a href="src/cashier/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
            </svg>
            <span>Cashier</span>
          </a>
          <a href="src/products/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" />
            </svg>
            <span>Products</span>
          </a>
          <a href="src/transactions/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
            </svg>
            <span>Transactions</span>
          </a>
          <a href="src/admin/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
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
            <div class="flex items-center gap-2">
              <h2 class="text-2xl font-bold text-gray-900">Welcome back, <?= htmlspecialchars(explode(' ', $cashierName)[0]) ?> 👋</h2>
            </div>
            <p class="text-sm text-gray-500 mt-1">Dashboard</p>
          </div>
          <div class="flex items-center gap-4">
            <button class="p-2 hover:bg-gray-100 rounded-lg transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-teal-500 text-white flex items-center justify-center font-semibold shadow-md"><?= strtoupper(substr($cashierName, 0, 1)) ?></div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-auto p-6">
        <div class="max-w-7xl mx-auto space-y-6">
          
          <!-- Top Row: Next Action + Stats -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Next Transaction Card -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Next transaction</h3>
                <a href="src/transactions/index.php" class="text-xs text-teal-600 hover:underline">View history</a>
              </div>
              <div class="flex items-center gap-4 mb-4">
                <div class="text-2xl">🛒</div>
                <div>
                  <div class="text-xs text-gray-500">Ready to serve</div>
                  <div class="text-sm font-medium text-gray-700">Start new transaction</div>
                </div>
              </div>
              <a href="src/cashier/index.php" class="block w-full text-center bg-gradient-to-r from-teal-500 to-teal-600 text-white py-2.5 rounded-xl font-medium hover:shadow-lg transition-all">
                Go to Cashier
              </a>
            </div>

            <!-- Transaction Stats -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-100">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-gray-500 uppercase tracking-wide">Transactions</div>
                </div>
              </div>
              <div class="text-3xl font-bold text-gray-900"><?= $totalTransactions ?></div>
              <div class="text-xs text-gray-500 mt-1">Today</div>
            </div>

            <!-- Products Stats -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-100">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-600" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs text-gray-500 uppercase tracking-wide">Products</div>
                </div>
              </div>
              <div class="text-3xl font-bold text-gray-900"><?= $totalProducts ?></div>
              <div class="text-xs text-gray-500 mt-1">In catalog</div>
            </div>
          </div>

          <!-- Bottom Row: Sales Overview + Recent Transactions -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Sales Stats Grid -->
            <div class="space-y-6">
              <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 shadow-lg text-white">
                <div class="flex items-center gap-2 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-xs uppercase tracking-wide opacity-90">Total Sales</span>
                </div>
                <div class="text-3xl font-bold"><?= idr($totalSales) ?></div>
                <div class="text-xs opacity-75 mt-1">This month</div>
              </div>

              <div class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-2xl p-6 shadow-lg text-white">
                <div class="flex items-center gap-2 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                  </svg>
                  <span class="text-xs uppercase tracking-wide opacity-90">Active Users</span>
                </div>
                <div class="text-3xl font-bold"><?= $totalCustomers ?></div>
                <div class="text-xs opacity-75 mt-1">Registered cashiers</div>
              </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-md border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-lg">Recent Transactions</h3>
                <a href="src/transactions/index.php" class="text-sm text-teal-600 hover:underline">View all</a>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="border-b border-gray-200">
                    <tr class="text-gray-600 text-xs uppercase tracking-wider">
                      <th class="pb-3 text-left font-semibold">ID</th>
                      <th class="pb-3 text-left font-semibold">Time</th>
                      <th class="pb-3 text-left font-semibold">Cashier</th>
                      <th class="pb-3 text-right font-semibold">Items</th>
                      <th class="pb-3 text-right font-semibold">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($recentTransactions)): ?>
                      <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">No transactions yet</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($recentTransactions as $t): ?>
                      <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all">
                        <td class="py-3 font-mono text-xs text-gray-700">#<?= htmlspecialchars($t['id']) ?></td>
                        <td class="py-3 text-xs text-gray-600"><?= date('H:i', strtotime($t['time'])) ?></td>
                        <td class="py-3 text-gray-700"><?= htmlspecialchars($t['cashier']) ?></td>
                        <td class="py-3 text-right text-gray-600"><?= htmlspecialchars($t['items']) ?> items</td>
                        <td class="py-3 text-right font-semibold text-teal-600"><?= idr($t['amount']) ?></td>
                      </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>

          <!-- Quick Actions Banner -->
          <div class="bg-gradient-to-r from-teal-500 via-cyan-500 to-blue-500 rounded-2xl p-8 shadow-xl text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
            <div class="relative z-10">
              <h3 class="text-2xl font-bold mb-2">🚀 Ready to boost sales?</h3>
              <p class="text-sm opacity-90 mb-6">Add new products or start processing transactions</p>
              <div class="flex gap-3">
                <a href="src/products/index.php" class="bg-white text-teal-600 px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all">
                  Manage Products
                </a>
                <a href="src/cashier/index.php" class="bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-xl font-medium hover:bg-white/30 transition-all">
                  Open Cashier
                </a>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</body>
</html>