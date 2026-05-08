<?php
/**
 * API untuk mendapatkan list purchase order dalam format JSON
 * Versi Kompatibel: PHP 5.6
 * File: /PO/list_po.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ));
    exit;
}

try {
    $api = new AccurateAPI();
    $extraParams = array();
    
    // 1. Paginasi (Menerima parameter 'page' dari request frontend)
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pageSize = 100; // Konsisten dengan pageSize di sistem paging
    
    $extraParams['sp.page'] = $page;
    $extraParams['sp.pageSize'] = $pageSize;

    // 2. Filter Vendor
    // Jika supplier bernilai '%' (All), maka tidak perlu menambah filter vendor
    if (isset($_GET['vendorNo']) && !empty($_GET['vendorNo']) && $_GET['vendorNo'] !== '%') {
        $extraParams['filter.vendorNo'] = $_GET['vendorNo'];
    }

    // 3. Filter Status
    if (isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] !== '%') {
        $extraParams['filter.status.op'] = 'EQUAL';
        $extraParams['filter.status.val'] = $_GET['status'];
    }

    // 4. Filter Tanggal (Range)
    if (isset($_GET['fromDate']) && !empty($_GET['fromDate']) && 
        isset($_GET['toDate']) && !empty($_GET['toDate'])) {
        
        $extraParams['filter.transDate.op'] = 'BETWEEN';
        $extraParams['filter.transDate.val[0]'] = $_GET['fromDate']; 
        $extraParams['filter.transDate.val[1]'] = $_GET['toDate'];
    } 
    
    // Panggil fungsi API utama dari class AccurateAPI
    $result = $api->getPurchaseOrderList($extraParams);
    
    if ($result['success']) {
        $responseData = isset($result['data']['d']) ? $result['data']['d'] : array();
        
        // Menambahkan metadata paginasi agar Frontend (NewDaftarPO.php) tahu 
        // kapan harus memunculkan tombol "Load More"
        $result['pagination'] = array(
            'more' => (count($responseData) === $pageSize)
        );

        // Bersihkan raw_response agar output JSON ringan saat dikirim ke browser
        if (isset($result['raw_response'])) {
            unset($result['raw_response']);
        }
        
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(array(
            'success' => false, 
            'message' => isset($result['error']) ? $result['error'] : 'Gagal mengambil data PO'
        ), JSON_PRETTY_PRINT);
    }
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false, 
        'message' => $e->getMessage()
    ), JSON_PRETTY_PRINT);
}
?>