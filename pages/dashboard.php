<?php
require '../config.php'; 

// Cek Sesi Login
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ==========================================
// BAGIAN 1: LOGIKA PROSES DATA (BACKEND) - TIDAK DIUBAH
// ==========================================

// --- [USER] A. Simpan Data Anak ---
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

// --- [USER] B. Buat Janji Imunisasi ---
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
        echo '<script>showAlert("error","Gagal","Gagal membuat janji.");</script>';
    }
}

// --- [ADMIN] C. Update Status Janji (Setuju/Batal) ---
if (isset($_POST['update_status']) && $role == 'admin') {
    $janji_id = $_POST['janji_id'];
    $status_baru = $_POST['status_baru'];
    
    if($status_baru == 'Selesai') {
        echo '<script>showAlert("info","Perhatian","Gunakan tombol Centang Hijau untuk menyelesaikan dan mencatat riwayat.");</script>';
    } else {
        if(mysqli_query($conn, "UPDATE janji_imunisasi SET status = '$status_baru' WHERE id = '$janji_id'")) {
            echo '<script>showAlert("success","Status Diperbarui","Status janji diubah menjadi '.addslashes($status_baru).'.","dashboard.php");</script>';
        }
    }
}

// --- [ADMIN] D. Selesaikan Janji & Catat Riwayat ---
if (isset($_POST['proses_imunisasi']) && $role == 'admin') {
    $janji_id = $_POST['janji_id'];
    $tgl_suntik = $_POST['tgl_suntik'];
    $bidan = mysqli_real_escape_string($conn, $_POST['bidan_penyuntik']);
    
    $cekJanji = mysqli_query($conn, "SELECT * FROM janji_imunisasi WHERE id = '$janji_id'");
    $dataJanji = mysqli_fetch_assoc($cekJanji);
    
    if ($dataJanji) {
        $insertRiwayat = "INSERT INTO riwayat_imunisasi (balita_id, imunisasi_id, tgl_suntik, bidan_penyuntik) 
                          VALUES ('$dataJanji[balita_id]', '$dataJanji[imunisasi_id]', '$tgl_suntik', '$bidan')";
        
        if (mysqli_query($conn, $insertRiwayat)) {
            mysqli_query($conn, "UPDATE janji_imunisasi SET status = 'Selesai' WHERE id = '$janji_id'");
            echo '<script>showAlert("success","Selesai","Imunisasi berhasil diselesaikan & tercatat di riwayat!","dashboard.php");</script>';
        } else {
            echo '<script>showAlert("error","Gagal","Gagal menyimpan riwayat.");</script>';
        }
    }
}

// ==========================================
// BAGIAN 2: PERSIAPAN DATA (FETCHING) - TIDAK DIUBAH
// ==========================================

if ($role == 'admin') {
    $balitaCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM balita"))['total'];
    $bumilCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ibu_hamil"))['total'];
    $pendingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM janji_imunisasi WHERE status = 'Menunggu'"))['total'];
    
    $queryJanji = "SELECT j.*, b.nama_balita, u.nama_lengkap as nama_ortu, m.nama_imunisasi 
                   FROM janji_imunisasi j 
                   JOIN balita b ON j.balita_id = b.id 
                   JOIN users u ON j.user_id = u.id 
                   JOIN master_imunisasi m ON j.imunisasi_id = m.id 
                   ORDER BY FIELD(j.status, 'Menunggu', 'Disetujui', 'Selesai', 'Batal'), j.tanggal_rencana ASC";
    $resultJanji = mysqli_query($conn, $queryJanji);

} else {
    $balitaCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM balita WHERE user_id = '$user_id'"))['total'];
    
    $queryJanji = "SELECT j.*, b.nama_balita, m.nama_imunisasi 
                   FROM janji_imunisasi j 
                   JOIN balita b ON j.balita_id = b.id 
                   JOIN master_imunisasi m ON j.imunisasi_id = m.id 
                   WHERE j.user_id = '$user_id' 
                   ORDER BY j.tanggal_rencana DESC";
    $resultJanji = mysqli_query($conn, $queryJanji);
}

$jadwalNext = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jadwal WHERE waktu_mulai > NOW() ORDER BY waktu_mulai ASC LIMIT 1"));
$queryAnak = mysqli_query($conn, "SELECT * FROM balita WHERE user_id = '$user_id'"); 
$queryImunisasi = mysqli_query($conn, "SELECT * FROM master_imunisasi ORDER BY id ASC");

require '../layout/header.php'; 
?>

<style>
    body { background-color: #2d4a38; /* Dark Forest Green Background */ color: #f0f0f0; }
    .font-theme { font-family: 'Plus Jakarta Sans', sans-serif; }
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 font-theme text-gray-100">
    
    <section aria-label="Welcome Banner" class="bg-[#1D3428] rounded-[40px] p-8 md:p-12 text-white shadow-xl mb-12 relative overflow-hidden group">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-block px-4 py-1.5 rounded-full bg-[#354f40] text-[#D0F246] text-sm font-semibold mb-4 border border-[#486354]">
                    👋 Dashboard Sistem Posyandu
                </div>
                <h1 class="text-3xl md:text-5xl font-bold tracking-tight leading-tight">
                    Halo, <span class="text-[#D0F246]"><?= htmlspecialchars($_SESSION['nama']); ?></span>
                </h1>
                <p class="mt-4 text-gray-300 text-lg md:text-xl font-light max-w-lg">
                    <?= $role == 'admin' ? "Ada <strong class='text-white'>$pendingCount</strong> janji temu yang perlu ditinjau hari ini." : 'Pantau tumbuh kembang anak dan jadwal imunisasi dengan lebih mudah.'; ?>
                </p>
                
                <div class="mt-8 flex flex-wrap gap-4">
                    <?php if($role == 'user'): ?>
                        <button onclick="toggleModalJanji()" class="inline-flex items-center gap-3 px-8 py-4 bg-[#D0F246] text-[#1D3428] rounded-full font-bold shadow-lg hover:bg-[#bce038] hover:scale-105 transition-all duration-300">
                            Buat Janji Temu
                            <i class="fas fa-plus"></i>
                        </button>
                    <?php else: ?>
                        <a href="#tabel-janji" class="inline-flex items-center gap-3 px-8 py-4 bg-[#D0F246] text-[#1D3428] rounded-full font-bold shadow-lg hover:bg-[#bce038] hover:scale-105 transition-all duration-300">
                            Cek Permintaan
                            <i class="fas fa-arrow-down"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="hidden md:block opacity-90">
                <div class="h-32 w-32 bg-[#D0F246] rounded-full blur-3xl absolute top-0 right-0 opacity-20"></div>
                <div class="h-40 w-40 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                    <span class="text-6xl">✨</span>
                </div>
            </div>
        </div>
        
        <svg class="absolute right-0 bottom-0 h-full w-2/3 text-white/5 pointer-events-none" fill="currentColor" viewBox="0 0 100 100"><circle cx="100" cy="100" r="80"/></svg>
    </section>

    <?php if ($role == 'user'): ?>
        
        <section aria-labelledby="daftar-anak-title" class="mb-16">
            <header class="mb-8 flex justify-between items-end">
                <div>
                    <h2 id="daftar-anak-title" class="text-3xl font-bold text-[#D0F246]">Buah Hati</h2>
                    <p class="text-gray-300 mt-2 font-medium">Data pertumbuhan keluarga Anda.</p>
                </div>
                <button onclick="toggleModal()" class="group flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-gray-200 text-[#1D3428] font-bold rounded-full hover:border-[#D0F246] hover:text-[#D0F246] transition-all duration-300">
                    <span class="bg-[#1D3428] text-white rounded-full w-6 h-6 flex items-center justify-center text-sm group-hover:scale-110 transition">+</span>
                    Tambah Anak
                </button>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                if(mysqli_num_rows($queryAnak) > 0) {
                    mysqli_data_seek($queryAnak, 0); 
                    while($anak = mysqli_fetch_assoc($queryAnak)): 
                        $umur = (new DateTime())->diff(new DateTime($anak['tgl_lahir']));
                        // Style Gender: Softened for theme integration
                        $bgIcon = $anak['jenis_kelamin'] == 'L' ? 'bg-[#E3F2FD] text-[#1565C0]' : 'bg-[#FCE4EC] text-[#AD1457]';
                        $iconClass = $anak['jenis_kelamin'] == 'L' ? 'fas fa-mars' : 'fas fa-venus';
                ?>
                <article class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] p-6 shadow-lg border-l-4 border-[#D0F246] hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col h-full relative group overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="h-8 w-8 bg-[#D0F246] rounded-lg flex items-center justify-center text-[#1D3428] text-sm">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                    
                    <div class="<?= $bgIcon; ?> h-16 w-16 rounded-[24px] flex items-center justify-center text-2xl mb-6 shadow-inner">
                        <i class="<?= $iconClass; ?>"></i>
                    </div>
                    
                    <h4 class="text-xl font-bold text-[#D0F246] mb-1"><?= htmlspecialchars($anak['nama_balita']); ?></h4>
                    <p class="text-sm text-gray-300 mb-6 font-medium tracking-wide"><?= $anak['nik']; ?></p>
                    
                    <div class="mt-auto pt-6 border-t border-[#154620]">
                         <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Usia</span>
                            <span class="text-sm font-bold text-[#D0F246] bg-[#154620] px-4 py-1.5 rounded-full">
                                <?= $umur->y; ?> Thn <?= $umur->m; ?> Bln
                            </span>
                         </div>
                    </div>
                    
                    <a href="detail_anak.php?id=<?= $anak['id']; ?>" class="absolute inset-0 z-10" aria-label="Lihat detail anak"></a>
                </article>
                <?php 
                    endwhile; 
                } else {
                    echo '
                    <div class="col-span-full py-16 text-center bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] border-2 border-dashed border-[#D0F246]">
                        <div class="text-6xl mb-4 text-[#D0F246]"><i class="fas fa-baby"></i></div>
                        <p class="text-gray-300 font-medium">Belum ada data anak.</p>
                        <button onclick="toggleModal()" class="mt-4 text-[#D0F246] font-bold hover:text-[#bce038] transition">Tambah Sekarang</button>
                    </div>';
                }
                ?>
            </div>
        </section>

        <section>
             <h2 class="text-3xl font-bold text-[#D0F246] mb-6">Janji Temu Aktif</h2>
             <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] shadow-lg border border-[#D0F246]/30 overflow-hidden p-2">
                <div class="overflow-x-auto rounded-[20px] bg-[#1D3428]">
                    <table class="min-w-full">
                        <thead class="bg-[#1D3428] border-b-2 border-[#D0F246]">
                            <tr>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Tanggal</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Detail Pasien</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#154620]">
                            <?php if(mysqli_num_rows($resultJanji) > 0): ?>
                                <?php while($j = mysqli_fetch_assoc($resultJanji)):
                                    $statusStyle = match($j['status']) { 
                                        'Menunggu'=>'bg-orange-900/30 text-orange-300 border border-orange-700', 
                                        'Disetujui'=>'bg-[#D0F246] text-[#1D3428] border border-[#bce038]', 
                                        'Selesai'=>'bg-blue-900/30 text-blue-300 border border-blue-700', 
                                        'Batal'=>'bg-red-900/30 text-red-300 border border-red-700' 
                                    };
                                ?>
                                <tr class="hover:bg-[#154620] transition">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="text-sm font-bold text-[#D0F246]"><?= date('d M Y', strtotime($j['tanggal_rencana'])); ?></div>
                                        <div class="text-xs text-gray-400 font-medium"><?= date('l', strtotime($j['tanggal_rencana'])); ?></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-[#D0F246]/20 text-[#D0F246] flex items-center justify-center text-sm mr-3">
                                                <i class="fas fa-syringe"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-[#D0F246]"><?= htmlspecialchars($j['nama_balita']); ?></div>
                                                <div class="text-xs text-gray-400"><?= $j['nama_imunisasi']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-4 py-1.5 text-xs font-bold rounded-full inline-block <?= $statusStyle; ?>">
                                            <?= $j['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="px-8 py-10 text-center text-gray-400">Belum ada riwayat janji temu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    <?php else: ?>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] border-l-4 border-[#FFC107] shadow-lg p-6 flex items-center gap-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="h-16 w-16 bg-[#FFF8E1] text-[#F57F17] rounded-[20px] flex items-center justify-center text-2xl shadow-md">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">Menunggu</p>
                    <p class="text-4xl font-extrabold text-[#D0F246] drop-shadow-sm"><?= $pendingCount; ?></p>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] border-l-4 border-[#4CAF50] shadow-lg p-6 flex items-center gap-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="h-16 w-16 bg-[#E0F2F1] text-[#00695C] rounded-[20px] flex items-center justify-center text-2xl shadow-md">
                    <i class="fas fa-baby"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">Total Balita</p>
                    <p class="text-4xl font-extrabold text-[#D0F246] drop-shadow-sm"><?= $balitaCount; ?></p>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] border-l-4 border-[#E91E63] shadow-lg p-6 flex items-center gap-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="h-16 w-16 bg-[#FCE4EC] text-[#AD1457] rounded-[20px] flex items-center justify-center text-2xl shadow-md">
                    <i class="fas fa-female"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">Ibu Hamil</p>
                    <p class="text-4xl font-extrabold text-[#D0F246] drop-shadow-sm\"><?= $bumilCount ?? 0; ?></p>
                </div>
            </div>
        </section>

        <?php require '../layout/admin_stats.php'; ?>

        <section aria-labelledby="admin-menu-title" class="mb-16">
            <h2 id="admin-menu-title" class="text-2xl font-bold text-[#D0F246] mb-6">Menu Operasional</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php 
                // Dark theme cards matching cetak_laporan style
                $menuItems = [
                    ['link' => 'edit_detail_balita.php', 'icon' => '<i class="fas fa-balance-scale"></i>', 'label' => 'Input Penimbangan'],
                    ['link' => 'data_ibu_hamil.php', 'icon' => '<i class="fas fa-female"></i>', 'label' => 'Data Ibu Hamil'],
                    ['link' => 'data_anak.php', 'icon' => '<i class="fas fa-clipboard-list"></i>', 'label' => 'Master Data Balita'],
                    ['link' => '#', 'icon' => '<i class="fas fa-calendar-alt"></i>', 'label' => 'Kelola Jadwal']
                ];
                foreach($menuItems as $item): ?>
                <a href="<?= $item['link']; ?>" class="group bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-8 rounded-[16px] border-2 border-[#D0F246] shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center justify-center text-center gap-4 relative overflow-hidden min-h-[220px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#D0F246]/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="h-14 w-14 bg-[#D0F246]/10 text-[#D0F246] rounded-full flex items-center justify-center text-2xl backdrop-blur-sm border border-[#D0F246]/30 group-hover:bg-[#D0F246]/20 transition-all relative z-10">
                        <?= $item['icon']; ?>
                    </div>
                    <span class="font-bold text-[#D0F246] group-hover:text-white transition text-sm relative z-10"><?= $item['label']; ?></span>
                </a>
                <?php endforeach; ?>
                
                <a href="cetak_laporan.php" target="_blank" class="group bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-8 rounded-[16px] border-2 border-[#D0F246] shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center justify-center text-center gap-4 relative overflow-hidden min-h-[220px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#D0F246]/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="h-14 w-14 bg-[#D0F246]/10 text-[#D0F246] rounded-full flex items-center justify-center text-2xl backdrop-blur-sm border border-[#D0F246]/30 group-hover:bg-[#D0F246]/20 transition-all relative z-10">
                        <i class="fas fa-print"></i>
                    </div>
                    <div class="relative z-10">
                        <span class="font-bold text-[#D0F246] group-hover:text-white transition text-sm block">Cetak Laporan</span>
                        <span class="text-xs text-gray-400 block">Export PDF</span>
                    </div>
                </a>
            </div>
        </section>

        <section id="tabel-janji" aria-labelledby="konfirmasi-janji-title">
            <div class="flex items-center justify-between mb-6">
                <h2 id="konfirmasi-janji-title" class="text-2xl font-bold text-[#D0F246]">Permintaan Masuk</h2>
                <span class="bg-gradient-to-r from-[#D0F246]/10 to-[#D0F246]/5 text-[#D0F246] text-xs font-bold px-4 py-2 rounded-full uppercase tracking-wider border border-[#D0F246]/30">Prioritas</span>
            </div>

            <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[24px] shadow-lg border border-[#D0F246]/30 overflow-hidden p-2">
                <div class="overflow-x-auto rounded-[20px] bg-[#1D3428]">
                    <table class="min-w-full text-sm">
                        <thead class="bg-[#1D3428] border-b-2 border-[#D0F246]">
                            <tr>
                                <th class="px-6 py-5 text-left font-bold text-[#D0F246]">Tanggal</th>
                                <th class="px-6 py-5 text-left font-bold text-[#D0F246]">Pasien</th>
                                <th class="px-6 py-5 text-left font-bold text-[#D0F246]">Layanan</th>
                                <th class="px-6 py-5 text-left font-bold text-[#D0F246]">Status</th>
                                <th class="px-6 py-5 text-center font-bold text-[#D0F246]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#154620]">
                            <?php if (mysqli_num_rows($resultJanji) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($resultJanji)): 
                                    $statusClass = match($row['status']) {
                                        'Menunggu' => 'bg-amber-900/30 text-amber-300 border border-amber-700',
                                        'Disetujui' => 'bg-blue-900/30 text-blue-300 border border-blue-700',
                                        'Selesai' => 'bg-emerald-900/30 text-emerald-300 border border-emerald-700',
                                        'Batal' => 'bg-red-900/30 text-red-300 border border-red-700',
                                    };
                                ?>
                                <tr class="hover:bg-[#154620] transition border-b border-[#154620]">
                                    <td class="px-6 py-5 font-bold text-[#D0F246]">
                                        <?= date('d M Y', strtotime($row['tanggal_rencana'])); ?>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-[#D0F246]"><?= htmlspecialchars($row['nama_balita']); ?></div>
                                        <div class="text-xs text-gray-400 mt-1">Ortu: <?= htmlspecialchars($row['nama_ortu']); ?></div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-[#D0F246]"></span>
                                            <span class="text-gray-300"><?= $row['nama_imunisasi']; ?></span>
                                        </div>
                                        <?php if($row['catatan']): ?>
                                            <div class="text-xs text-gray-400 mt-1 italic pl-4 border-l-2 border-[#D0F246]/30">"<?= htmlspecialchars($row['catatan']); ?>"</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1.5 rounded-full text-xs font-bold <?= $statusClass; ?>">
                                            <?= $row['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <?php if($row['status'] != 'Selesai' && $row['status'] != 'Batal'): ?>
                                            <div class="flex justify-center items-center gap-3">
                                                <button onclick="bukaModalSelesai(
                                                    '<?= $row['id']; ?>', 
                                                    '<?= htmlspecialchars($row['nama_balita'], ENT_QUOTES); ?>', 
                                                    '<?= $row['nama_imunisasi']; ?>'
                                                )" class="h-10 w-10 rounded-xl bg-[#D0F246]/20 text-[#D0F246] hover:bg-[#D0F246] hover:text-[#1D3428] transition flex items-center justify-center border border-[#D0F246]/40 shadow-sm" title="Selesaikan">
                                                    <i class="fas fa-check"></i>
                                                </button>

                                                <form method="POST" action="" class="inline-block">
                                                    <input type="hidden" name="janji_id" value="<?= $row['id']; ?>">
                                                    <input type="hidden" name="update_status" value="1">
                                                    <div class="relative">
                                                        <select name="status_baru" onchange="this.form.submit()" class="appearance-none bg-[#2d4a38] border border-[#154620] text-[#D0F246] text-xs font-bold py-2.5 pl-4 pr-10 rounded-xl cursor-pointer hover:border-[#D0F246] focus:outline-none focus:ring-2 focus:ring-[#D0F246] transition-all">
                                                            <option value="" disabled selected class="bg-[#1D3428] text-[#D0F246]">Aksi...</option>
                                                            <option value="Disetujui" class="bg-[#1D3428] text-[#D0F246]">Setujui</option>
                                                            <option value="Batal" class="bg-[#1D3428] text-[#D0F246]">Batalkan</option>
                                                        </select>
                                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#D0F246]">
                                                            <i class="fas fa-chevron-down text-xs"></i>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-300 text-xs font-medium bg-[#154620] px-3 py-1 rounded-full border border-[#D0F246]/30">Selesai/Batal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Tidak ada permintaan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    <?php endif; ?>

</main>

<?php 
// Modals tetap dipanggil
require '../card_component/modal_tambah_anak.php'; 
require '../card_component/card_buat_janji.php'; 
?>

<div id="modalSelesai" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-[#1D3428] bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="tutupModalSelesai()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-[#FBFBF9] rounded-[32px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-white/10">
            <form method="POST" action="">
                <div class="bg-[#1D3428] px-8 py-6 flex items-center gap-4">
                    <div class="h-12 w-12 flex items-center justify-center bg-[#D0F246] rounded-full text-[#1D3428] text-xl">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Selesaikan Imunisasi</h3>
                        <p class="text-[#D0F246] text-xs mt-1">Konfirmasi & simpan riwayat</p>
                    </div>
                </div>
                
                <div class="px-8 py-8 space-y-6">
                    <input type="hidden" name="janji_id" id="selesai_janji_id">
                    
                    <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-sm">
                        <div class="flex justify-between mb-3 border-b border-gray-100 pb-2">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Pasien</span>
                            <span class="font-bold text-[#1D3428]" id="selesai_nama_anak">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Jenis Vaksin</span>
                            <span class="font-bold text-[#1D3428]" id="selesai_vaksin">-</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#1D3428] mb-2">Tanggal Disuntik</label>
                        <input type="date" name="tgl_suntik" value="<?= date('Y-m-d'); ?>" required class="block w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#1D3428] focus:border-[#1D3428] text-sm bg-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-[#1D3428] mb-2">Bidan / Petugas</label>
                        <input type="text" name="bidan_penyuntik" placeholder="Nama Bidan..." required class="block w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#1D3428] focus:border-[#1D3428] text-sm bg-white">
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-6 flex flex-row-reverse gap-3">
                    <button type="submit" name="proses_imunisasi" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-[#1D3428] text-base font-bold text-white hover:bg-[#14241c] hover:scale-105 transform transition-all focus:outline-none">
                        Simpan Data
                    </button>
                    <button type="button" onclick="tutupModalSelesai()" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 focus:outline-none transition">
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