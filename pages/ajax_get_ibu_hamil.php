<?php
// e_posyandu_web_app/pages/ajax_get_ibu_hamil.php

session_start();
// File ini hanya berfungsi sebagai proxy, pastikan perlindungan admin tetap ada
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

// Teruskan permintaan ke service utama
// Catatan: Pastikan Anda menyesuaikan parameter GET/POST jika ini digunakan
include '../services/ibu_hamil_service.php';
?>