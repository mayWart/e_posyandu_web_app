<?php
require '../config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$id_balita = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Ambil Data Balita (Pastikan milik user yang login agar aman)
$query = "SELECT * FROM balita WHERE id = '$id_balita' AND user_id = '$user_id'";
$balita = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$balita) { die("Data tidak ditemukan atau Anda tidak memiliki akses."); }

// 2. Ambil Riwayat Pengukuran (Untuk Grafik)
$ukur_query = mysqli_query($conn, "SELECT * FROM pengukuran WHERE balita_id = '$id_balita' ORDER BY tgl_ukur ASC");
$labels = [];
$data_bb = [];
while ($row = mysqli_fetch_assoc($ukur_query)) {
    $labels[] = date('d M Y', strtotime($row['tgl_ukur'])); // Sumbu X (Tanggal)
    $data_bb[] = $row['berat_badan']; // Sumbu Y (Berat)
}

// 3. Logika Jadwal Imunisasi Otomatis
// Ambil semua master imunisasi
$master_imunisasi = mysqli_query($conn, "SELECT * FROM master_imunisasi ORDER BY usia_wajib_bulan ASC");

// Ambil imunisasi yang SUDAH dilakukan anak ini
$sudah_imunisasi = [];
$cek_imunisasi = mysqli_query($conn, "SELECT imunisasi_id, tgl_suntik FROM riwayat_imunisasi WHERE balita_id = '$id_balita'");
while($row = mysqli_fetch_assoc($cek_imunisasi)) {
    $sudah_imunisasi[$row['imunisasi_id']] = $row['tgl_suntik'];
}

require '../layout/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
        <div class="bg-blue-600 h-24"></div>
        <div class="px-6 pb-6 relative">
            <div class="-mt-12 mb-4">
                <div class="bg-white p-2 rounded-full inline-block shadow-lg">
                    <div class="h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center text-4xl">
                        <?= $balita['jenis_kelamin'] == 'L' ? '👦' : '👧'; ?>
                    </div>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900"><?= $balita['nama_balita']; ?></h1>
            <p class="text-gray-500">
                NIK: <?= $balita['nik']; ?> | Lahir: <?= date('d F Y', strtotime($balita['tgl_lahir'])); ?> 
                (Usia: <?= date_diff(date_create($balita['tgl_lahir']), date_create('today'))->y; ?> Tahun)
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">📈 Grafik Pertumbuhan</h3>
                    <span class="text-xs font-semibold bg-blue-100 text-blue-800 px-2 py-1 rounded">Berat Badan (Kg)</span>
                </div>
                <?php if(count($data_bb) > 0): ?>
                    <canvas id="chartBalita" height="150"></canvas>
                <?php else: ?>
                    <div class="text-center py-10 text-gray-400 bg-gray-50 rounded-lg border-2 border-dashed">
                        Belum ada data pengukuran. Hubungi kader posyandu.
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm overflow-hidden">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📋 Riwayat Detail</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">BB (Kg)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TB (Cm)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ket</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            // Reset pointer query agar bisa diloop ulang jika perlu, atau query ulang limit 5
                            $ukur_list = mysqli_query($conn, "SELECT * FROM pengukuran WHERE balita_id = '$id_balita' ORDER BY tgl_ukur DESC");
                            while($u = mysqli_fetch_assoc($ukur_list)): 
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('d/m/Y', strtotime($u['tgl_ukur'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600"><?= $u['berat_badan']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $u['tinggi_badan']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= $u['keterangan']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-sm sticky top-24">
                <h3 class="text-lg font-bold text-gray-800 mb-4">💉 Jadwal Imunisasi</h3>
                <div class="space-y-4">
                    <?php while($imun = mysqli_fetch_assoc($master_imunisasi)): 
                        // Hitung Tanggal Estimasi (Tgl Lahir + Usia Wajib Bulan)
                        $tgl_estimasi = date('Y-m-d', strtotime("+$imun[usia_wajib_bulan] month", strtotime($balita['tgl_lahir'])));
                        $sudah = isset($sudah_imunisasi[$imun['id']]);
                        $tgl_suntik = $sudah ? $sudah_imunisasi[$imun['id']] : null;
                        
                        // Tentukan Status Warna
                        if ($sudah) {
                            $bg_class = "bg-green-50 border-green-200";
                            $icon = "✅";
                            $status_text = "Sudah: " . date('d M Y', strtotime($tgl_suntik));
                            $text_color = "text-green-700";
                        } elseif (date('Y-m-d') > $tgl_estimasi) {
                            $bg_class = "bg-red-50 border-red-200";
                            $icon = "⚠️";
                            $status_text = "Terlewat! (Jadwal: " . date('d M Y', strtotime($tgl_estimasi)) . ")";
                            $text_color = "text-red-700";
                        } else {
                            $bg_class = "bg-blue-50 border-blue-200";
                            $icon = "📅";
                            $status_text = "Jadwal: " . date('d M Y', strtotime($tgl_estimasi));
                            $text_color = "text-blue-700";
                        }
                    ?>
                    
                    <div class="border rounded-lg p-3 <?= $bg_class; ?> transition hover:shadow-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm"><?= $imun['nama_imunisasi']; ?></h4>
                                <p class="text-xs <?= $text_color; ?> mt-1"><?= $status_text; ?></p>
                            </div>
                            <span class="text-lg"><?= $icon; ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="mt-6 p-4 bg-yellow-50 rounded-lg text-xs text-yellow-800 border border-yellow-200">
                    <span class="font-bold">Catatan:</span> Jadwal di atas adalah estimasi otomatis berdasarkan tanggal lahir. Konsultasikan dengan bidan untuk jadwal pasti.
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const ctx = document.getElementById('chartBalita').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels); ?>, // Data Tanggal dari PHP
            datasets: [{
                label: 'Berat Badan (Kg)',
                data: <?= json_encode($data_bb); ?>, // Data Berat dari PHP
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgb(255, 255, 255)',
                pointBorderColor: 'rgb(37, 99, 235)',
                pointRadius: 5,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: { display: true, text: 'Berat (Kg)' }
                }
            }
        }
    });
</script>

<?php require '../layout/footer.php'; ?>