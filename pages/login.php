<?php
require '../config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $identity = mysqli_real_escape_string($conn, $_POST['identity']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$identity' OR nama_lengkap = '$identity'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['role'] = $row['role']; 
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
    <style>body{font-family:'Poppins',sans-serif}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1D3428] via-[#2d4a38] to-[#0f1f18] flex items-center justify-center p-4">

    <div class="w-full max-w-5xl bg-[#1D3428] rounded-[20px] shadow-2xl border-2 border-[#D0F246] overflow-hidden flex flex-col md:flex-row h-full md:min-h-[600px]">
        
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center items-center text-center bg-[#152920] relative">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            
            <div class="relative z-10">
                <img src="../assets/images/logo_eposyadu.png" alt="E-Posyandu" class="w-24 h-24 object-contain mx-auto mb-6 drop-shadow-lg">
                
                <h1 class="text-4xl font-black text-[#D0F246] leading-tight mb-4">E-Posyandu</h1>
                <p class="text-gray-300 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
                    Kelola jadwal imunisasi dan catat riwayat kesehatan anak Anda dengan mudah, aman, dan terintegrasi.
                </p>

                <ul class="text-sm text-gray-400 space-y-2 text-left inline-block">
                    <li class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-[#D0F246]"></span> Akses Mudah 24/7</li>
                    <li class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-[#D0F246]"></span> Notifikasi Jadwal</li>
                    <li class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-[#D0F246]"></span> Grafik Pertumbuhan</li>
                </ul>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-gradient-to-b from-[#1D3428] to-[#0f1f18]">
            <div class="max-w-md mx-auto w-full">
                <h2 class="text-3xl font-bold text-[#D0F246] mb-2">Selamat Datang</h2>
                <p class="text-gray-400 text-sm mb-8">Silakan masuk dengan akun Anda.</p>

                <?php if (isset($error)): ?>
                    <div class="rounded-[10px] bg-red-900/30 p-4 text-red-300 text-xs border border-red-700/50 mb-6 flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="rounded-[10px] bg-emerald-900/30 p-4 text-emerald-300 text-xs border border-emerald-700/50 mb-6">
                        Registrasi berhasil! Silakan login.
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-[#D0F246] mb-2 uppercase tracking-wide">Email / Username</label>
                        <input type="text" name="identity" required class="w-full px-4 py-3 border-2 border-[#D0F246]/30 bg-[#15261d] text-[#D0F246] rounded-[10px] placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] transition" placeholder="Masukkan email atau nama">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-semibold text-[#D0F246] uppercase tracking-wide">Password</label>
                            <a href="#" class="text-xs text-[#D0F246] hover:text-[#bce038] transition">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <input id="password" name="password" type="password" required class="w-full px-4 py-3 border-2 border-[#D0F246]/30 bg-[#15261d] text-[#D0F246] rounded-[10px] pr-12 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] transition" placeholder="Masukkan password">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#D0F246] hover:text-[#bce038] transition">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" class="w-full py-3.5 bg-gradient-to-r from-[#D0F246] to-[#bce038] text-[#1D3428] rounded-[10px] font-bold shadow-lg hover:shadow-[#D0F246]/20 hover:scale-[1.02] transition-all duration-300">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-gray-400 text-sm">
                        Belum punya akun? <a href="register.php" class="text-[#D0F246] font-bold hover:underline">Daftar disini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(){
            const p = document.getElementById('password');
            p.type = p.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>