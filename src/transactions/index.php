<?php
require_once '../../config/connect.php';

// Fetch transaksi
$query = "
    SELECT t.*, c.cashier_name, v.voucher_code 
    FROM transactions t
    LEFT JOIN cashiers c ON t.cashier_id = c.cashier_id
    LEFT JOIN vouchers v ON t.voucher_id = v.voucher_id
    ORDER BY t.transaction_id DESC";
$result = $conn->query($query);
$transactions = $result->fetch_all(MYSQLI_ASSOC);

// Ambil detail tiap transaksi (1 query besar)
$detailQuery = "
    SELECT td.*, p.product_name 
    FROM transaction_details td
    JOIN products p ON td.product_id = p.product_id
";
$detailResult = $conn->query($detailQuery);
$details = [];

while ($row = $detailResult->fetch_assoc()) {
    $details[$row['transaction_id']][] = $row;
}

$conn->close();

function idr($val) {
    return 'Rp ' . number_format($val, 0, ',', '.');
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transactions - Indomaret POS</title>
<link href="../../output.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

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
                <a href="../products/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-slate-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" />
                    </svg>
                    <span>Products</span>
                </a>
                <a href="../transactions/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-teal-500 text-white font-medium shadow-md transition-all">
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
                    <h2 class="text-2xl font-bold text-gray-900">Transaction History</h2>
                    <p class="text-sm text-gray-500 mt-1">View all sales transactions</p>
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
                <section class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-semibold text-gray-800 text-lg flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                            All Transactions
                        </h3>
                        <span class="text-xs bg-teal-100 text-teal-700 px-3 py-1.5 rounded-full font-medium"><?= count($transactions) ?> records</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b-2 border-gray-200">
                                <tr class="text-gray-600 text-xs uppercase tracking-wider">
                                    <th class="pb-3 text-left font-semibold">ID</th>
                                    <th class="pb-3 text-left font-semibold">Cashier</th>
                                    <th class="pb-3 text-left font-semibold">Voucher</th>
                                    <th class="pb-3 text-left font-semibold">Payment</th>
                                    <th class="pb-3 text-right font-semibold">Total</th>
                                    <th class="pb-3 text-right font-semibold">Final</th>
                                    <th class="pb-3 text-center font-semibold">Date</th>
                                    <th class="pb-3 text-center font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="8" class="py-12 text-center text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            No transactions found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $t): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all">
                                        <td class="py-3 font-mono text-xs text-gray-700">#<?= $t['transaction_id'] ?></td>
                                        <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($t['cashier_name']) ?></td>
                                        <td class="py-3">
                                            <?php if ($t['voucher_code']): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                                                    </svg>
                                                    <?= htmlspecialchars($t['voucher_code']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $t['payment_method'] == 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' ?>">
                                                <?= ucfirst($t['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-right text-gray-600"><?= idr($t['total_amount']) ?></td>
                                        <td class="py-3 text-right font-bold text-teal-600"><?= idr($t['final_amount']) ?></td>
                                        <td class="py-3 text-center text-xs text-gray-600">
                                            <?= date('d/m/Y', strtotime($t['transaction_date'])) ?><br>
                                            <span class="text-gray-400"><?= date('H:i', strtotime($t['transaction_date'])) ?></span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <button 
                                                class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium hover:shadow-lg transition-all"
                                                onclick='showDetail(<?= json_encode($t) ?>, <?= json_encode($details[$t["transaction_id"]] ?? []) ?>)'>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative animate-fade-in">
        <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white p-6 rounded-t-2xl">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl font-light transition-all">&times;</button>
            <h3 class="text-xl font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                Transaction Details
            </h3>
        </div>
        <div id="detailContent" class="p-6 text-sm text-gray-700 max-h-96 overflow-y-auto"></div>
        <div class="bg-gray-50 p-4 rounded-b-2xl border-t border-gray-200 flex justify-end">
            <button onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-xl text-sm font-medium transition-all">Close</button>
        </div>
    </div>
</div>

<script>
function showDetail(data, detailList) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');

    let productRows = "";
    if (detailList.length > 0) {
        productRows = detailList.map(d => `
            <tr class="hover:bg-gray-50 transition-all">
                <td class="px-4 py-3 border-b border-gray-100 text-gray-800">${d.product_name}</td>
                <td class="px-4 py-3 border-b border-gray-100 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        ${d.quantity}x
                    </span>
                </td>
                <td class="px-4 py-3 border-b border-gray-100 text-right font-semibold text-teal-600">Rp ${parseFloat(d.subtotal).toLocaleString('id-ID')}</td>
            </tr>
        `).join('');
    } else {
        productRows = `<tr><td colspan="3" class="text-center py-8 text-gray-400">No products found</td></tr>`;
    }

    const discountAmount = parseFloat(data.total_amount) - parseFloat(data.final_amount);
    const hasDiscount = discountAmount > 0;

    content.innerHTML = `
        <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Transaction ID</p>
                <p class="font-mono font-semibold text-gray-800">#${data.transaction_id}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Cashier</p>
                <p class="font-semibold text-gray-800">${data.cashier_name}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payment Method</p>
                <p class="font-semibold text-gray-800">${data.payment_method.toUpperCase()}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date & Time</p>
                <p class="font-semibold text-gray-800">${new Date(data.transaction_date).toLocaleString('id-ID')}</p>
            </div>
        </div>

        <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                </svg>
                Products
            </h4>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Product</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>${productRows}</tbody>
                </table>
            </div>
        </div>

        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-xl space-y-2">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold text-gray-800">Rp ${parseFloat(data.total_amount).toLocaleString('id-ID')}</span>
            </div>
            ${hasDiscount ? `
            <div class="flex justify-between items-center text-sm">
                <span class="text-green-600 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                    </svg>
                    Discount
                </span>
                <span class="font-semibold text-green-600">-Rp ${discountAmount.toLocaleString('id-ID')}</span>
            </div>
            ` : ''}
            <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300">
                <span class="font-bold text-gray-800">Total Payment</span>
                <span class="font-bold text-xl text-teal-600">Rp ${parseFloat(data.final_amount).toLocaleString('id-ID')}</span>
            </div>
        </div>
    `;
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
</body>
</html>