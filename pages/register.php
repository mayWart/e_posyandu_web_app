<?php
require '../config.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (nama_lengkap, email, password, role) VALUES ('$nama', '$email', '$pass', 'user')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: login.php?status=success");
    } else {
        $error = "Gagal mendaftar. Email mungkin sudah digunakan.";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar — E-Posyandu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Poppins',sans-serif}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1D3428] via-[#2d4a38] to-[#0f1f18] flex items-center justify-center p-4">

    <div class="w-full max-w-5xl bg-[#1D3428] rounded-[20px] shadow-2xl border-2 border-[#D0F246] overflow-hidden flex flex-col md:flex-row h-full md:min-h-[650px]">
        
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center items-center text-center bg-[#152920] relative">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            
            <div class="relative z-10">
                <img src="../assets/images/logo_eposyadu.png" alt="E-Posyandu" class="w-24 h-24 object-contain mx-auto mb-6 drop-shadow-lg">
                <h1 class="text-3xl font-black text-[#D0F246] leading-tight mb-4">Gabung Sekarang</h1>
                <p class="text-gray-300 text-sm leading-relaxed mb-6 max-w-xs mx-auto">
                    Buat akun untuk mulai memantau tumbuh kembang anak dan dapatkan akses ke fitur eksklusif.
                </p>
                <div class="bg-[#1D3428]/80 p-4 rounded-xl border border-[#D0F246]/20 text-left space-y-2">
                    <div class="flex items-center gap-2 text-xs text-gray-300">
                        <svg class="w-4 h-4 text-[#D0F246]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Gratis selamanya
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-300">
                        <svg class="w-4 h-4 text-[#D0F246]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Data aman & terenkripsi
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-gradient-to-b from-[#1D3428] to-[#0f1f18]">
            <div class="max-w-md mx-auto w-full">
                <h2 class="text-3xl font-bold text-[#D0F246] mb-2">Buat Akun</h2>
                <p class="text-gray-400 text-sm mb-8">Lengkapi data diri Anda di bawah ini.</p>

                <?php if (isset($error)): ?>
                    <div class="rounded-[10px] bg-red-900/30 p-4 text-red-300 text-xs border border-red-700/50 mb-6">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#D0F246] mb-1.5 uppercase tracking-wide">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full px-4 py-3 border-2 border-[#D0F246]/30 bg-[#15261d] text-[#D0F246] rounded-[10px] placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] transition" placeholder="Contoh: Siti Aminah">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#D0F246] mb-1.5 uppercase tracking-wide">Alamat Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 border-2 border-[#D0F246]/30 bg-[#15261d] text-[#D0F246] rounded-[10px] placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] transition" placeholder="nama@email.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#D0F246] mb-1.5 uppercase tracking-wide">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 border-2 border-[#D0F246]/30 bg-[#15261d] text-[#D0F246] rounded-[10px] placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] transition" placeholder="Minimal 6 karakter">
                    </div>

                    <button type="submit" name="register" class="w-full py-3.5 bg-gradient-to-r from-[#D0F246] to-[#bce038] text-[#1D3428] rounded-[10px] font-bold shadow-lg hover:shadow-[#D0F246]/20 hover:scale-[1.02] transition-all duration-300 mt-2">
                        Daftar Akun
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-gray-400 text-sm">
                        Sudah punya akun? <a href="login.php" class="text-[#D0F246] font-bold hover:underline">Masuk disini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>