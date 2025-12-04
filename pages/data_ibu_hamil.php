<?php

include '../config.php';

// PERLINDUNGAN AKSES
$user_role = strtolower($_SESSION['role'] ?? '');

if ($user_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

include '../layout/header.php';

// --- LOGIKA PENGAMBILAN RINGKASAN DATA (MySQLi) ---
$total_ibu_hamil = 0;
$tanggal_data_terakhir = "N/A";

if (isset($conn) && !$conn->connect_error) {
    // 1. Hitung Total Ibu Hamil
    $res_total = $conn->query("SELECT COUNT(*) as total FROM ibu_hamil");
    if ($res_total && $row = $res_total->fetch_assoc()) {
        $total_ibu_hamil = $row['total'];
    }

    // 2. Ambil Tanggal HPHT Terbaru
    $res_terakhir = $conn->query("SELECT tanggal_hpht FROM ibu_hamil ORDER BY tanggal_hpht DESC LIMIT 1");
    if ($res_terakhir && $row = $res_terakhir->fetch_assoc()) {
        // Menggunakan format yang sama dengan Penimbangan (d M Y)
        $tanggal_data_terakhir = date("d M Y", strtotime($row['tanggal_hpht']));
    }
}
// ----------------------------------------------------
?>

<style>
    /* Tambahkan style dari referensi Penimbangan */
    .fade-up {
        animation: fadeUp .36s cubic-bezier(.2, .9, .2, 1) both;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-shadow {
        box-shadow: 0 6px 18px rgba(13, 38, 76, 0.06);
    }

    .btn-primary {
        background: linear-gradient(90deg, #2563eb, #1e40af);
    }
</style>

<main class="sm:ml-64 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-up">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="w-full sm:w-2/3">
            <nav class="flex text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                <a href="dashboard.php" class="hover:text-blue-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Data Ibu Hamil</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Data Ibu Hamil</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data kehamilan, HPHT, dan perkiraan lahir ibu.</p>

            <div class="mt-4 bg-white border rounded-2xl p-4 flex items-center gap-4 card-shadow">
                <div class="flex-shrink-0">
                    <div
                        class="h-14 w-14 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.5A5.5 5.5 0 0117.5 10c0 3.32-3.5 7-5.5 7s-5.5-3.68-5.5-7A5.5 5.5 0 0112 4.5zm0 0v17.5m5.5-11.5a5.5 5.5 0 00-11 0" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="text-sm text-gray-500">Ringkasan</div>
                    <div class="flex items-baseline gap-6 mt-1">
                        <div>
                            <div class="text-2xl font-bold text-gray-900"><?= $total_ibu_hamil; ?></div>
                            <div class="text-xs text-gray-500">Total data ibu hamil</div>
                        </div>
                        <div class="border-l pl-4">
                            <div class="text-sm text-gray-700">Terakhir: <span
                                    class="font-medium"><?= $tanggal_data_terakhir; ?></span></div>
                            <div class="text-xs text-gray-500">Data HPHT terbaru</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-shrink-0">
            <button onclick="window.location='form_ibu_hamil.php'"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Input Data Baru
            </button>
        </div>
    </div>


    <section class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No.</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Ibu</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Usia (Mg)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">HPHT</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Perkiraan Lahir</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Dicatat Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-ibu-hamil-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php
require '../layout/footer.php';
?>

<script>
    // (Sertakan kembali seluruh kode JavaScript/AJAX yang ada di file Anda)
    $(document).ready(function () {

        function loadIbuHamilData() {
            $('#data-ibu-hamil-body').html('<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Memuat data...</td></tr>');

            $.ajax({
                url: '../services/ibu_hamil_service.php',
                type: 'GET',
                data: { action: 'list' },
                dataType: 'json',
                success: function (response) {
                    let tableBody = $('#data-ibu-hamil-body');
                    tableBody.empty();

                    if (response.status === 'success' && response.data.length > 0) {
                        $.each(response.data, function (index, ibu) {

                            let row = `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                                <th scope="row" class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                                    ${ibu.nama_ibu}
                                </th>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ibu.usia_kehamilan}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ibu.tanggal_hpht}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ibu.tanggal_perkiraan_lahir}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ibu.nama_lengkap}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                                    <a href="detail_ibu_hamil.php?id=${ibu.id_ibu_hamil}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-blue-600 border border-gray-100">
                                        Detail
                                    </a>
                                    <a href="form_ibu_hamil.php?id=${ibu.id_ibu_hamil}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-yellow-600 border border-gray-100">
                                        Edit
                                    </a>
                                    <button data-id="${ibu.id_ibu_hamil}" class="btn-delete inline-flex items-center px-3 py-1.5 rounded-lg bg-white hover:bg-red-50 text-red-600 border border-red-100">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        `;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.html('<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada data ibu hamil.</td></tr>');
                    }
                },
                error: function (xhr) {
                    let message = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat berkomunikasi dengan server.';
                    $('#data-ibu-hamil-body').html(`
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-red-600">Gagal memuat data: ${message}</td>
                    </tr>
                `);
                }
            });
        }

        $(document).on('click', '.btn-delete', function () {
            let idHamil = $(this).data('id');
            let namaIbu = $(this).closest('tr').find('th').text();

            if (confirm(`Yakin ingin menghapus data Ibu Hamil: ${namaIbu}? Data pemeriksaan terkait juga akan dihapus.`)) {
                $.ajax({
                    url: '../services/ibu_hamil_service.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id_ibu_hamil: idHamil
                    },
                    dataType: 'json',
                    success: function (response) {
                        alert(response.message);
                        if (response.status === 'success') {
                            loadIbuHamilData();
                        }
                    },
                    error: function (xhr) {
                        let message = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
                        alert('Gagal menghapus: ' + message);
                    }
                });
            }
        });

        loadIbuHamilData();

    });
</script>