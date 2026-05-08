<?php
/**
 * File: api/vendor/list.php
 */

require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json; charset=UTF-8');

$api = new AccurateAPI();

// Tangkap parameter dari request Select2
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = 100; // StandarpageSize yang stabil

$params = array(
    'sp.page'     => $page,
    'sp.pageSize' => $pageSize,
    'sp.sort'     => 'name|asc', // Sesuai dokumentasi sp.sort
    'fields'      => 'id,name,vendorNo,email' // Ringan
);

// Filter keywords jika user mengetik di kotak search
if (isset($_GET['search']) && $_GET['search'] != '') {
    $params['filter.keywords'] = $_GET['search'];
}

$result = $api->getVendorList($params);

if ($result['success']) {
    $responseData = isset($result['data']['d']) ? $result['data']['d'] : array();
    
    echo json_encode(array(
        'status'  => 'success',
        'data'    => $responseData, // Array utama
        'pagination' => array(
            // more true jika jumlah data yang didapat sama dengan pageSize
            'more' => (count($responseData) === $pageSize) 
        )
    ));
} else {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Gagal'));
}
?>