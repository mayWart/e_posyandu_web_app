<?php
require '../config.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Default Role adalah 'user'
    $query = "INSERT INTO users (nama_lengkap, email, password, role) VALUES ('$nama', '$email', '$pass', 'user')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: login.php?status=success");
    } else {
        $error = "Pendaftaran gagal!";
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
    <style>body{font-family:'Poppins','Helvetica Neue',Arial,sans-serif}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-orange-50 flex items-center justify-center py-8">

    <div class="max-w-5xl w-full grid grid-cols-1 md:grid-cols-2 gap-8 items-center px-4">
        <div class="hidden md:flex flex-col items-start justify-center gap-6 pl-12">
            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-rose-500 to-orange-400 flex items-center justify-center text-white text-2xl font-extrabold">EP</div>
            <h2 class="text-3xl font-extrabold text-slate-900">Bergabung dengan E-Posyandu</h2>
            <p class="text-slate-600 max-w-md">Buat akun untuk mulai mengelola janji imunisasi dan catat riwayat imunisasi anak Anda dengan mudah.</p>
        </div>

        <div class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-4">
                <h3 class="text-2xl font-bold text-slate-900">Daftar Akun</h3>
                <p class="text-sm text-slate-500 mt-1">Sudah punya akun? <a href="login.php" class="text-blue-600">Masuk</a></p>
            </div>

            <?php if (isset($error)): ?>
                <div class="rounded-md bg-red-50 p-3 text-red-700 text-sm"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text" name="nama" required class="mt-2 block w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" required class="mt-2 block w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required class="mt-2 block w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-200">
                </div>

                <button type="submit" name="register" class="w-full py-3 bg-gradient-to-r from-rose-500 to-orange-400 text-white rounded-lg font-semibold shadow hover:scale-[1.01] transition">Daftar Sekarang</button>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-slate-500">Atau daftar dengan</span></div>
                </div>
                <div class="mt-4">
                    <button class="w-full flex justify-center items-center py-3 px-4 border border-gray-200 rounded-lg bg-white text-sm text-slate-700 hover:bg-gray-50">
                        <img class="h-5 w-5 mr-2" src="https://www.svgrepo.com/show/475656/google-color.svg" alt=""> Daftar dengan Google
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>