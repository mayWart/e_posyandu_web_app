<?php
require '../config.php';

// Cek Keamanan: Hanya Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// --- LOGIKA 1: TAMBAH DATA BARU (CREATE) ---
if (isset($_POST['simpan_ukur'])) {
    $balita_id = $_POST['balita_id'];
    $tgl = $_POST['tgl_ukur'];
    $bb = $_POST['berat_badan'];
    $tb = $_POST['tinggi_badan'];
    $lk = $_POST['lingkar_kepala'];
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query = "INSERT INTO pengukuran (balita_id, tgl_ukur, berat_badan, tinggi_badan, lingkar_kepala, keterangan) 
              VALUES ('$balita_id', '$tgl', '$bb', '$tb', '$lk', '$ket')";

    if (mysqli_query($conn, $query)) {
        echo '<script>showAlert("success","Berhasil","Data penimbangan berhasil disimpan!","data_penimbangan.php");</script>';
    } else {
        $err = addslashes(mysqli_error($conn));
        echo '<script>showAlert("error","Gagal","Gagal: ' . $err . '");</script>';
    }
}

// --- LOGIKA 2: UPDATE DATA (EDIT) ---
if (isset($_POST['update_ukur'])) {
    $id = $_POST['ukur_id'];
    $balita_id = $_POST['balita_id']; 
    $tgl = $_POST['tgl_ukur'];
    $bb = $_POST['berat_badan'];
    $tb = $_POST['tinggi_badan'];
    $lk = $_POST['lingkar_kepala'];
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $queryUpdate = "UPDATE pengukuran SET 
                    balita_id = '$balita_id',
                    tgl_ukur = '$tgl', 
                    berat_badan = '$bb', 
                    tinggi_badan = '$tb', 
                    lingkar_kepala = '$lk', 
                    keterangan = '$ket' 
                    WHERE id = '$id'";

    if (mysqli_query($conn, $queryUpdate)) {
        echo '<script>showAlert("success","Berhasil","Data berhasil diperbarui!","data_penimbangan.php");</script>';
    }
}

// --- LOGIKA 3: HAPUS DATA ---
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    mysqli_query($conn, "DELETE FROM pengukuran WHERE id = '$id'");
    echo '<script>window.location="data_penimbangan.php";</script>';
}

// Ambil Data Balita untuk Dropdown
$dataBalita = mysqli_query($conn, "SELECT id, nama_balita FROM balita ORDER BY nama_balita ASC");

require '../layout/header.php';
?>

<style>
    .fade-up { animation: fadeUp .36s cubic-bezier(.2,.9,.2,1) both; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .card-shadow { box-shadow: 0 6px 18px rgba(13, 38, 76, 0.06); }
    .btn-primary { background: linear-gradient(90deg,#2563eb,#1e40af); }
    @media (max-width: 640px) {
        .table-responsive td { display: block; width: 100%; }
        .table-responsive tr { display: block; margin-bottom: .75rem; }
        .table-responsive thead { display: none; }
    }
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-up">
    
    <?php
        // Small stats for hero
        $totalCountRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengukuran");
        $totalCountRow = mysqli_fetch_assoc($totalCountRes);
        $totalCount = $totalCountRow ? intval($totalCountRow['cnt']) : 0;

        $lastDateRes = mysqli_query($conn, "SELECT MAX(tgl_ukur) as maxd FROM pengukuran");
        $lastDateRow = mysqli_fetch_assoc($lastDateRes);
        $lastUpdate = $lastDateRow && $lastDateRow['maxd'] ? date('d M Y', strtotime($lastDateRow['maxd'])) : '-';
    ?>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div class="w-full sm:w-2/3">
            <nav class="flex text-sm text-gray-400 mb-2" aria-label="Breadcrumb">
                <a href="dashboard.php" class="hover:text-[#D0F246] transition">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-[#D0F246] font-bold">Input Penimbangan</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold text-[#D0F246] mb-2">Data Pengukuran Balita</h1>
            <p class="text-gray-300 text-base">Kelola data berat, tinggi, dan lingkar kepala untuk monitoring pertumbuhan optimal.</p>

            <div class="mt-6 bg-gradient-to-br from-[#2d4a38] to-[#1D3428] border-2 border-[#D0F246] rounded-[16px] p-6 flex items-center gap-6 shadow-lg">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 rounded-[12px] bg-gradient-to-br from-[#D0F246] to-[#bce038] flex items-center justify-center text-[#1D3428] text-2xl font-bold">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-baseline gap-8">
                        <div>
                            <div class="text-3xl font-bold text-[#D0F246]"><?= $totalCount; ?></div>
                            <div class="text-xs text-gray-400 mt-1">Total data penimbangan</div>
                        </div>
                        <div class="border-l-2 border-[#D0F246]/30 pl-8">
                            <div class="text-sm text-gray-300">Terakhir diperbarui:</div>
                            <div class="text-lg font-bold text-[#D0F246] mt-1"><?= $lastUpdate; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-shrink-0">
            <button onclick="bukaModalTambah()" class="inline-flex items-center gap-2 px-6 py-3 border-0 text-sm font-bold rounded-[12px] text-[#1D3428] bg-[#D0F246] hover:bg-[#bce038] shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Data Baru
            </button>
        </div>
    </div>

    <section class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-2xl shadow-lg border border-[#D0F246]/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#154620] table-responsive">
                <thead class="bg-[#1D3428] border-b-2 border-[#D0F246]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-[#D0F246] uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-[#D0F246] uppercase">Nama Anak</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-[#D0F246] uppercase">Berat (Kg)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-[#D0F246] uppercase">Tinggi (Cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-[#D0F246] uppercase">Lingkar (Cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-[#D0F246] uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-[#1D3428] divide-y divide-[#154620]">
                    <?php
                    // Join Tabel Pengukuran dengan Balita
                    $queryList = "SELECT p.*, b.nama_balita 
                                  FROM pengukuran p 
                                  JOIN balita b ON p.balita_id = b.id 
                                  ORDER BY p.tgl_ukur DESC, p.id DESC";
                    $result = mysqli_query($conn, $queryList);

                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr class="hover:bg-[#154620] transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-300">
                            <?= date('d/m/Y', strtotime($row['tgl_ukur'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#D0F246]">
                            <?= htmlspecialchars($row['nama_balita']); ?>
                            <?php if($row['keterangan']): ?>
                                <p class="text-xs text-gray-400 font-normal mt-0.5 truncate w-32"><?= htmlspecialchars($row['keterangan']); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-[#D0F246]">
                            <?= $row['berat_badan']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            <?= $row['tinggi_badan']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            <?= $row['lingkar_kepala']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-2">
                            <button onclick="bukaModalEdit(
                                '<?= $row['id']; ?>',
                                '<?= $row['balita_id']; ?>',
                                '<?= $row['tgl_ukur']; ?>',
                                '<?= $row['berat_badan']; ?>',
                                '<?= $row['tinggi_badan']; ?>',
                                '<?= $row['lingkar_kepala']; ?>',
                                '<?= htmlspecialchars($row['keterangan'], ENT_QUOTES); ?>'
                            )" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[10px] bg-[#D0F246]/20 hover:bg-[#D0F246]/30 text-[#D0F246] border border-[#D0F246]/50 hover:border-[#D0F246] transition-all duration-200 font-medium text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M11 9h6M11 13h6M5 5h.01M5 9h.01M5 13h.01M5 17h14"></path></svg>
                                Edit
                            </button>

                            <button onclick="event.preventDefault(); confirmDelete('Hapus data pengukuran ini?', '?hapus_id=<?= $row['id']; ?>');" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[10px] bg-red-900/20 hover:bg-red-900/30 text-red-400 border border-red-700/50 hover:border-red-700 transition-all duration-200 font-medium text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="modalUkur" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-[#1D3428] bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="tutupModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-gradient-to-b from-[#1D3428] to-[#0f1f18] rounded-[20px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border-2 border-[#D0F246]">
            <form method="POST" action="">
                <div class="bg-gradient-to-r from-[#D0F246] to-[#bce038] px-6 py-5 flex items-center gap-3">
                    <svg class="w-6 h-6 text-[#1D3428]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <h3 class="text-lg leading-6 font-bold text-[#1D3428]" id="modal-judul">Input Penimbangan Baru</h3>
                </div>
                
                <div class="px-6 py-6 space-y-4">
                    <input type="hidden" name="ukur_id" id="ukur_id">

                    <div>
                        <label class="block text-sm font-bold text-[#D0F246] mb-2">Nama Balita</label>
                        <select name="balita_id" id="balita_id" required class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] shadow-sm py-3 px-4 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all">
                            <option value="" disabled selected class="bg-[#1D3428]">-- Pilih Anak --</option>
                            <?php 
                            // Loop data balita untuk dropdown
                            mysqli_data_seek($dataBalita, 0);
                            while($b = mysqli_fetch_assoc($dataBalita)): 
                            ?>
                                <option value="<?= $b['id']; ?>" class="bg-[#1D3428]"><?= $b['nama_balita']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#D0F246] mb-2">Tanggal Pengukuran</label>
                        <input type="date" name="tgl_ukur" id="tgl_ukur" value="<?= date('Y-m-d'); ?>" required class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] shadow-sm py-3 px-4 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all">
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#D0F246] mb-2">Berat (Kg)</label>
                            <input type="number" step="0.01" name="berat_badan" id="berat_badan" required class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] shadow-sm py-2 px-3 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#D0F246] mb-2">Tinggi (Cm)</label>
                            <input type="number" step="0.01" name="tinggi_badan" id="tinggi_badan" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] shadow-sm py-2 px-3 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#D0F246] mb-2">Lingkar Kpl (Cm)</label>
                            <input type="number" step="0.01" name="lingkar_kepala" id="lingkar_kepala" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] shadow-sm py-2 px-3 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#D0F246] mb-2">Keterangan / Catatan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="block w-full border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] shadow-sm py-3 px-4 focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm transition-all" placeholder="Contoh: Anak sehat, vitamin A diberikan."></textarea>
                    </div>
                </div>

                <div class="bg-[#154620] px-6 py-4 flex flex-row-reverse gap-3 border-t border-[#D0F246]/20">
                    <button type="submit" name="simpan_ukur" id="btn-simpan" class="w-full inline-flex justify-center rounded-[10px] border-0 shadow-lg px-4 py-3 bg-[#D0F246] text-[#1D3428] text-base font-bold hover:bg-[#bce038] hover:scale-105 focus:outline-none transition-all sm:w-auto sm:text-sm">
                        Simpan Data
                    </button>
                    <button type="button" onclick="tutupModal()" class="mt-3 w-full inline-flex justify-center rounded-[10px] border-2 border-[#D0F246]/30 shadow-sm px-4 py-3 bg-transparent text-[#D0F246] text-base font-bold hover:bg-[#D0F246]/10 focus:outline-none transition-all sm:mt-0 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalUkur');
    const judul = document.getElementById('modal-judul');
    const btnSimpan = document.getElementById('btn-simpan');
    
    // Mode Tambah
    function bukaModalTambah() {
        modal.classList.remove('hidden');
        judul.innerText = "Input Penimbangan Baru";
        btnSimpan.name = "simpan_ukur";
        btnSimpan.innerText = "Simpan Data";
        
        // Reset Form
        document.getElementById('ukur_id').value = '';
        document.getElementById('balita_id').value = '';
        document.getElementById('berat_badan').value = '';
        document.getElementById('tinggi_badan').value = '';
        document.getElementById('lingkar_kepala').value = '';
        document.getElementById('keterangan').value = '';
    }

    // Mode Edit (Isi form dengan data lama)
    function bukaModalEdit(id, balitaId, tgl, bb, tb, lk, ket) {
        modal.classList.remove('hidden');
        judul.innerText = "Edit Data Penimbangan";
        btnSimpan.name = "update_ukur";
        btnSimpan.innerText = "Simpan Perubahan";

        document.getElementById('ukur_id').value = id;
        document.getElementById('balita_id').value = balitaId;
        document.getElementById('tgl_ukur').value = tgl;
        document.getElementById('berat_badan').value = bb;
        document.getElementById('tinggi_badan').value = tb;
        document.getElementById('lingkar_kepala').value = lk;
        document.getElementById('keterangan').value = ket;
    }

    function tutupModal() {
        modal.classList.add('hidden');
    }
</script>

<?php require '../layout/footer.php'; ?>