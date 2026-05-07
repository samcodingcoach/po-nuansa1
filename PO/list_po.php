<?php
/**
 * API untuk mendapatkan list purchase order dalam format JSON
 * Versi Kompatibel: PHP 5.6
 * File: /purchaseorder/list_po.php
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
    // Inisialisasi AccurateAPI (Versi 5.6)
    $api = new AccurateAPI();
    
    // Siapkan filter dinamis
    $extraParams = array();
    
    // ======================================================================
    // 1. FILTER VENDOR BERDASARKAN NOMOR VENDOR (Direct String)
    // ======================================================================
    if (isset($_GET['vendorNo']) && !empty($_GET['vendorNo'])) {
        // Langsung diisi string tanpa .op dan .val sesuai dokumentasi
        $extraParams['filter.vendorNo'] = $_GET['vendorNo'];
    }

    // 2. Paginasi
    if (isset($_GET['page'])) {
        $extraParams['sp.page'] = $_GET['page'];
    }
    
    // ======================================================================
    // 3. FILTER TANGGAL PENGAKUAN TRANSAKSI (transDate)
    // ======================================================================
    
    // Skenario A: Rentang Tanggal (BETWEEN) menggunakan fromDate & toDate
    if (isset($_GET['fromDate']) && !empty($_GET['fromDate']) && 
        isset($_GET['toDate']) && !empty($_GET['toDate'])) {
        
        $extraParams['filter.transDate.op'] = 'BETWEEN';
        // Gunakan index [0] dan [1] karena operator BETWEEN butuh 2 parameter
        $extraParams['filter.transDate.val[0]'] = $_GET['fromDate']; 
        $extraParams['filter.transDate.val[1]'] = $_GET['toDate'];
        
    } 
    // Skenario B: Satu Tanggal Spesifik (EQUAL) menggunakan transDate
    elseif (isset($_GET['transDate']) && !empty($_GET['transDate'])) {
        
        $extraParams['filter.transDate.op'] = 'EQUAL';
        $extraParams['filter.transDate.val'] = $_GET['transDate']; 
        
    }
    
    // Panggil fungsi dengan parameter yang sudah disiapkan
    $result = $api->getPurchaseOrderList($extraParams);
    
    if ($result['success']) {
        // Meniadakan raw_response agar output JSON bersih
        if (isset($result['raw_response'])) {
            unset($result['raw_response']);
        }
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        $errorMessage = isset($result['error']) ? $result['error'] : 'Failed to fetch purchase order data';
        
        $errorResponse = array(
            'success' => false,
            'message' => $errorMessage
        );
        echo json_encode($errorResponse, JSON_PRETTY_PRINT);
    }
} catch (Exception $e) {
    $exceptionResponse = array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    );
    echo json_encode($exceptionResponse, JSON_PRETTY_PRINT);
}
?>