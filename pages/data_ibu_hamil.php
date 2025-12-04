<?php
require '../config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- LOGIKA 1: TAMBAH / EDIT DATA (UPSERT) ---
if (isset($_POST['simpan_ibu'])) {
    $id = $_POST['ibu_id']; // Jika ada ID, berarti Edit. Jika kosong, berarti Tambah.
    
    // Logika penentuan User ID
    // Jika Admin, ambil dari dropdown. Jika User, ambil dari session.
    $target_user_id = ($role == 'admin' && !empty($_POST['user_id'])) ? $_POST['user_id'] : $user_id;

    $nama = mysqli_real_escape_string($conn, $_POST['nama_ibu']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $hpht = $_POST['hpht'];
    $hpl = $_POST['hpl']; // Dikalkulasi otomatis di JS, tapi disimpan juga
    $lila = $_POST['lingkar_lengan'];
    $tb = $_POST['tinggi_badan'];
    $goldar = $_POST['golongan_darah'];
    $riwayat = mysqli_real_escape_string($conn, $_POST['riwayat_penyakit']);

    if (empty($id)) {
        // --- INSERT (CREATE) ---
        $query = "INSERT INTO ibu_hamil (user_id, nama_ibu, nik, hpht, hpl, lingkar_lengan, tinggi_badan, golongan_darah, riwayat_penyakit) 
                  VALUES ('$target_user_id', '$nama', '$nik', '$hpht', '$hpl', '$lila', '$tb', '$goldar', '$riwayat')";
        $msg = "Data Ibu Hamil berhasil ditambahkan!";
    } else {
        // --- UPDATE (EDIT) ---
        $query = "UPDATE ibu_hamil SET 
                  nama_ibu='$nama', nik='$nik', hpht='$hpht', hpl='$hpl', 
                  lingkar_lengan='$lila', tinggi_badan='$tb', golongan_darah='$goldar', riwayat_penyakit='$riwayat' 
                  WHERE id='$id'";
        $msg = "Data berhasil diperbarui!";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('$msg'); window.location='data_ibu_hamil.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}

// --- LOGIKA 2: HAPUS DATA ---
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    mysqli_query($conn, "DELETE FROM ibu_hamil WHERE id = '$id'");
    echo "<script>alert('Data dihapus.'); window.location='data_ibu_hamil.php';</script>";
}

// --- LOGIKA 3: AMBIL DATA ---
$keyword = isset($_GET['cari']) ? $_GET['cari'] : '';
if ($role == 'admin') {
    // Admin lihat semua
    $queryData = "SELECT i.*, u.nama_lengkap as nama_akun FROM ibu_hamil i LEFT JOIN users u ON i.user_id = u.id WHERE i.nama_ibu LIKE '%$keyword%' ORDER BY i.created_at DESC";
    // Ambil list user untuk dropdown tambah data
    $listUser = mysqli_query($conn, "SELECT id, nama_lengkap FROM users WHERE role='user'");
} else {
    // User lihat datanya sendiri
    $queryData = "SELECT * FROM ibu_hamil WHERE user_id = '$user_id' AND nama_ibu LIKE '%$keyword%'";
}
$result = mysqli_query($conn, $queryData);

require '../layout/header.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <nav class="flex text-sm text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-[#D0F246] transition">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-[#D0F246] font-bold">Data Ibu Hamil</span>
            </nav>
            <h1 class="text-3xl font-bold text-[#D0F246] mb-2">Pendataan Ibu Hamil</h1>
            <p class="text-gray-300">Monitoring kesehatan kehamilan (KIA) dengan data komprehensif.</p>
        </div>
        
        <div class="flex gap-3 w-full sm:w-auto">
            <form action="" method="GET" class="relative flex-grow sm:w-64">
                <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari nama ibu..." class="w-full pl-10 pr-4 py-3 border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] text-sm transition">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-[#D0F246]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>
            <button onclick="bukaModalTambah()" class="inline-flex items-center gap-2 px-6 py-3 border-0 text-sm font-bold rounded-[10px] text-[#1D3428] bg-[#D0F246] hover:bg-[#bce038] shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Data
            </button>
        </div>
    </div>

    <section class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[16px] shadow-lg border-2 border-[#D0F246] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#154620]">
                <thead class="bg-[#1D3428] border-b-2 border-[#D0F246]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Nama Ibu & NIK</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">HPHT & HPL</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Kesehatan (LILA/TB)</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Usia Kandungan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-[#D0F246] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#154620]">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            // Hitung Usia Kandungan (Minggu) dari HPHT
                            $hpht_date = new DateTime($row['hpht']);
                            $today = new DateTime();
                            $diff = $today->diff($hpht_date);
                            $weeks = floor($diff->days / 7);
                        ?>
                        <tr class="hover:bg-[#154620] transition">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-[#D0F246]"><?= htmlspecialchars($row['nama_ibu']); ?></div>
                                <div class="text-xs text-gray-400">NIK: <?= $row['nik']; ?></div>
                                <?php if($role == 'admin'): ?>
                                    <div class="text-[10px] text-blue-400 bg-blue-900/30 inline-block px-2 py-1 rounded-full mt-2 border border-blue-700/50">Akun: <?= $row['nama_akun'] ?? '-'; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-300"><span class="text-gray-500 text-xs">HPHT:</span> <?= date('d M Y', strtotime($row['hpht'])); ?></div>
                                <div class="text-sm font-bold text-[#D0F246]"><span class="text-gray-500 text-xs font-normal">HPL:</span> <?= date('d M Y', strtotime($row['hpl'])); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-300">LILA: <b class="text-[#D0F246]"><?= $row['lingkar_lengan']; ?></b> cm</div>
                                <div class="text-xs text-gray-400">TB: <?= $row['tinggi_badan']; ?> cm | Gol: <?= $row['golongan_darah']; ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-purple-900/30 text-purple-300 border border-purple-700/50">
                                    <?= $weeks; ?> Minggu
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex gap-2">
                                    <button onclick="bukaModalEdit(<?= htmlspecialchars(json_encode($row)); ?>)" class="text-[#D0F246] hover:text-white bg-[#D0F246]/20 hover:bg-[#D0F246]/30 p-2.5 rounded-[8px] transition border border-[#D0F246]/50 hover:border-[#D0F246]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <a href="?hapus_id=<?= $row['id']; ?>" onclick="return confirm('Hapus data ini?')" class="text-red-400 hover:text-red-300 bg-red-900/20 hover:bg-red-900/30 p-2.5 rounded-[8px] transition border border-red-700/50 hover:border-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Belum ada data ibu hamil.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="modalIbuHamil" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-[#1D3428] bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="tutupModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-gradient-to-b from-[#1D3428] to-[#0f1f18] rounded-[20px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border-2 border-[#D0F246]">
            <form method="POST" action="">
                <div class="bg-gradient-to-r from-[#D0F246] to-[#bce038] px-6 py-5 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-[#1D3428]" id="modal-title">Tambah Data Ibu Hamil</h3>
                    <button type="button" onclick="tutupModal()" class="text-[#1D3428] hover:opacity-70 font-bold text-xl">✕</button>
                </div>
                
                <div class="px-6 py-6 space-y-4">
                    <input type="hidden" name="ibu_id" id="ibu_id">

                    <?php if ($role == 'admin'): ?>
                    <div>
                        <label class="block text-sm font-bold text-[#D0F246] mb-2">Akun User (Opsional)</label>
                        <select name="user_id" id="user_id" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all">
                            <option value="" class="bg-[#1D3428]">-- Pilih Akun --</option>
                            <?php if(isset($listUser)): foreach($listUser as $usr): ?>
                                <option value="<?= $usr['id']; ?>" class="bg-[#1D3428]"><?= $usr['nama_lengkap']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-[#D0F246] mb-2">Nama Ibu</label>
                            <input type="text" name="nama_ibu" id="nama_ibu" required class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#D0F246] mb-2">NIK</label>
                            <input type="number" name="nik" id="nik" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5 bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[10px] border-2 border-[#D0F246]/30">
                        <div>
                            <label class="block text-sm font-bold text-[#D0F246] mb-2">HPHT (Haid Terakhir)</label>
                            <input type="date" name="hpht" id="hpht" required onchange="hitungHPL()" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#D0F246] mb-2">Estimasi HPL</label>
                            <input type="date" name="hpl" id="hpl" readonly class="block w-full bg-[#154620] border-2 border-[#D0F246]/30 text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm text-sm cursor-not-allowed">
                            <p class="text-[10px] text-[#D0F246]/70 mt-2">*Otomatis dihitung (Rumus Naegele)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#D0F246] mb-2">LILA (cm)</label>
                            <input type="number" step="0.01" name="lingkar_lengan" id="lila" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm sm:text-sm transition-all" placeholder="23.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#D0F246] mb-2">Tinggi (cm)</label>
                            <input type="number" step="0.01" name="tinggi_badan" id="tb" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm sm:text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#D0F246] mb-2">Gol. Darah</label>
                            <select name="golongan_darah" id="goldar" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm sm:text-sm transition-all">
                                <option value="Tidak Tahu" class="bg-[#1D3428]">Unknown</option>
                                <option value="A" class="bg-[#1D3428]">A</option>
                                <option value="B" class="bg-[#1D3428]">B</option>
                                <option value="AB" class="bg-[#1D3428]">AB</option>
                                <option value="O" class="bg-[#1D3428]">O</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#D0F246] mb-2">Riwayat Penyakit / Catatan</label>
                        <textarea name="riwayat_penyakit" id="riwayat" rows="3" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] py-3 px-4 shadow-sm sm:text-sm transition-all"></textarea>
                    </div>
                </div>

                <div class="bg-[#154620] px-6 py-4 flex flex-row-reverse gap-3 border-t border-[#D0F246]/20">
                    <button type="submit" name="simpan_ibu" class="w-full inline-flex justify-center rounded-[10px] border-0 shadow-lg px-4 py-3 bg-[#D0F246] text-[#1D3428] text-base font-bold hover:bg-[#bce038] hover:scale-105 focus:outline-none transition-all sm:w-auto sm:text-sm">Simpan Data</button>
                    <button type="button" onclick="tutupModal()" class="mt-3 w-full inline-flex justify-center rounded-[10px] border-2 border-[#D0F246]/30 shadow-sm px-4 py-3 bg-transparent text-[#D0F246] text-base font-bold hover:bg-[#D0F246]/10 focus:outline-none transition-all sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalIbuHamil');
    const modalTitle = document.getElementById('modal-title');

    function bukaModalTambah() {
        modal.classList.remove('hidden');
        modalTitle.innerText = "Tambah Data Ibu Hamil";
        document.getElementById('ibu_id').value = '';
        document.getElementById('nama_ibu').value = '';
        document.getElementById('nik').value = '';
        document.getElementById('hpht').value = '';
        document.getElementById('hpl').value = '';
        document.getElementById('lila').value = '';
        document.getElementById('tb').value = '';
        document.getElementById('goldar').value = 'Tidak Tahu';
        document.getElementById('riwayat').value = '';
        if(document.getElementById('user_id')) document.getElementById('user_id').value = '';
    }

    function bukaModalEdit(data) {
        modal.classList.remove('hidden');
        modalTitle.innerText = "Edit Data Ibu Hamil";
        
        // Isi Form dengan data JSON
        document.getElementById('ibu_id').value = data.id;
        document.getElementById('nama_ibu').value = data.nama_ibu;
        document.getElementById('nik').value = data.nik;
        document.getElementById('hpht').value = data.hpht;
        document.getElementById('hpl').value = data.hpl;
        document.getElementById('lila').value = data.lingkar_lengan;
        document.getElementById('tb').value = data.tinggi_badan;
        document.getElementById('goldar').value = data.golongan_darah;
        document.getElementById('riwayat').value = data.riwayat_penyakit;
        if(document.getElementById('user_id')) document.getElementById('user_id').value = data.user_id;
    }

    function tutupModal() {
        modal.classList.add('hidden');
    }

    // Hitung Hari Perkiraan Lahir (Rumus Naegele)
    // HPL = (Tanggal + 7), (Bulan - 3), (Tahun + 1)
    function hitungHPL() {
        const hphtVal = document.getElementById('hpht').value;
        if(hphtVal) {
            const hpht = new Date(hphtVal);
            // Tambah 280 hari (40 minggu) rata-rata kehamilan
            hpht.setDate(hpht.getDate() + 280); 
            
            const yyyy = hpht.getFullYear();
            const mm = String(hpht.getMonth() + 1).padStart(2, '0');
            const dd = String(hpht.getDate()).padStart(2, '0');
            
            document.getElementById('hpl').value = `${yyyy}-${mm}-${dd}`;
        }
    }
</script>

<?php require '../layout/footer.php'; ?>