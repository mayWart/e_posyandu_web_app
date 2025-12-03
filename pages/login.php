<?php
require '../config.php';

// Jika sudah login, lempar langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Logika Login
if (isset($_POST['login'])) {
    // Ubah variabel dari $email jadi $identity agar lebih umum
    $identity = mysqli_real_escape_string($conn, $_POST['identity']);
    $password = $_POST['password'];

    // Cek user berdasarkan email ATAU nama_lengkap
    // Menggunakan query "OR"
    $query = "SELECT * FROM users WHERE email = '$identity' OR nama_lengkap = '$identity'";
    $result = mysqli_query($conn, $query);

    // Jika user ditemukan
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi Password
        if (password_verify($password, $row['password'])) {
            // Set Session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['role'] = $row['role']; 
            
            // Redirect ke Dashboard
            header("Location: dashboard.php");
            exit;
        }
    }
    
    $error = "Akun tidak ditemukan atau Password salah!";
}

?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk — E-Posyandu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Poppins','Helvetica Neue',Arial,sans-serif}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-emerald-50 flex items-center justify-center py-8">

    <div class="max-w-6xl w-full grid grid-cols-1 md:grid-cols-2 gap-8 items-center px-4">
        <!-- Visual / Hero -->
        <div class="hidden md:flex flex-col items-start justify-center gap-6 pl-12">
            <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-blue-600 to-teal-400 flex items-center justify-center text-white text-2xl font-extrabold">EP</div>
            <h1 class="text-4xl font-extrabold text-slate-900">Selamat Datang di E-Posyandu</h1>
            <p class="text-slate-600 max-w-md">Kelola janji imunisasi dan catat riwayat imunisasi anak Anda dengan mudah. Aman, cepat, dan terintegrasi.</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-600">
                <li class="flex items-center gap-2"><span class="text-emerald-500">✔</span> Mudah diakses</li>
                <li class="flex items-center gap-2"><span class="text-emerald-500">✔</span> Notifikasi jadwal</li>
                <li class="flex items-center gap-2"><span class="text-emerald-500">✔</span> Riwayat digital</li>
            </ul>
        </div>

        <!-- Form Card -->
        <div class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-slate-900">Masuk ke E-Posyandu</h2>
                <p class="mt-2 text-sm text-slate-500">Belum punya akun? <a href="register.php" class="text-blue-600 font-medium">Daftar sekarang</a></p>
            </div>

            <div class="mt-6">
                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="rounded-md bg-emerald-50 p-3 text-emerald-700 text-sm">Akun berhasil dibuat. Silakan login.</div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="rounded-md bg-red-50 p-3 text-red-700 text-sm"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email atau Nama Lengkap</label>
                        <input type="text" name="identity" required class="mt-2 block w-full px-4 py-3 border border-gray-200 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-200" autocomplete="username">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative mt-2">
                            <input id="password" name="password" type="password" required class="block w-full px-4 py-3 border border-gray-200 rounded-lg pr-12 focus:outline-none focus:ring-2 focus:ring-blue-200" autocomplete="current-password">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                <svg id="eye-on" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="remember-me" class="h-4 w-4 text-blue-600"> Ingat saya</label>
                        <a href="#" class="text-blue-600">Lupa password?</a>
                    </div>

                    <div>
                        <button type="submit" name="login" class="w-full py-3 bg-gradient-to-r from-blue-600 to-teal-400 text-white rounded-lg font-semibold shadow hover:scale-[1.01] transition">Masuk</button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                        <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-slate-500">atau masuk dengan</span></div>
                    </div>
                    <div class="mt-4">
                        <a href="#" class="w-full inline-flex items-center justify-center gap-3 px-4 py-3 border border-gray-200 rounded-lg text-sm text-slate-700 hover:bg-gray-50">
                            <img class="h-5 w-5" src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">Masuk dengan Google
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(){
            const p = document.getElementById('password');
            if(p.type === 'password') p.type = 'text'; else p.type = 'password';
        }
    </script>
</body>
</html>