<?php
require '../config.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// --- BAGIAN 1: QUERY DATA STATISTIK ---

// A. Total Data
$totalBalita = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM balita"))['total'];
$totalIbu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ibu_hamil"))['total'];
$totalKunjungan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM janji_imunisasi WHERE status = 'Selesai'"))['total'];

// B. Data Grafik 1: Kunjungan per Bulan (Tahun Ini)
$tahunIni = date('Y');
$qGrafik = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_rencana, '%M') as bulan, COUNT(*) as jumlah 
    FROM janji_imunisasi 
    WHERE status = 'Selesai' AND YEAR(tanggal_rencana) = '$tahunIni'
    GROUP BY MONTH(tanggal_rencana)
");

$labelBulan = [];
$dataKunjungan = [];
while($g = mysqli_fetch_assoc($qGrafik)) {
    $labelBulan[] = $g['bulan'];
    $dataKunjungan[] = $g['jumlah'];
}

// C. Data Grafik 2: Komposisi Gender Balita
$qGender = mysqli_query($conn, "SELECT jenis_kelamin, COUNT(*) as total FROM balita GROUP BY jenis_kelamin");
$dataL = 0;
$dataP = 0;
while($gen = mysqli_fetch_assoc($qGender)) {
    if($gen['jenis_kelamin'] == 'L') $dataL = $gen['total'];
    if($gen['jenis_kelamin'] == 'P') $dataP = $gen['total'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Statistik Posyandu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS KHUSUS CETAK */
        body { font-family: Arial, sans-serif; -webkit-print-color-adjust: exact; margin: 0; padding: 20px; background: #f0f0f0; }
        
        .page {
            width: 210mm; min-height: 297mm; /* A4 */
            background: white; margin: 0 auto; padding: 15mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #2563eb; text-transform: uppercase; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }

        /* Kartu Statistik */
        .stats-container { display: flex; justify-content: space-between; margin-bottom: 40px; gap: 20px; }
        .card { 
            flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; 
            background: #f9fafb; 
        }
        .card h3 { margin: 0; font-size: 32px; color: #333; }
        .card p { margin: 5px 0 0; color: #666; font-size: 14px; text-transform: uppercase; font-weight: bold; }

        /* Container Grafik */
        .charts-wrapper { display: flex; gap: 30px; margin-bottom: 40px; }
        .chart-box { flex: 1; border: 1px solid #eee; padding: 15px; border-radius: 8px; }
        .chart-box h4 { text-align: center; margin-top: 0; color: #444; }

        /* Tabel Ringkas */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; font-size: 12px; }
        th { background-color: #2563eb; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }

        /* Footer Tanda Tangan */
        .footer { margin-top: 50px; display: flex; justify-content: flex-end; }
        .ttd { text-align: center; width: 200px; }

        /* Tombol Print */
        .btn-print {
            position: fixed; top: 20px; right: 20px;
            background: #2563eb; color: white; border: none; padding: 12px 24px;
            border-radius: 50px; cursor: pointer; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
            font-weight: bold; transition: all 0.3s;
        }
        .btn-print:hover { background: #1d4ed8; transform: translateY(-2px); }

        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; margin: 0; width: 100%; }
            .btn-print { display: none; }
            /* Paksa grafik tercetak */
            canvas { min-height: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print"><i class="fas fa-print mr-2"></i> Cetak PDF</button>

    <div class="page">
        <div class="header">
            <h1>Posyandu Desa Sebaneh</h1>
            <p>Laporan Statistik & Kinerja Posyandu</p>
            <p style="font-size: 12px;">Periode Tahun: <?= $tahunIni; ?></p>
        </div>

        <div class="stats-container">
            <div class="card">
                <h3><?= $totalBalita; ?></h3>
                <p>Total Balita</p>
            </div>
            <div class="card">
                <h3><?= $totalIbu; ?></h3>
                <p>Ibu Hamil</p>
            </div>
            <div class="card">
                <h3><?= $totalKunjungan; ?></h3>
                <p>Kunjungan Selesai</p>
            </div>
        </div>

        <div class="charts-wrapper">
            <div class="chart-box" style="flex: 2;">
                <h4>Tren Kunjungan Imunisasi (<?= $tahunIni; ?>)</h4>
                <canvas id="chartKunjungan"></canvas>
            </div>
            <div class="chart-box" style="flex: 1;">
                <h4>Komposisi Balita</h4>
                <canvas id="chartGender"></canvas>
            </div>
        </div>

        <h4 style="margin-bottom: 10px; color: #333;">5 Riwayat Imunisasi Terakhir</h4>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Anak</th>
                    <th>Jenis Vaksin</th>
                    <th>Bidan / Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $qList = mysqli_query($conn, "
                    SELECT r.tgl_suntik, b.nama_balita, m.nama_imunisasi, r.bidan_penyuntik 
                    FROM riwayat_imunisasi r
                    JOIN balita b ON r.balita_id = b.id
                    JOIN master_imunisasi m ON r.imunisasi_id = m.id
                    ORDER BY r.tgl_suntik DESC LIMIT 5
                ");
                if(mysqli_num_rows($qList) > 0) {
                    while($r = mysqli_fetch_assoc($qList)) {
                        echo "<tr>
                            <td>".date('d M Y', strtotime($r['tgl_suntik']))."</td>
                            <td>".$r['nama_balita']."</td>
                            <td>".$r['nama_imunisasi']."</td>
                            <td>".$r['bidan_penyuntik']."</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center'>Belum ada data.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="footer">
            <div class="ttd">
                <p>Mengetahui,</p>
                <p>Ketua Kader</p>
                <br><br><br>
                <p style="font-weight: bold; text-decoration: underline;"><?= $_SESSION['nama']; ?></p>
            </div>
        </div>
    </div>

    <script>
        // Konfigurasi Umum agar Grafik Bisa Dicetak (Matikan Animasi)
        Chart.defaults.animation = false; 
        Chart.defaults.responsive = true;

        // 1. Chart Kunjungan (Bar Chart)
        const ctx1 = document.getElementById('chartKunjungan').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelBulan); ?>, // Dari PHP
                datasets: [{
                    label: 'Jumlah Anak',
                    data: <?= json_encode($dataKunjungan); ?>, // Dari PHP
                    backgroundColor: '#3b82f6',
                    borderColor: '#1d4ed8',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // 2. Chart Gender (Pie Chart)
        const ctx2 = document.getElementById('chartGender').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [<?= $dataL; ?>, <?= $dataP; ?>], // Dari PHP
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    hoverOffset: 4
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>

</body>
</html>