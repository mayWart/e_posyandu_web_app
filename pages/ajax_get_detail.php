<?php
require '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { exit; }

$id_balita = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Validasi Keamanan: Pastikan data milik user tersebut (kecuali admin)
if ($role == 'admin') {
    $query = "SELECT * FROM balita WHERE id = '$id_balita'";
} else {
    $query = "SELECT * FROM balita WHERE id = '$id_balita' AND user_id = '$user_id'";
}

$balita = mysqli_fetch_assoc(mysqli_query($conn, $query));
if (!$balita) { echo "<div class='p-4 text-center text-red-500'>Data tidak ditemukan.</div>"; exit; }

// 2. Ambil Data Grafik
$ukur_query = mysqli_query($conn, "SELECT * FROM pengukuran WHERE balita_id = '$id_balita' ORDER BY tgl_ukur ASC");
$labels = [];
$data_bb = [];
while ($row = mysqli_fetch_assoc($ukur_query)) {
    $labels[] = date('d M', strtotime($row['tgl_ukur']));
    $data_bb[] = $row['berat_badan'];
}

// 3. Data Riwayat Imunisasi
$history_query = mysqli_query($conn, "
    SELECT r.*, m.nama_imunisasi 
    FROM riwayat_imunisasi r 
    JOIN master_imunisasi m ON r.imunisasi_id = m.id 
    WHERE r.balita_id = '$id_balita' 
    ORDER BY r.tgl_suntik DESC
");
?>

<style>
    .fade-up { animation: fadeUp .36s ease both; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="space-y-6 fade-up">

    <div class="flex items-center gap-4 bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-[16px] border-2 border-[#D0F246] shadow-lg">
        <?php $initial = htmlspecialchars(strtoupper(substr(trim($balita['nama_balita']),0,1))); ?>
        <div class="h-16 w-16 rounded-full flex items-center justify-center text-xl font-semibold text-white <?= strtoupper(trim($balita['jenis_kelamin'])) === 'L' ? 'bg-blue-500' : 'bg-pink-500'; ?> transition-transform transform group-hover:scale-105">
            <?= $initial; ?>
        </div>
        <div>
            <h3 class="text-xl font-bold text-[#D0F246]"><?= htmlspecialchars($balita['nama_balita']); ?></h3>
            <div class="text-sm text-gray-300 flex gap-3 mt-1 items-center flex-wrap">
                <span class="bg-[#154620] px-3 py-1 rounded-full border border-[#D0F246]/30 text-gray-300">NIK: <?= htmlspecialchars($balita['nik']); ?></span>
                <span class="bg-[#154620] px-3 py-1 rounded-full border border-[#D0F246]/30 text-gray-300">Lahir: <?= date('d/m/Y', strtotime($balita['tgl_lahir'])); ?></span>
                <span class="bg-[#154620] px-3 py-1 rounded-full border border-[#D0F246]/30 font-medium text-gray-300"><?= strtoupper(trim($balita['jenis_kelamin'])) === 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-[16px] border-2 border-[#D0F246] shadow-lg">
        <h4 class="font-bold text-[#D0F246] mb-4 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-[#D0F246]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            Grafik Berat Badan
        </h4>
        <div class="relative h-48 w-full">
            <?php if(count($data_bb) > 0): ?>
                <canvas id="chartModalCanvas"></canvas>
            <?php else: ?>
                <div class="flex h-full items-center justify-center text-gray-400 text-sm italic bg-[#154620] rounded-lg">Belum ada data pengukuran.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] border-2 border-[#D0F246] rounded-[16px] overflow-hidden shadow-lg">
            <div class="bg-[#1D3428] px-6 py-3 border-b-2 border-[#D0F246] text-xs font-bold text-[#D0F246] uppercase tracking-wider">📏 Pengukuran Terakhir</div>
            <div class="overflow-y-auto max-h-40">
                <table class="min-w-full text-sm divide-y divide-[#154620]">
                    <?php 
                    // Reset pointer query pengukuran
                    mysqli_data_seek($ukur_query, 0);
                    // Balik urutan jadi DESC untuk tabel (yang terbaru diatas)
                    $data_ukur_array = [];
                    while($row = mysqli_fetch_assoc($ukur_query)) { $data_ukur_array[] = $row; }
                    $data_ukur_array = array_reverse($data_ukur_array);

                    if(count($data_ukur_array) > 0):
                        foreach($data_ukur_array as $u): 
                    ?>
                        <tr class="hover:bg-[#154620] transition">
                            <td class="px-6 py-3 text-gray-300 font-medium"><?= date('d/m/y', strtotime($u['tgl_ukur'])); ?></td>
                            <td class="px-6 py-3 font-bold text-[#D0F246]"><?= $u['berat_badan']; ?> Kg</td>
                            <td class="px-6 py-3 text-gray-400"><?= $u['tinggi_badan']; ?> Cm</td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="3" class="px-6 py-6 text-center text-gray-400">Kosong</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] border-2 border-[#D0F246] rounded-[16px] overflow-hidden shadow-lg">
            <div class="bg-[#1D3428] px-6 py-3 border-b-2 border-[#D0F246] text-xs font-bold text-[#D0F246] uppercase tracking-wider">💉 Riwayat Imunisasi</div>
            <div class="overflow-y-auto max-h-40">
                <table class="min-w-full text-sm divide-y divide-[#154620]">
                    <?php if(mysqli_num_rows($history_query) > 0): while($h = mysqli_fetch_assoc($history_query)): ?>
                        <tr class="hover:bg-[#154620] transition">
                            <td class="px-6 py-3 text-gray-300 font-medium"><?= htmlspecialchars($h['nama_imunisasi']); ?></td>
                            <td class="px-6 py-3 text-right">
                                <span class="text-xs bg-emerald-900/30 text-emerald-300 px-3 py-1 rounded-full border border-emerald-700"><?= date('d M Y', strtotime($h['tgl_suntik'])); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="2" class="px-6 py-6 text-center text-gray-400">Belum ada imunisasi.</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <input type="hidden" id="ajax-chart-labels" value='<?= json_encode($labels); ?>'>
    <input type="hidden" id="ajax-chart-data" value='<?= json_encode($data_bb); ?>'>
</div>