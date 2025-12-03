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
    $balita_id = $_POST['balita_id']; // Opsional jika ingin ubah anak
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

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="w-full sm:w-2/3">
            <nav class="flex text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                <a href="dashboard.php" class="hover:text-blue-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Input Penimbangan</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Data Pengukuran & Pertumbuhan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data berat, tinggi, dan lingkar kepala balita.</p>

            <div class="mt-4 bg-white border rounded-2xl p-4 flex items-center gap-4 card-shadow">
                <div class="flex-shrink-0">
                    <div class="h-14 w-14 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c1.657 0 3-1.567 3-3.5S13.657 1 12 1 9 2.567 9 4.5 10.343 8 12 8zM6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="text-sm text-gray-500">Ringkasan</div>
                    <div class="flex items-baseline gap-6 mt-1">
                        <div>
                            <div class="text-2xl font-bold text-gray-900"><?= $totalCount; ?></div>
                            <div class="text-xs text-gray-500">Total data penimbangan</div>
                        </div>
                        <div class="border-l pl-4">
                            <div class="text-sm text-gray-700">Terakhir: <span class="font-medium"><?= $lastUpdate; ?></span></div>
                            <div class="text-xs text-gray-500">Tanggal pengukuran terbaru</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-shrink-0">
            <button onclick="bukaModalTambah()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Data Baru
            </button>
        </div>
    </div>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 table-responsive">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Anak</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Berat (Kg)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tinggi (Cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Lingkar (Cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    // Join Tabel Pengukuran dengan Balita
                    $queryList = "SELECT p.*, b.nama_balita 
                                  FROM pengukuran p 
                                  JOIN balita b ON p.balita_id = b.id 
                                  ORDER BY p.tgl_ukur DESC, p.id DESC";
                    $result = mysqli_query($conn, $queryList);

                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr class="hover:bg-gray-50 transition rounded-lg">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= date('d/m/Y', strtotime($row['tgl_ukur'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">
                            <?= htmlspecialchars($row['nama_balita']); ?>
                            <?php if($row['keterangan']): ?>
                                <p class="text-xs text-gray-400 font-normal mt-0.5 truncate w-32"><?= htmlspecialchars($row['keterangan']); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">
                            <?= $row['berat_badan']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?= $row['tinggi_badan']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?= $row['lingkar_kepala']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                            <button onclick="bukaModalEdit(
                                '<?= $row['id']; ?>',
                                '<?= $row['balita_id']; ?>',
                                '<?= $row['tgl_ukur']; ?>',
                                '<?= $row['berat_badan']; ?>',
                                '<?= $row['tinggi_badan']; ?>',
                                '<?= $row['lingkar_kepala']; ?>',
                                '<?= htmlspecialchars($row['keterangan'], ENT_QUOTES); ?>'
                            )" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-blue-600 border border-gray-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M11 9h6M11 13h6M5 5h.01M5 9h.01M5 13h.01M5 17h14"></path></svg>
                                Edit
                            </button>

                            <button onclick="event.preventDefault(); confirmDelete('Hapus data pengukuran ini?', '?hapus_id=<?= $row['id']; ?>');" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white hover:bg-red-50 text-red-600 border border-red-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="tutupModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form method="POST" action="">
                <div class="bg-blue-600 px-6 py-4">
                    <h3 class="text-lg leading-6 font-bold text-white" id="modal-judul">Input Penimbangan Baru</h3>
                </div>
                
                <div class="px-6 py-6 space-y-4">
                    <input type="hidden" name="ukur_id" id="ukur_id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Balita</label>
                        <select name="balita_id" id="balita_id" required class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="" disabled selected>-- Pilih Anak --</option>
                            <?php 
                            // Loop data balita untuk dropdown
                            mysqli_data_seek($dataBalita, 0);
                            while($b = mysqli_fetch_assoc($dataBalita)): 
                            ?>
                                <option value="<?= $b['id']; ?>"><?= $b['nama_balita']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengukuran</label>
                        <input type="date" name="tgl_ukur" id="tgl_ukur" value="<?= date('Y-m-d'); ?>" required class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Berat (Kg)</label>
                            <input type="number" step="0.01" name="berat_badan" id="berat_badan" required class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tinggi (Cm)</label>
                            <input type="number" step="0.01" name="tinggi_badan" id="tinggi_badan" class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Lingkar Kpl (Cm)</label>
                            <input type="number" step="0.01" name="lingkar_kepala" id="lingkar_kepala" class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Catatan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Contoh: Anak sehat, vitamin A diberikan."></textarea>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="submit" name="simpan_ukur" id="btn-simpan" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                        Simpan Data
                    </button>
                    <button type="button" onclick="tutupModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
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