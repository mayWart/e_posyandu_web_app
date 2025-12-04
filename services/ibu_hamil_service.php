<?php

// session_start();
header('Content-Type: application/json');

// --- PERLINDUNGAN AKSES ADMIN ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya Admin yang diizinkan.']);
    exit;
}
// ---------------------------------

include '../config.php'; // Pastikan ini mendefinisikan objek koneksi MySQLi, misalnya $conn

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Aksi tidak valid.'];

// Asumsikan $conn adalah objek mysqli yang terhubung di config.php
if (!isset($conn) || $conn->connect_error) {
    $response['message'] = 'Koneksi database gagal atau tidak tersedia.';
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {

        // --- READ: Mengambil semua data (list) ---
        case 'list':
            // PERBAIKAN: Mengganti JOIN u ON ih.id = u.id menjadi u ON ih.id_user = u.id (Sesuai skema ERD)
            $sql = "SELECT ih.id_ibu_hamil, ih.nama_ibu, ih.usia_kehamilan, ih.tanggal_hpht, ih.tanggal_perkiraan_lahir, u.nama_lengkap 
                    FROM ibu_hamil ih
                    JOIN users u ON ih.id_user = u.id 
                    ORDER BY ih.id_ibu_hamil DESC"; // Perbaikan: Menyesuaikan nama tabel user

            $result = $conn->query($sql);
            $data = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
            $response = ['status' => 'success', 'data' => $data];
            break;

        // --- READ: Mengambil satu data (Untuk mode edit) ---
        case 'get_detail':
            $id_ibu_hamil = $_GET['id'] ?? 0;
            if ($id_ibu_hamil == 0)
                throw new Exception("ID Ibu Hamil tidak valid.");

            $sql = "SELECT * FROM ibu_hamil WHERE id_ibu_hamil = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt)
                throw new Exception("Prepared statement failed: " . $conn->error);

            $stmt->bind_param("i", $id_ibu_hamil);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            if (!$data)
                throw new Exception("Data tidak ditemukan.");

            $response = ['status' => 'success', 'data' => $data];
            $stmt->close();
            break;

        // --- CREATE: Menambahkan data baru ---
        case 'create':
            // ASUMSI: $_SESSION['id'] adalah ID pengguna yang sedang login
            $id_user = $_SESSION['id'] ?? 0;

            $nama_ibu = $_POST['nama_ibu'] ?? '';
            $usia_kehamilan = $_POST['usia_kehamilan'] ?? 0;
            $tanggal_hpht = $_POST['tanggal_hpht'] ?? '';
            $tanggal_perkiraan_lahir = $_POST['tanggal_perkiraan_lahir'] ?? '';
            $alamat = $_POST['alamat'] ?? '';
            $nomor_telepon = $_POST['nomor_telepon'] ?? '';

            if ($id_user == 0 || empty($nama_ibu) || empty($tanggal_hpht))
                throw new Exception("Data wajib harus diisi atau sesi pengguna hilang.");

            // PERBAIKAN QUESRY: Mengganti 'id' menjadi 'id_user' di kolom list
            $sql = "INSERT INTO ibu_hamil (id_user, nama_ibu, usia_kehamilan, tanggal_hpht, tanggal_perkiraan_lahir, alamat, nomor_telepon) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt)
                throw new Exception("Prepared statement failed: " . $conn->error);

            // PERBAIKAN BINDING: Mengganti "isissis" menjadi "isissss" (id_user (i), nama(s), usia(i), tgl_hpht(s), tgl_lahir(s), alamat(s), no_telp(s))
            // Pastikan Anda menggunakan $id_user yang diambil dari sesi
            $stmt->bind_param("isissss", $id_user, $nama_ibu, $usia_kehamilan, $tanggal_hpht, $tanggal_perkiraan_lahir, $alamat, $nomor_telepon);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Data Ibu Hamil berhasil ditambahkan.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Gagal menambahkan data, 0 baris terpengaruh: ' . $stmt->error];
            }
            $stmt->close();
            break;

        // --- UPDATE: Mengubah data yang sudah ada ---
        case 'update':
            $id_ibu_hamil = $_POST['id_ibu_hamil'] ?? 0;
            $nama_ibu = $_POST['nama_ibu'] ?? '';
            $usia_kehamilan = $_POST['usia_kehamilan'] ?? 0;
            $tanggal_hpht = $_POST['tanggal_hpht'] ?? '';
            $tanggal_perkiraan_lahir = $_POST['tanggal_perkiraan_lahir'] ?? '';
            $alamat = $_POST['alamat'] ?? '';
            $nomor_telepon = $_POST['nomor_telepon'] ?? '';

            if ($id_ibu_hamil == 0 || empty($nama_ibu) || empty($tanggal_hpht))
                throw new Exception("ID dan data wajib harus diisi.");

            $sql = "UPDATE ibu_hamil SET nama_ibu = ?, usia_kehamilan = ?, tanggal_hpht = ?, tanggal_perkiraan_lahir = ?, alamat = ?, nomor_telepon = ? 
                    WHERE id_ibu_hamil = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt)
                throw new Exception("Prepared statement failed: " . $conn->error);

            $stmt->bind_param("sissisi", $nama_ibu, $usia_kehamilan, $tanggal_hpht, $tanggal_perkiraan_lahir, $alamat, $nomor_telepon, $id_ibu_hamil);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Data Ibu Hamil berhasil diperbarui.'];
            } else {
                $response = ['status' => 'info', 'message' => 'Tidak ada perubahan yang dilakukan pada data.'];
            }
            $stmt->close();
            break;

        // --- DELETE: Menghapus data ---
        case 'delete':
            $id_ibu_hamil = $_POST['id_ibu_hamil'] ?? 0;
            if ($id_ibu_hamil == 0)
                throw new Exception("ID Ibu Hamil tidak valid.");

            $conn->begin_transaction();

            try {
                // 1. Hapus data pemeriksaan terkait
                $sql_del_pemeriksaan = "DELETE FROM pemeriksaan_ibu_hamil WHERE id_ibu_hamil = ?";
                $stmt_p = $conn->prepare($sql_del_pemeriksaan);
                if (!$stmt_p)
                    throw new Exception("Prepared statement pemeriksaan failed: " . $conn->error);
                $stmt_p->bind_param("i", $id_ibu_hamil);
                $stmt_p->execute();
                $stmt_p->close();

                // 2. Hapus data ibu hamil
                $sql_del_ibu = "DELETE FROM ibu_hamil WHERE id_ibu_hamil = ?";
                $stmt_i = $conn->prepare($sql_del_ibu);
                if (!$stmt_i)
                    throw new Exception("Prepared statement ibu failed: " . $conn->error);
                $stmt_i->bind_param("i", $id_ibu_hamil);
                $stmt_i->execute();

                if ($stmt_i->affected_rows > 0) {
                    $conn->commit();
                    $response = ['status' => 'success', 'message' => 'Data Ibu Hamil berhasil dihapus.'];
                } else {
                    $conn->rollback();
                    $response = ['status' => 'error', 'message' => 'Gagal menghapus data ibu hamil.'];
                }
                $stmt_i->close();

            } catch (Exception $e) {
                $conn->rollback();
                throw $e; // Lempar ke blok catch luar untuk respons JSON
            }
            break;

        default:
            $response['message'] = 'Aksi yang diminta tidak dikenali.';
            break;
    }

} catch (Exception $e) {
    // Tangani kegagalan dari blok try/catch
    $response['message'] = 'Operasi gagal: ' . $e->getMessage();
}

echo json_encode($response);
// Pastikan tidak ada penutup koneksi di sini agar bisa digunakan oleh layanan lain
// $conn->close(); 
?>