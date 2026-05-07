<?php
/**
 * API untuk mendapatkan informasi branch/cabang
 * Versi Kompatibel: PHP 5.6
 * Berdasarkan dokumentasi Accurate API untuk branch
 */

require_once __DIR__ . '/../bootstrap.php';

// (Opsional) Jika Anda ingin memproteksinya agar wajib login, buka komentar di bawah ini:
// require_once __DIR__ . '/../utils/api_auth.php';

// Set header agar output dikenali sebagai JSON
header('Content-Type: application/json; charset=UTF-8');

// Inisialisasi API class (Versi 5.6)
$api = new AccurateAPI();

/**
 * PHP 5.6: Mengganti operator Null Coalescing (??) dengan isset ternary
 */
$branchId = null;
if (isset($_GET['id'])) {
    $branchId = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $branchId = $_POST['id'];
}

if ($branchId) {
    // Get branch detail by ID
    $result = $api->getBranchDetail($branchId);
    $message = "Detail branch dengan ID: " . $branchId;
    
    // Jika ID tidak valid, berikan saran ID yang valid
    if (!$result['success'] && 
        isset($result['message']) && 
        strpos($result['message'], 'Invalid field value') !== false) {
        
        $branchList = $api->getBranchList();
        if ($branchList['success'] && isset($branchList['data']['d'])) {
            // PHP 5.6: array_column aman digunakan
            $validIds = array_column($branchList['data']['d'], 'id');
            
            $result['data'] = array(
                'error' => 'Invalid branch ID',
                'provided_id' => $branchId,
                'valid_ids' => $validIds,
                'suggestion' => 'Use one of the valid IDs from the valid_ids array'
            );
            $result['message'] = 'Branch ID tidak valid. Gunakan ID yang valid dari daftar.';
        }
    }
} else {
    // Get branch list (tanpa parameter)
    $result = $api->getBranchList();
    $message = "Informasi branch";
}

// MENGGANTI jsonResponse() DENGAN json_encode() STANDAR
if ($result['success']) {
    // Sesuaikan posisi data dari struktur array Accurate (biasanya di dalam index 'd')
    $responseData = isset($result['data']['d']) ? $result['data']['d'] : $result['data'];
    
    echo json_encode(array(
        'status'  => 'success',
        'message' => $message,
        'data'    => $responseData
    ), JSON_PRETTY_PRINT);
} else {
    // Set HTTP code 400 (Bad Request)
    http_response_code(400);
    
    // PHP 5.6: Mengganti ?? dengan isset
    $errorInfo = isset($result['error']) ? $result['error'] : 'Unknown error';
    $errorMessage = 'Gagal mengambil data branch: ' . $errorInfo;
    
    echo json_encode(array(
        'status'  => 'error',
        'message' => $errorMessage,
        'data'    => null
    ), JSON_PRETTY_PRINT);
}
?>