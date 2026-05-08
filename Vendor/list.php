<?php
/**
 * File: api/vendor/list.php
 * Prosedur: Paginasi Murni Accurate
 */

require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json; charset=UTF-8');

$api = new AccurateAPI();

// Tangkap nomor halaman dari request Select2 (default ke halaman 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Gunakan pageSize 100 sesuai keinginan Anda
$pageSize = 100;

$params = array(
    'sp.page'     => $page,
    'sp.pageSize' => $pageSize,
    'sp.sort'     => 'name|asc' // Mengurutkan berdasarkan nama
);

// Tambahkan filter kata kunci jika user mengetik di kotak search Select2
if (isset($_GET['search']) && $_GET['search'] != '') {
    $params['filter.keywords'] = $_GET['search'];
}

$result = $api->getVendorList($params);

if ($result['success']) {
    $responseData = isset($result['data']['d']) ? $result['data']['d'] : array();
    
    echo json_encode(array(
        'status'  => 'success',
        'data'    => $responseData,
        // Prosedur teknis agar Select2 tahu ada data selanjutnya (101-200, dst)
        'pagination' => array(
            'more' => (count($responseData) === $pageSize) 
        )
    ));
} else {
    http_response_code(400);
    echo json_encode(array(
        'status'  => 'error',
        'message' => isset($result['error']) ? $result['error'] : 'Gagal'
    ));
}