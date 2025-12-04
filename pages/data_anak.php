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
            <nav class="flex text-sm text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-[#D0F246] transition">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-[#D0F246] font-bold">Master Data Balita</span>
            </nav>
            <h1 class="text-3xl font-bold text-[#D0F246] mb-2"><?= $role == 'admin' ? 'Master Data Balita' : 'Daftar Buah Hati Anda'; ?></h1>
            <p class="text-gray-300">Klik pada kartu anak untuk melihat detail lengkap dan monitoring pertumbuhan.</p>
        </div>
        <form action="" method="GET" class="relative w-full sm:w-64">
            <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari nama anak..." class="w-full pl-10 pr-4 py-3 border-2 border-[#D0F246]/30 bg-[#1D3428] text-[#D0F246] rounded-[10px] focus:ring-2 focus:ring-[#D0F246] focus:border-[#D0F246] text-sm transition">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-[#D0F246]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
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

            <article onclick="showDetailModal('<?= $anak['id']; ?>')" class="cursor-pointer bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[16px] shadow-lg border-2 border-[#D0F246] overflow-hidden hover:shadow-2xl hover:border-[#bce038] transition-all group relative transform hover:-translate-y-2">

                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold <?= $gender === 'L' ? 'bg-blue-900/30 text-blue-300 border border-blue-700' : 'bg-pink-900/30 text-pink-300 border border-pink-700'; ?>">
                        <?= $genderLabel; ?>
                    </span>
                </div>

                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="h-14 w-14 rounded-full flex items-center justify-center text-lg font-bold text-[#1D3428] <?= $gender === 'L' ? 'bg-blue-400' : 'bg-pink-400'; ?>">
                            <?= htmlspecialchars($initial); ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#D0F246] group-hover:text-white transition"><?= htmlspecialchars($anak['nama_balita']); ?></h3>
                            <p class="text-sm text-gray-400"><?= $umur->y; ?> thn <?= $umur->m; ?> bln</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-300 border-t border-[#D0F246]/30 pt-4">
                        <span>Lahir: <?= date('d M Y', strtotime($anak['tgl_lahir'])); ?></span>
                        <span class="text-[#D0F246] font-bold flex items-center gap-2 group-hover:gap-3 transition-all">
                            <span>Lihat Detail</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-gradient-to-br from-[#2d4a38] to-[#1D3428] rounded-[16px] border-2 border-dashed border-[#D0F246]/30">
            <p class="text-gray-400 text-lg">Data tidak ditemukan.</p>
        </div>
    <?php endif; ?>

</main>

<div id="modalDetail" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 bg-[#1D3428] bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="closeDetailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-gradient-to-b from-[#1D3428] to-[#0f1f18] rounded-[20px] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border-2 border-[#D0F246]">
            
            <div class="bg-gradient-to-r from-[#D0F246] to-[#bce038] px-6 py-5 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-[#1D3428]">Detail Balita</h3>
                <button onclick="closeDetailModal()" class="text-[#1D3428] hover:opacity-70 focus:outline-none font-bold text-xl">
                    ✕
                </button>
            </div>

            <div id="modalDetailBody" class="px-6 py-6 bg-[#1D3428] min-h-[300px]">
                <div class="flex flex-col items-center justify-center h-full py-10">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#D0F246]"></div>
                    <p class="mt-3 text-sm text-gray-400">Memuat data...</p>
                </div>
            </div>

            <div class="bg-[#154620] px-6 py-4 sm:flex sm:flex-row-reverse border-t border-[#D0F246]/20">
                <button type="button" onclick="closeDetailModal()" class="w-full inline-flex justify-center rounded-[10px] border-2 border-[#D0F246]/30 shadow-sm px-4 py-3 bg-transparent text-[#D0F246] text-base font-bold hover:bg-[#D0F246]/10 focus:outline-none transition-all sm:ml-3 sm:w-auto sm:text-sm">
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
                    borderColor: '#D0F246',
                    backgroundColor: 'rgba(208, 242, 70, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#1D3428',
                    pointBorderColor: '#D0F246',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: false,
                        ticks: { color: '#D0F246' },
                        grid: { borderDash: [2, 4], color: 'rgba(208, 242, 70, 0.1)' },
                        border: { color: 'rgba(208, 242, 70, 0.2)' }
                    },
                    x: { 
                        ticks: { color: '#D0F246' },
                        grid: { display: false },
                        border: { color: 'rgba(208, 242, 70, 0.2)' }
                    }
                }
            }
        });
    }
</script>

<?php require '../layout/footer.php'; ?>