<?php
/**
 * File: api/vendor/list.php
 * Deskripsi: Mengambil daftar pemasok (vendor) dari Accurate Online
 * Versi Kompatibel: PHP 5.6
 */

// 1. Muat konfigurasi utama
// Ganti jumlah '../' sesuai dengan kedalaman folder tempat Anda menaruh file ini
require_once __DIR__ . '/../bootstrap.php';

// 3. Set header agar output berupa JSON
header('Content-Type: application/json; charset=UTF-8');

// Inisialisasi API
$api = new AccurateAPI();

// Tangkap parameter dari URL (Paginasi) dengan ternary operator ala PHP 5.6
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 100;

$params = array(
    'sp.page' => $page,
    'sp.pageSize' => $pageSize
);

// Panggil fungsi dari AccurateAPI.php
$result = $api->getVendorList($params);

// Format dan kembalikan response
if ($result['success']) {
    // Ambil array list vendor dari dalam index 'd' bawaan Accurate
    $responseData = isset($result['data']['d']) ? $result['data']['d'] : $result['data'];
    
    echo json_encode(array(
        'status'  => 'success',
        'message' => 'Berhasil mengambil daftar vendor',
        'data'    => $responseData
    ), JSON_PRETTY_PRINT);

} else {
    // Set HTTP code ke 400 (Bad Request) jika terjadi error
    http_response_code(400);
    
    // PHP 5.6: Hindari penggunaan operator ??
    $errorInfo = isset($result['error']) ? $result['error'] : 'Terjadi kesalahan yang tidak diketahui';
    
    echo json_encode(array(
        'status'  => 'error',
        'message' => 'Gagal mengambil data vendor: ' . $errorInfo,
        'data'    => null
    ), JSON_PRETTY_PRINT);
}
?>