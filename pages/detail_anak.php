<?php
require '../config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$id_balita = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Ambil Data Balita
$query = "SELECT * FROM balita WHERE id = '$id_balita' AND user_id = '$user_id'";
$balita = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$balita) { die("<div class='min-h-screen flex items-center justify-center bg-gray-50 text-[#1D3428] font-bold'>Data tidak ditemukan atau akses ditolak.</div>"); }

// 2. Ambil Riwayat Pengukuran
$ukur_query = mysqli_query($conn, "SELECT * FROM pengukuran WHERE balita_id = '$id_balita' ORDER BY tgl_ukur ASC");
$labels = [];
$data_bb = [];
while ($row = mysqli_fetch_assoc($ukur_query)) {
    $labels[] = date('d M', strtotime($row['tgl_ukur'])); 
    $data_bb[] = $row['berat_badan']; 
}

// 3. Logika Jadwal Imunisasi
$master_imunisasi = mysqli_query($conn, "SELECT * FROM master_imunisasi ORDER BY usia_wajib_bulan ASC");
$sudah_imunisasi = [];
$cek_imunisasi = mysqli_query($conn, "SELECT imunisasi_id, tgl_suntik FROM riwayat_imunisasi WHERE balita_id = '$id_balita'");
while($row = mysqli_fetch_assoc($cek_imunisasi)) {
    $sudah_imunisasi[$row['imunisasi_id']] = $row['tgl_suntik'];
}

require '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .text-forest { color: #1D3428; }
    .bg-forest { background-color: #1D3428; }
    .text-neon { color: #D0F246; }
    .bg-neon { background-color: #D0F246; }
    .border-neon { border-color: #D0F246; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="bg-white rounded-[20px] shadow-lg border border-gray-100 overflow-hidden mb-8 group">
        <div class="h-32 bg-[#1D3428] relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="absolute bottom-0 w-full h-1 bg-[#D0F246]"></div>
        </div>

        <div class="px-8 pb-8 relative flex flex-col md:flex-row items-start md:items-end gap-6 -mt-12">
            
            <div class="bg-white p-1.5 rounded-full shadow-lg z-10">
                <div class="h-24 w-24 bg-[#1D3428] rounded-full flex items-center justify-center text-4xl border-4 border-white text-[#D0F246]">
                    <i class="<?= $balita['jenis_kelamin'] == 'L' ? 'fa-solid fa-child' : 'fa-solid fa-child-dress'; ?>"></i>
                </div>
            </div>

            <div class="flex-1 pb-6">
                <h1 class="text-3xl font-bold text-[#D0F246] mb-2"><?= htmlspecialchars($balita['nama_balita']); ?></h1>
                
                <div class="flex flex-wrap gap-4 pt-2 text-sm">
                    <span class="flex items-center gap-2 bg-[#1D3428]/5 px-3 py-1.5 rounded-lg border border-[#1D3428]/10 text-[#1D3428] font-medium">
                        <i class="fa-regular fa-id-card text-[#1D3428]"></i> <?= $balita['nik'] ? $balita['nik'] : '-'; ?>
                    </span>
                    <span class="flex items-center gap-2 bg-[#1D3428]/5 px-3 py-1.5 rounded-lg border border-[#1D3428]/10 text-[#1D3428] font-medium">
                        <i class="fa-regular fa-calendar text-[#1D3428]"></i> <?= date('d M Y', strtotime($balita['tgl_lahir'])); ?>
                    </span>
                    <span class="flex items-center gap-2 bg-[#D0F246]/20 px-3 py-1.5 rounded-lg border border-[#D0F246]/50 text-[#1D3428] font-bold">
                        <i class="fa-solid fa-cake-candles text-[#1D3428]"></i> 
                        <?= date_diff(date_create($balita['tgl_lahir']), date_create('today'))->y; ?> Thn
                        <?= date_diff(date_create($balita['tgl_lahir']), date_create('today'))->m; ?> Bln
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white p-6 rounded-[20px] shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-[#1D3428] flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-[#1D3428] flex items-center justify-center text-[#D0F246]">
                                <i class="fa-solid fa-chart-line text-sm"></i>
                            </span>
                            Grafik Pertumbuhan
                        </h3>
                    </div>
                    <span class="text-xs font-semibold bg-[#1D3428] text-[#D0F246] px-3 py-1 rounded-full">Berat Badan (Kg)</span>
                </div>
                
                <div class="relative h-64 w-full">
                    <?php if(count($data_bb) > 0): ?>
                        <canvas id="chartBalita"></canvas>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <i class="fa-solid fa-chart-area text-3xl mb-2 opacity-30"></i>
                            <span class="text-sm">Belum ada data pengukuran.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 bg-[#1D3428] flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-table-list text-[#D0F246]"></i> Riwayat Penimbangan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#1D3428] uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#1D3428] uppercase tracking-wider">Berat (Kg)</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#1D3428] uppercase tracking-wider">Tinggi (Cm)</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#1D3428] uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <?php 
                            mysqli_data_seek($ukur_query, 0);
                            $query_result = mysqli_fetch_all($ukur_query, MYSQLI_ASSOC);
                            $reversed_ukur = array_reverse($query_result);
                            
                            if(count($reversed_ukur) > 0):
                                foreach($reversed_ukur as $u): 
                            ?>
                            <tr class="hover:bg-[#D0F246]/10 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                    <?= date('d/m/Y', strtotime($u['tgl_ukur'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#1D3428]">
                                    <?= $u['berat_badan']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= $u['tinggi_badan']; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 italic">
                                    <?= $u['keterangan'] ? $u['keterangan'] : '-'; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Data kosong.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-[20px] shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-lg font-bold text-[#1D3428] mb-6 flex items-center gap-2 pb-4 border-b border-gray-100">
                    <span class="w-8 h-8 rounded-lg bg-[#D0F246] flex items-center justify-center text-[#1D3428]">
                        <i class="fa-solid fa-syringe text-sm"></i>
                    </span>
                    Jadwal Imunisasi
                </h3>
                
                <div class="relative pl-4 border-l-2 border-dashed border-gray-200 space-y-6">
                    <?php while($imun = mysqli_fetch_assoc($master_imunisasi)): 
                        $tgl_estimasi = date('Y-m-d', strtotime("+$imun[usia_wajib_bulan] month", strtotime($balita['tgl_lahir'])));
                        $sudah = isset($sudah_imunisasi[$imun['id']]);
                        $tgl_suntik = $sudah ? $sudah_imunisasi[$imun['id']] : null;
                        
                        // Style Config (Dark Green & Neon Theme)
                        if ($sudah) {
                            $dot_bg = "bg-[#1D3428]"; // Dark Green Dot
                            $card_border = "border-[#1D3428]";
                            $card_bg = "bg-[#1D3428]/5";
                            $text_status = "Selesai: " . date('d M Y', strtotime($tgl_suntik));
                            $text_color = "text-[#1D3428]";
                            $icon = "fa-check-circle";
                            $icon_color = "text-[#1D3428]";
                        } elseif (date('Y-m-d') > $tgl_estimasi) {
                            $dot_bg = "bg-red-500";
                            $card_border = "border-red-200";
                            $card_bg = "bg-red-50";
                            $text_status = "Terlewat (" . date('d M Y', strtotime($tgl_estimasi)) . ")";
                            $text_color = "text-red-600";
                            $icon = "fa-circle-exclamation";
                            $icon_color = "text-red-500";
                        } else {
                            $dot_bg = "bg-gray-300";
                            $card_border = "border-gray-200";
                            $card_bg = "bg-white";
                            $text_status = "Jadwal: " . date('d M Y', strtotime($tgl_estimasi));
                            $text_color = "text-gray-500";
                            $icon = "fa-clock";
                            $icon_color = "text-gray-400";
                        }
                    ?>
                    
                    <div class="relative group">
                        <div class="absolute -left-[21px] top-4 h-3 w-3 rounded-full <?= $dot_bg; ?> ring-4 ring-white"></div>
                        
                        <div class="p-3 rounded-xl border <?= $card_border; ?> <?= $card_bg; ?> transition hover:shadow-sm flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800"><?= $imun['nama_imunisasi']; ?></h4>
                                <p class="text-xs font-medium mt-1 <?= $text_color; ?>"><?= $text_status; ?></p>
                            </div>
                            <i class="fa-solid <?= $icon; ?> <?= $icon_color; ?> mt-1"></i>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const ctx = document.getElementById('chartBalita');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Berat Badan (Kg)',
                    data: <?= json_encode($data_bb); ?>,
                    // Warna Garis: Neon Green
                    borderColor: '#D0F246', 
                    // Warna Area Bawah: Dark Green Transparan
                    backgroundColor: 'rgba(29, 52, 40, 0.8)', 
                    borderWidth: 3,
                    pointBackgroundColor: '#1D3428', // Titik Dark Green
                    pointBorderColor: '#D0F246', // Border Titik Neon
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1D3428', // Tooltip Dark Green
                        titleColor: '#D0F246', // Judul Neon
                        bodyColor: '#ffffff',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { borderDash: [4, 4], color: '#E5E7EB' },
                        ticks: { font: { size: 11 }, color: '#4B5563' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#4B5563' }
                    }
                }
            }
        });
    }
</script>

<?php require '../layout/footer.php'; ?>