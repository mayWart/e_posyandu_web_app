<?php

include '../config.php';

// PERLINDUNGAN AKSES
$user_role = strtolower($_SESSION['role'] ?? '');

if ($user_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

include '../layout/header.php';

// 4. LOGIKA HALAMAN
$mode = isset($_GET['id']) ? 'edit' : 'create';
$id_ibu_hamil = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$page_title = $mode === 'edit' ? 'Edit Data Ibu Hamil' : 'Input Data Ibu Hamil Baru';

?>
<style>
    /* Tambahkan style dasar jika belum ada di header.php */
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
</style>

<main class="sm:ml-64 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-up min-h-screen bg-gray-50">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="w-full">
            <nav class="flex text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                <a href="dashboard.php" class="hover:text-blue-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="data_ibu_hamil.php" class="hover:text-blue-600 transition">Data Ibu Hamil</a>
                <span class="mx-2">/</span>
                <span
                    class="text-gray-900 font-medium"><?php echo $mode === 'edit' ? 'Edit Data' : 'Input Baru'; ?></span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900"><?php echo $page_title; ?></h1>
        </div>
    </div>


    <div class="flex justify-center items-start">
        <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-gray-100">

            <h2 class="text-xl font-bold text-center text-blue-600 mb-6 border-b pb-3">
                <?php echo $page_title; ?>
            </h2>

            <form id="ibu-hamil-form" method="POST">

                <?php if ($mode === 'edit'): ?>
                    <input type="hidden" id="id_ibu_hamil" name="id_ibu_hamil" value="<?php echo $id_ibu_hamil; ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label for="nama_ibu" class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu:</label>
                    <input type="text" id="nama_ibu" name="nama_ibu"
                        class="border border-gray-300 p-2 w-full rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div class="mb-4">
                    <label for="tanggal_hpht" class="block text-sm font-medium text-gray-700 mb-1">Hari
                        Pertama Haid Terakhir (HPHT):</label>
                    <input type="date" id="tanggal_hpht" name="tanggal_hpht"
                        class="border border-gray-300 p-2 w-full rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="usia_kehamilan" class="block text-sm font-medium text-gray-700 mb-1">Usia Kehamilan
                            (Minggu):</label>
                        <input type="number" id="usia_kehamilan" name="usia_kehamilan"
                            class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50" min="0" max="42" required
                            readonly>
                        <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari HPHT.</p>
                    </div>
                    <div>
                        <label for="tanggal_perkiraan_lahir"
                            class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perkiraan Lahir (TPL):</label>
                        <input type="date" id="tanggal_perkiraan_lahir" name="tanggal_perkiraan_lahir"
                            class="border border-gray-300 p-2 w-full rounded-lg bg-gray-50" required readonly>
                        <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari HPHT.</p>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat:</label>
                    <textarea id="alamat" name="alamat" rows="3"
                        class="border border-gray-300 p-2 w-full rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Contoh: Jl. Mawar No. 12, RT 01 RW 02</p>
                </div>

                <div class="mb-6">
                    <label for="nomor_telepon" class="block text-sm font-medium text-gray-700 mb-1">Nomor
                        Telepon:</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon"
                        class="border border-gray-300 p-2 w-full rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t">
                    <a href="data_ibu_hamil.php"
                        class="py-2 px-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-150">Batal</a>
                    <button type="submit"
                        class="py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include '../layout/footer.php'; ?>

<script>
    $(document).ready(function () {
        const mode = '<?php echo $mode; ?>';
        const id_ibu_hamil = '<?php echo $id_ibu_hamil; ?>';

        // --- FUNGSI MUAT DATA UNTUK MODE EDIT ---
        if (mode === 'edit') {
            $.ajax({
                url: '../services/ibu_hamil_service.php',
                type: 'GET',
                data: { action: 'get_detail', id: id_ibu_hamil },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Isi form dengan data yang diterima
                        $('#nama_ibu').val(response.data.nama_ibu);
                        $('#usia_kehamilan').val(response.data.usia_kehamilan);
                        $('#tanggal_hpht').val(response.data.tanggal_hpht);
                        $('#tanggal_perkiraan_lahir').val(response.data.tanggal_perkiraan_lahir);
                        $('#alamat').val(response.data.alamat);
                        $('#nomor_telepon').val(response.data.nomor_telepon);
                    } else {
                        alert('Gagal memuat data ibu hamil: ' + response.message);
                        window.location.href = 'data_ibu_hamil.php';
                    }
                },
                error: function () {
                    alert('Terjadi kesalahan saat mengambil data.');
                    window.location.href = 'data_ibu_hamil.php';
                }
            });
        }

        // --- FUNGSI SUBMIT FORM ---
        $('#ibu-hamil-form').submit(function (e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let submitAction = mode === 'edit' ? 'update' : 'create';

            if ($('#usia_kehamilan').val() === '0') {
                calculateUsiaKehamilan();
            }

            $.ajax({
                url: '../services/ibu_hamil_service.php',
                type: 'POST',
                data: formData + '&action=' + submitAction,
                dataType: 'json',
                success: function (response) {
                    alert(response.message);
                    if (response.status === 'success') {
                        window.location.href = 'data_ibu_hamil.php';
                    }
                },
                error: function (xhr) {
                    let message = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan data.';
                    alert('Gagal: ' + message);
                }
            });
        });

        // --- FUNGSI BANTU: HITUNG TPL OTOMATIS BERDASARKAN HPHT --- 
        $('#tanggal_hpht').on('change', function () {
            let hphtVal = $(this).val();
            if (hphtVal) {
                let hphtDate = new Date(hphtVal);
                // Tambahkan 280 hari (sekitar 40 minggu) untuk TPL
                hphtDate.setDate(hphtDate.getDate() + 280);

                // Format tanggal menjadi YYYY-MM-DD
                let tpl = hphtDate.toISOString().split('T')[0];
                $('#tanggal_perkiraan_lahir').val(tpl);
            }
            calculateUsiaKehamilan();
        });

        // --- FUNGSI BANTU: HITUNG USIA KEHAMILAN OTOMATIS ---
        function calculateUsiaKehamilan() {
            let hphtVal = $('#tanggal_hpht').val();
            if (hphtVal) {
                let hphtTime = new Date(hphtVal).getTime();
                let nowTime = new Date().getTime();

                if (nowTime > hphtTime) {
                    let diffTime = Math.abs(nowTime - hphtTime);
                    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    let usiaMinggu = Math.floor(diffDays / 7);

                    $('#usia_kehamilan').val(usiaMinggu);
                } else {
                    $('#usia_kehamilan').val(0);
                }
            }
        }
    });
</script>