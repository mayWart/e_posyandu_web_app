<?php
require '../config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// LOGIKA QUERY
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

if ($role == 'admin') {
    $query = "SELECT b.*, u.nama_lengkap as nama_ortu FROM balita b JOIN users u ON b.user_id = u.id WHERE b.nama_balita LIKE '%$keyword%' ORDER BY b.nama_balita ASC";
} else {
    $query = "SELECT * FROM balita WHERE user_id = '$user_id' AND nama_balita LIKE '%$keyword%' ORDER BY nama_balita ASC";
}
$result = mysqli_query($conn, $query);

require '../layout/header.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $role == 'admin' ? 'Master Data Balita' : 'Daftar Buah Hati Anda'; ?></h1>
            <p class="text-sm text-gray-500 mt-1">Klik pada kartu anak untuk melihat detail lengkap.</p>
        </div>
        <form action="" method="GET" class="relative w-full sm:w-64">
            <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari nama anak..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm transition">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($anak = mysqli_fetch_assoc($result)): 
                $umur = (new DateTime())->diff(new DateTime($anak['tgl_lahir']));
                $gender = strtoupper(trim($anak['jenis_kelamin'] ?? '')) === 'L' ? 'L' : 'P';
                $genderLabel = $gender === 'L' ? 'Laki-laki' : 'Perempuan';
                $initial = strtoupper(substr(trim($anak['nama_balita']), 0, 1));
            ?>

            <article onclick="showDetailModal('<?= $anak['id']; ?>')" class="cursor-pointer bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-blue-200 transition-all group relative transform hover:-translate-y-1">

                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold <?= $gender === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700'; ?>">
                        <?= $genderLabel; ?>
                    </span>
                </div>

                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="h-14 w-14 rounded-full flex items-center justify-center text-lg font-semibold text-white <?= $gender === 'L' ? 'bg-blue-500' : 'bg-pink-500'; ?>">
                            <?= htmlspecialchars($initial); ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition"><?= htmlspecialchars($anak['nama_balita']); ?></h3>
                            <p class="text-sm text-gray-500"><?= $umur->y; ?> thn <?= $umur->m; ?> bln</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500 border-t pt-3">
                        <span>Lahir: <?= date('d M Y', strtotime($anak['tgl_lahir'])); ?></span>
                        <span class="text-blue-600 font-medium flex items-center gap-2">
                            <span>Lihat Detail</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200">
            <p class="text-gray-500">Data tidak ditemukan.</p>
        </div>
    <?php endif; ?>

</main>

<div id="modalDetail" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeDetailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-gray-900">Detail Balita</h3>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div id="modalDetailBody" class="px-6 py-6 bg-white min-h-[300px]">
                <div class="flex flex-col items-center justify-center h-full py-10">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                    <p class="mt-3 text-sm text-gray-500">Memuat data...</p>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeDetailModal()" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let myChartInstance = null; // Variabel global untuk menyimpan instance chart

    function showDetailModal(id) {
        // 1. Buka Modal
        const modal = document.getElementById('modalDetail');
        const body = document.getElementById('modalDetailBody');
        modal.classList.remove('hidden');
        
        // 2. Tampilkan Loading State
        body.innerHTML = `
            <div class="flex flex-col items-center justify-center h-48">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                <p class="mt-3 text-sm text-gray-500">Mengambil data...</p>
            </div>
        `;

        // 3. Panggil Data via AJAX Fetch
        fetch(`ajax_get_detail.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                // Masukkan HTML ke Body
                body.innerHTML = html;

                // 4. Render Chart (Karena Chart butuh Script JS yang tidak jalan otomatis via innerHTML)
                initChart();
            })
            .catch(err => {
                body.innerHTML = '<p class="text-center text-red-500">Gagal memuat data.</p>';
            });
    }

    function closeDetailModal() {
        document.getElementById('modalDetail').classList.add('hidden');
    }

    // Fungsi Render Chart Manual
    function initChart() {
        const ctx = document.getElementById('chartModalCanvas');
        if (!ctx) return; // Jika tidak ada data chart

        // Hancurkan chart lama jika ada (untuk mencegah overlap)
        if (myChartInstance) {
            myChartInstance.destroy();
        }

        // Ambil data dari hidden input yang dikirim ajax_get_detail.php
        const labels = JSON.parse(document.getElementById('ajax-chart-labels').value);
        const dataBB = JSON.parse(document.getElementById('ajax-chart-data').value);

        myChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Berat Badan (Kg)',
                    data: dataBB,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563EB',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: false, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>

<?php require '../layout/footer.php'; ?>