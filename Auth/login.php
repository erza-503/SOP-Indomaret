<?php
session_start();
require_once '../config/connect.php';

// Jika sudah login, langsung redirect ke kasir
if (isset($_SESSION['cashier_id'])) {
  header("Location: src/cashier/index.php");
  exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  if (!empty($username) && !empty($password)) {
    $stmt = $conn->prepare("SELECT cashier_id, password FROM cashiers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $cashier = $result->fetch_assoc();
      // Untuk testing awal tanpa hash, gunakan: if ($password == $cashier['password'])
      if (password_verify($password, $cashier['password']) || $password === $cashier['password']) {
        $_SESSION['cashier_id'] = $cashier['cashier_id'];
        header("Location: ../index.php");
        exit();
      } else {
        $error = 'Password salah.';
      }
    } else {
      $error = 'Username tidak ditemukan.';
    }
    $stmt->close();
  } else {
    $error = 'Harap isi semua kolom.';
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Indomaret POS</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link href="../output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 flex items-center justify-center min-h-screen p-4">
  
  <div class="w-full max-w-md">
    <!-- Logo & Brand -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl shadow-2xl mb-4">
        <span class="text-white font-bold text-3xl">IM</span>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Indomaret</h1>
      <p class="text-gray-500 text-sm">Point of Sales System</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white/80 backdrop-blur-lg shadow-2xl rounded-3xl p-8 border border-white/20">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Welcome back 👋</h2>
        <p class="text-gray-500 text-sm">Sign in to your cashier account</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm mb-6 flex items-start gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-5">
        <!-- Username Field -->
        <div>
          <label class="block text-gray-700 font-semibold mb-2 text-sm">Username</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
            <input 
              type="text" 
              name="username" 
              placeholder="Enter your username" 
              required
              class="w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all bg-white"
            >
          </div>
        </div>

        <!-- Password Field -->
        <div>
          <label class="block text-gray-700 font-semibold mb-2 text-sm">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <input 
              type="password" 
              name="password" 
              placeholder="Enter your password" 
              required
              class="w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all bg-white"
            >
          </div>
        </div>

        <!-- Login Button -->
        <button 
          type="submit" 
          class="w-full bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold py-3.5 rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
        >
          Sign In
        </button>
      </form>

      <!-- Additional Info -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span>Secure login with encrypted connection</span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-8 text-sm text-gray-500">
      <p>© <?= date('Y') ?> Indomaret POS. All rights reserved.</p>
      <p class="mt-2 text-xs">Need help? Contact <span class="text-teal-600 font-medium">support@indomaretpos.com</span></p>
    </div>
  </div>

  <!-- Decorative Elements -->
  <div class="fixed top-0 left-0 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl -z-10"></div>
  <div class="fixed bottom-0 right-0 w-96 h-96 bg-blue-200/30 rounded-full blur-3xl -z-10"></div>
</body>
</html>