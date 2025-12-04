<?php
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- [LOGIKA 1] SIMPAN DATA ANAK (USER) ---
if (isset($_POST['simpan_anak'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_balita']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $jk = $_POST['jenis_kelamin'];
    $tgl = $_POST['tgl_lahir'];

    $queryInsert = "INSERT INTO balita (user_id, nama_balita, nik, tgl_lahir, jenis_kelamin) 
                    VALUES ('$user_id', '$nama', '$nik', '$tgl', '$jk')";

    if (mysqli_query($conn, $queryInsert)) {
        echo '<script>showAlert("success","Berhasil","Data anak berhasil disimpan!","dashboard.php");</script>';
    } else {
        echo '<script>showAlert("error","Gagal","Gagal menyimpan data.");</script>';
    }
}

// --- [LOGIKA 2] BUAT JANJI IMUNISASI (USER) ---
if (isset($_POST['buat_janji'])) {
    $balita_id = $_POST['balita_id'];
    $imunisasi_id = $_POST['imunisasi_id'];
    $tgl_rencana = $_POST['tanggal_rencana'];
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    $queryJanji = "INSERT INTO janji_imunisasi (user_id, balita_id, imunisasi_id, tanggal_rencana, catatan) 
                   VALUES ('$user_id', '$balita_id', '$imunisasi_id', '$tgl_rencana', '$catatan')";

    if (mysqli_query($conn, $queryJanji)) {
        echo '<script>showAlert("success","Berhasil","Janji temu berhasil dibuat! Menunggu konfirmasi kader.","dashboard.php");</script>';
    } else {
        $err = addslashes(mysqli_error($conn));
        echo '<script>showAlert("error","Gagal","Gagal membuat janji: ' . $err . '");</script>';
    }
}

// --- [LOGIKA 3 - BARU] UPDATE STATUS JANJI (ADMIN) ---
if (isset($_POST['update_status']) && $role == 'admin') {
    $janji_id = $_POST['janji_id'];
    $status_baru = $_POST['status_baru'];

    $updateQuery = "UPDATE janji_imunisasi SET status = '$status_baru' WHERE id = '$janji_id'";

    if (mysqli_query($conn, $updateQuery)) {
        echo '<script>showAlert("success","Status Diperbarui","Status janji berhasil diperbarui menjadi ' . addslashes($status_baru) . '.","dashboard.php");</script>';
    } else {
        echo '<script>showAlert("error","Gagal","Gagal memperbarui status.");</script>';
    }
}
if (isset($_POST['update_status']) && $role == 'admin') {
    $janji_id = $_POST['janji_id'];
    $status_baru = $_POST['status_baru'];

    // Cegah status 'Selesai' lewat jalur ini, harus lewat jalur 'proses_imunisasi'
    if ($status_baru == 'Selesai') {
        echo '<script>showAlert("info","Perhatian","Untuk menyelesaikan, mohon gunakan tombol Centang Hijau agar tercatat di riwayat.");</script>';
    } else {
        mysqli_query($conn, "UPDATE janji_imunisasi SET status = '$status_baru' WHERE id = '$janji_id'");
        echo '<script>window.location="dashboard.php";</script>';
    }
}

// --- [LOGIKA 4 - BARU] SELESAIKAN & CATAT RIWAYAT (ADMIN) ---
if (isset($_POST['proses_imunisasi']) && $role == 'admin') {
    $janji_id = $_POST['janji_id'];
    $tgl_suntik = $_POST['tgl_suntik']; // Admin bisa ubah tanggal jika realisasinya beda hari
    $bidan = mysqli_real_escape_string($conn, $_POST['bidan_penyuntik']);

    // 1. Ambil data dari janji temu
    $cekJanji = mysqli_query($conn, "SELECT * FROM janji_imunisasi WHERE id = '$janji_id'");
    $dataJanji = mysqli_fetch_assoc($cekJanji);

    if ($dataJanji) {
        // 2. Insert ke Tabel Riwayat Imunisasi
        $insertRiwayat = "INSERT INTO riwayat_imunisasi (balita_id, imunisasi_id, tgl_suntik, bidan_penyuntik) 
                          VALUES ('$dataJanji[balita_id]', '$dataJanji[imunisasi_id]', '$tgl_suntik', '$bidan')";

        $simpan = mysqli_query($conn, $insertRiwayat);

        // 3. Update Status Janji jadi 'Selesai'
        if ($simpan) {
            mysqli_query($conn, "UPDATE janji_imunisasi SET status = 'Selesai' WHERE id = '$janji_id'");
            echo '<script>showAlert("success","Selesai","Imunisasi berhasil diselesaikan & tercatat di riwayat!","dashboard.php");</script>';
        } else {
            $err = addslashes(mysqli_error($conn));
            echo '<script>showAlert("error","Gagal","Gagal menyimpan riwayat: ' . $err . '");</script>';
        }
    }
}

// --- [DATA FETCHING] ---

// 1. Statistik & Jadwal
if ($role == 'admin') {
    // Admin melihat total semua balita
    $balitaCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM balita"))['total'];
    // Admin melihat jumlah janji yang statusnya 'Menunggu'
    $pendingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM janji_imunisasi WHERE status = 'Menunggu'"))['total'];
    // UNTUK MENGHITUNG TOTAL IBU HAMIL (Admin Only) <<<
    $ibuHamilCountResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM ibu_hamil");
    $ibuHamilCount = mysqli_fetch_assoc($ibuHamilCountResult)['total'];
} else {
    // User hanya melihat miliknya
    $balitaCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM balita WHERE user_id = '$user_id'"))['total'];
}

$jadwalNext = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jadwal WHERE waktu_mulai > NOW() ORDER BY waktu_mulai ASC LIMIT 1"));

// 2. Data Helper (Dropdown & Lists)
$queryAnak = mysqli_query($conn, "SELECT * FROM balita WHERE user_id = '$user_id'");
$queryImunisasi = mysqli_query($conn, "SELECT * FROM master_imunisasi ORDER BY id ASC");

require '../layout/header.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <section aria-label="Welcome Banner"
        class="bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl p-8 text-white shadow-xl shadow-blue-200 mb-10 relative overflow-hidden">
        <div class="relative z-10 max-w-2xl fade-up">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Halo,
                <?= htmlspecialchars($_SESSION['nama']); ?> !
            </h1>
            <p class="mt-3 text-blue-50 text-lg max-w-xl">
                <?= $role == 'admin' ? 'Ada <strong>' . $pendingCount . '</strong> janji temu baru yang perlu konfirmasi hari ini.' : 'Pantau kesehatan keluarga dengan mudah — buat janji, catat riwayat, dan pantau pertumbuhan anak.'; ?>
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <?php if ($role == 'user'): ?>
                    <button onclick="toggleModalJanji()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white text-teal-600 rounded-xl font-semibold shadow hover:scale-105 transition transform">Buat
                        Janji</button>
                <?php else: ?>
                    <a href="edit_detail_balita.php"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white text-teal-600 rounded-xl font-semibold shadow hover:scale-105 transition transform">Kelola
                        Penimbangan</a>
                <?php endif; ?>
            </div>
        </div>

        <svg class="absolute right-[-10%] top-0 h-full w-1/2 text-white opacity-10 float-up" viewBox="0 0 100 100"
            fill="currentColor">
            <circle cx="50" cy="50" r="50" />
        </svg>
        <div class="absolute -left-6 bottom-0 w-72 h-72 rounded-full bg-white/5 blur-3xl float-up"
            style="filter: blur(50px); opacity:0.5;"></div>
    </section>

    <?php if ($role == 'user'): ?>

        <section aria-labelledby="daftar-anak-title" class="mb-12">
            <header class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <h2 id="daftar-anak-title" class="text-2xl font-bold text-gray-900">Daftar Buah Hati</h2>
                    <p class="text-sm text-gray-500 mt-1">Data pertumbuhan anak Anda.</p>
                </div>

                <div class="flex gap-3">
                    <button onclick="toggleModal()"
                        class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-sm font-medium rounded-xl text-blue-600 bg-white hover:bg-blue-50 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Anak
                    </button>
                    <button onclick="toggleModalJanji()"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm shadow-blue-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Buat Janji
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                mysqli_data_seek($queryAnak, 0);
                while ($anak = mysqli_fetch_assoc($queryAnak)):
                    $umur = (new DateTime())->diff(new DateTime($anak['tgl_lahir']));
                    ?>
                    <article
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group">
                        <div class="p-6">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="<?= $anak['jenis_kelamin'] == 'L' ? 'bg-blue-50 text-blue-500' : 'bg-pink-50 text-pink-500'; ?> h-14 w-14 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition">
                                        <?= htmlspecialchars($anak['nama_balita']); ?>
                                    </h4>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded"><?= $umur->y; ?> Thn
                                        <?= $umur->m; ?> Bln</span>
                                </div>
                            </div>
                        </div>
                        <a href="detail_anak.php?id=<?= $anak['id']; ?>"
                            class="block bg-gray-50 hover:bg-blue-50 p-3 text-center text-sm font-semibold text-blue-600 border-t border-gray-100 transition">Lihat
                            Detail →</a>
                    </article>
                <?php endwhile; ?>
            </div>
        </section>

        <section aria-labelledby="janji-title">
            <h2 id="janji-title" class="text-2xl font-bold text-gray-900 mb-6">Janji Temu Aktif</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Anak & Imunisasi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $qUser = mysqli_query($conn, "SELECT j.*, b.nama_balita, m.nama_imunisasi FROM janji_imunisasi j JOIN balita b ON j.balita_id = b.id JOIN master_imunisasi m ON j.imunisasi_id = m.id WHERE j.user_id = '$user_id' ORDER BY j.tanggal_rencana DESC");
                            while ($j = mysqli_fetch_assoc($qUser)):
                                $color = match ($j['status']) { 'Menunggu' => 'bg-yellow-100 text-yellow-800', 'Disetujui' => 'bg-green-100 text-green-800', 'Selesai' => 'bg-blue-100 text-blue-800', 'Batal' => 'bg-red-100 text-red-800'};
                                ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($j['tanggal_rencana'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="font-medium text-gray-900"><?= $j['nama_balita']; ?></div>
                                        <div class="text-xs"><?= $j['nama_imunisasi']; ?></div>
                                    </td>
                                    <td class="px-6 py-4"><span
                                            class="px-2 py-1 text-xs font-bold rounded-full <?= $color; ?>"><?= $j['status']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    <?php else: ?>

        <section aria-labelledby="admin-menu-title" class="mb-12">
            <h2 id="admin-menu-title" class="text-xl font-bold text-gray-900 mb-6">Menu Operasional</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="edit_detail_balita.php"
                    class="flex items-center p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition group">
                    <div
                        class="h-12 w-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Input Penimbangan</h3>
                        <p class="text-xs text-gray-500">Update data bulanan</p>
                    </div>
                </a>
                <a href="#"
                    class="flex items-center p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-200 transition group">
                    <div
                        class="h-12 w-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-green-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Kelola Jadwal</h3>
                        <p class="text-xs text-gray-500">Atur kegiatan posyandu</p>
                    </div>
                </a>
                <a href="data_anak.php"
                    class="flex items-center p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-200 transition group">
                    <div
                        class="h-12 w-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-orange-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Data Master Balita</h3>
                        <p class="text-xs text-gray-500">Database seluruh desa</p>
                    </div>
                </a>
                <a href="data_ibu_hamil.php"
                    class="flex items-center p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-teal-200 transition group">
                    <div
                        class="h-12 w-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-teal-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4.354a6 6 0 014.242 1.756l.707.707a.5.5 0 01-.707.707L16 6.561A5 5 0 0012 5.061M12 21a9 9 0 01-9-9c0-3.321 2.679-6 6-6h6c3.321 0 6 2.679 6 6a9 9 0 01-9 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Data Ibu Hamil</h3>
                        <p class="text-xs text-gray-500">Total Aktif: <strong
                                class="text-lg text-teal-600"><?= $ibuHamilCount; ?></strong></p>
                        <span class="text-xs text-blue-500 hover:text-blue-700 font-semibold mt-1 block">Lihat Data →</span>
                    </div>
                </a>
            </div>
        </section>

        <section aria-labelledby="konfirmasi-janji-title">
            <div class="flex items-center justify-between mb-6">
                <h2 id="konfirmasi-janji-title" class="text-xl font-bold text-gray-900">Permintaan Janji Temu</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Total:
                    <?= $pendingCount; ?> Menunggu</span>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Orang Tua / Anak</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Imunisasi & Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            // Query Admin
                            $queryAdminJanji = mysqli_query($conn, "
        SELECT j.*, b.nama_balita, u.nama_lengkap as nama_ortu, m.nama_imunisasi 
        FROM janji_imunisasi j 
        JOIN balita b ON j.balita_id = b.id 
        JOIN users u ON j.user_id = u.id 
        JOIN master_imunisasi m ON j.imunisasi_id = m.id 
        ORDER BY j.status = 'Menunggu' DESC, j.tanggal_rencana ASC
    ");

                            while ($row = mysqli_fetch_assoc($queryAdminJanji)):
                                $statusClass = match ($row['status']) {
                                    'Menunggu' => 'bg-yellow-100 text-yellow-800',
                                    'Disetujui' => 'bg-green-100 text-green-800',
                                    'Selesai' => 'bg-blue-100 text-blue-800',
                                    'Batal' => 'bg-red-100 text-red-800',
                                };
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($row['tanggal_rencana'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['nama_ortu']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">Anak: <?= htmlspecialchars($row['nama_balita']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-sm text-gray-700 bg-gray-100 px-2 py-1 rounded"><?= $row['nama_imunisasi']; ?></span>
                                        <?php if ($row['catatan']): ?>
                                            <p class="text-xs text-gray-400 mt-1 italic">"<?= htmlspecialchars($row['catatan']); ?>"
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass; ?>">
                                            <?= $row['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">

                                        <?php if ($row['status'] != 'Selesai' && $row['status'] != 'Batal'): ?>
                                            <div class="flex items-center gap-2">

                                                <button onclick="bukaModalSelesai(
                        '<?= $row['id']; ?>', 
                        '<?= htmlspecialchars($row['nama_balita'], ENT_QUOTES); ?>', 
                        '<?= $row['nama_imunisasi']; ?>'
                    )" class="bg-green-100 text-green-600 p-2 rounded-lg hover:bg-green-600 hover:text-white transition"
                                                    title="Selesaikan & Catat Riwayat">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>

                                                <form method="POST" action="" class="flex items-center">
                                                    <input type="hidden" name="janji_id" value="<?= $row['id']; ?>">
                                                    <select name="status_baru" onchange="this.form.submit()"
                                                        class="text-xs border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1.5 ml-1">
                                                        <option value="" disabled selected>Ubah...</option>
                                                        <option value="Disetujui">Setujui</option>
                                                        <option value="Batal">Batalkan</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">Selesai/Batal</span>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    <?php endif; ?>

</main>

<?php
require '../card_component/modal_tambah_anak.php';
require '../card_component/card_buat_janji.php';
?>
<div id="modalSelesai" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"
            onclick="tutupModalSelesai()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <form method="POST" action="">
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Konfirmasi Imunisasi
                    </h3>
                </div>

                <div class="px-6 py-6 space-y-4">
                    <input type="hidden" name="janji_id" id="selesai_janji_id">

                    <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                        <p class="text-xs text-gray-500">Pasien:</p>
                        <p class="font-bold text-gray-800" id="selesai_nama_anak">-</p>
                        <p class="text-xs text-gray-500 mt-2">Vaksin:</p>
                        <p class="font-bold text-green-700" id="selesai_vaksin">-</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Suntik (Realisasi)</label>
                        <input type="date" name="tgl_suntik" value="<?= date('Y-m-d'); ?>" required
                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bidan / Petugas</label>
                        <input type="text" name="bidan_penyuntik" placeholder="Contoh: Bidan Ani" required
                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="submit" name="proses_imunisasi"
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm">
                        Simpan & Selesaikan
                    </button>
                    <button type="button" onclick="tutupModalSelesai()"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bukaModalSelesai(id, nama, vaksin) {
        document.getElementById('modalSelesai').classList.remove('hidden');
        document.getElementById('selesai_janji_id').value = id;
        document.getElementById('selesai_nama_anak').innerText = nama;
        document.getElementById('selesai_vaksin').innerText = vaksin;
    }

    function tutupModalSelesai() {
        document.getElementById('modalSelesai').classList.add('hidden');
    }
</script>
<?php require '../layout/footer.php'; ?>