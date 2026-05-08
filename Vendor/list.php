<?php
/**
 * File: Vendor/list.php
 */
require_once __DIR__ . '/../classes/AccurateAPI.php'; 
header('Content-Type: application/json; charset=UTF-8');

$api = new AccurateAPI();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$pageSize = 100;

$params = array(
    'sp.page' => $page,
    'sp.pageSize' => $pageSize,
    'sp.sort' => 'name|asc',
    'fields' => 'id,name,vendorNo'
);

// PENTING: Mengirim kata kunci pencarian ke Accurate
if ($search != '') {
    $params['filter.keywords.op'] = 'CONTAIN';
    $params['filter.keywords.val'] = $search;
}

$result = $api->getVendorList($params);

if ($result['success']) {
    $responseData = isset($result['data']['d']) ? $result['data']['d'] : array();
    echo json_encode(array(
        'status'  => 'success',
        'data'    => $responseData,
        'pagination' => array(
            'more' => (count($responseData) >= $pageSize)
        )
    ));
} else {
    http_response_code(400);
    echo json_encode(array('status' => 'error'));
}
?>