<?php
// Pastikan koneksi database ($conn) sudah tersedia dari file induk (dashboard.php)

// --- 1. QUERY KARTU RINGKASAN ---
$totalBalita = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM balita"))['total'];
$totalIbu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ibu_hamil"))['total'];
$totalUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
$totalKunjungan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM janji_imunisasi WHERE status = 'Selesai'"))['total'];

// --- 2. QUERY GRAFIK JENIS VAKSINASI (Top 5) ---
$qVaksin = mysqli_query($conn, "
    SELECT m.nama_imunisasi, COUNT(r.id) as jumlah 
    FROM riwayat_imunisasi r
    JOIN master_imunisasi m ON r.imunisasi_id = m.id
    GROUP BY m.nama_imunisasi
    ORDER BY jumlah DESC LIMIT 5
");

$labelVaksin = [];
$dataVaksin = [];
while($v = mysqli_fetch_assoc($qVaksin)) {
    $labelVaksin[] = $v['nama_imunisasi'];
    $dataVaksin[] = $v['jumlah'];
}

// --- 3. QUERY GRAFIK TREN KUNJUNGAN (PER BULAN TAHUN INI) ---
$tahunIni = date('Y');
$qTren = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_rencana, '%M') as bulan, COUNT(*) as jumlah 
    FROM janji_imunisasi 
    WHERE status = 'Selesai' AND YEAR(tanggal_rencana) = '$tahunIni'
    GROUP BY MONTH(tanggal_rencana)
    ORDER BY MONTH(tanggal_rencana) ASC
");

$labelBulan = [];
$dataTren = [];
while($t = mysqli_fetch_assoc($qTren)) {
    $labelBulan[] = $t['bulan'];
    $dataTren[] = $t['jumlah'];
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-2xl shadow-lg border border-[#D0F246]/30 flex justify-between items-start transition hover:-translate-y-1 hover:shadow-xl">
        <div>
            <p class="text-sm font-medium text-gray-400">Total Balita</p>
            <h3 class="text-3xl font-bold text-[#D0F246] mt-2"><?= $totalBalita; ?></h3>
            <p class="text-xs text-[#D0F246] mt-1 flex items-center">
                <span class="bg-[#D0F246]/20 px-1.5 py-0.5 rounded mr-1">Aktif</span> Terdaftar
            </p>
        </div>
        <div class="p-3 bg-[#D0F246]/10 text-[#D0F246] rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-2xl shadow-lg border border-[#D0F246]/30 flex justify-between items-start transition hover:-translate-y-1 hover:shadow-xl">
        <div>
            <p class="text-sm font-medium text-gray-400">Ibu Hamil</p>
            <h3 class="text-3xl font-bold text-[#D0F246] mt-2"><?= $totalIbu; ?></h3>
            <p class="text-xs text-gray-400 mt-1">Data KIA</p>
        </div>
        <div class="p-3 bg-[#D0F246]/10 text-[#D0F246] rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
    </div>

    <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-2xl shadow-lg border border-[#D0F246]/30 flex justify-between items-start transition hover:-translate-y-1 hover:shadow-xl">
        <div>
            <p class="text-sm font-medium text-gray-400">Kunjungan Selesai</p>
            <h3 class="text-3xl font-bold text-[#D0F246] mt-2"><?= $totalKunjungan; ?></h3>
            <p class="text-xs text-gray-400 mt-1">Total layanan sukses</p>
        </div>
        <div class="p-3 bg-[#D0F246]/10 text-[#D0F246] rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <div class="bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-2xl shadow-lg border-2 border-[#D0F246] flex justify-between items-start transition hover:-translate-y-1 hover:shadow-xl">
        <div>
            <p class="text-sm font-medium text-gray-400">Pengguna Aplikasi</p>
            <h3 class="text-3xl font-bold text-[#D0F246] mt-2"><?= $totalUser; ?></h3>
            <p class="text-xs text-gray-400 mt-1">Orang tua terdaftar</p>
        </div>
        <div class="p-3 bg-[#D0F246]/10 text-[#D0F246] rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
    
    <div class="lg:col-span-1 bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-2xl shadow-lg border-2 border-[#D0F246]">
        <h4 class="text-lg font-bold text-[#D0F246] mb-4">Vaksinasi Terbanyak</h4>
        <div class="relative h-64">
            <canvas id="chartVaksin"></canvas>
        </div>
    </div>

    <div class="lg:col-span-2 bg-gradient-to-br from-[#2d4a38] to-[#1D3428] p-6 rounded-2xl shadow-lg border-2 border-[#D0F246]">
        <h4 class="text-lg font-bold text-[#D0F246] mb-4">Tren Kunjungan Bulanan (<?= $tahunIni; ?>)</h4>
        <div class="relative h-64">
            <canvas id="chartTren"></canvas>
        </div>
    </div>
</div>

<script>
    // --- KONFIGURASI GRAFIK VAKSIN (BAR CHART) ---
    const ctxVaksin = document.getElementById('chartVaksin').getContext('2d');
    new Chart(ctxVaksin, {
        type: 'doughnut', // Bisa diganti 'bar' atau 'pie'
        data: {
            labels: <?= json_encode($labelVaksin); ?>,
            datasets: [{
                data: <?= json_encode($dataVaksin); ?>,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // --- KONFIGURASI GRAFIK TREN (LINE CHART) ---
    const ctxTren = document.getElementById('chartTren').getContext('2d');
    new Chart(ctxTren, {
        type: 'line',
        data: {
            labels: <?= json_encode($labelBulan); ?>,
            datasets: [{
                label: 'Jumlah Kunjungan Selesai',
                data: <?= json_encode($dataTren); ?>,
                borderColor: '#D0F246',
                backgroundColor: 'rgba(208, 242, 70, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1D3428',
                pointBorderColor: '#D0F246',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                filler: { propagate: true }
            },
            scales: {
                y: { 
                    beginAtZero: true,
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
</script>