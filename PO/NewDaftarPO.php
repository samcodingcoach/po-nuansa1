<?php
session_start();
include('../StructureIndex/head-library.php');
include('../Connection/validateSession.php');

require_once("../classes/AccurateAPI.php");
$api = new AccurateAPI();

// 1. Konfigurasi Session & Filter
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "%";

if(!isset($_REQUEST['supplier']) && $_SESSION['restricted_nuansa1'] == "%") {
    $supplier = "%";
} else if(!isset($_REQUEST['supplier']) && $_SESSION['restricted_nuansa1'] != "%") {
    $supplier = trim($_SESSION['restricted_nuansa1']);
} else {
    $supplier = $_REQUEST['supplier'];
}

// 2. Manajemen Tanggal
if(!isset($_REQUEST['tanggal'])) {
    $date = date("d-m-Y", mktime(date("H"),date("i"),date("s"),date("m")-1,date("d"),date("Y")));
} else {
    $date = $_REQUEST['tanggal'];
}

if(!isset($_REQUEST['tanggal2'])) {
    $date2 = date("d-m-Y");
} else {
    $date2 = $_REQUEST['tanggal2'];
}

// ======================================================================
// INTEGRASI ACCURATE API
// ======================================================================

// Ambil SEMUA Vendor untuk pencarian lokal agar tidak hit API terus menerus
$resVendor = $api->getVendorList(array('fields' => 'vendorNo,name', 'sp.pageSize' => 2000));
$vendors = ($resVendor['success'] && isset($resVendor['data']['d'])) ? $resVendor['data']['d'] : array();

$api_start = str_replace('-', '/', $date);
$api_end = str_replace('-', '/', $date2);

// Susun Parameter API Purchase Order
$params = array(
    'filter.transDate.op'     => 'BETWEEN',
    'filter.transDate.val[0]' => $api_start,
    'filter.transDate.val[1]' => $api_end,
    'sp.pageSize'             => 500
);

if ($status != "%") {
    $params['filter.status.op'] = 'EQUAL';
    $params['filter.status.val'] = $status;
}

if ($supplier != "%") {
    $params['filter.vendorNo'] = $supplier;
}

$resPO = $api->getPurchaseOrderList($params);
$poData = ($resPO['success'] && isset($resPO['data']['d'])) ? $resPO['data']['d'] : array();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Purchase Order - Accurate System</title>
    
    <script language="javascript" src="../lib Calendar/calendar.js"></script>
    <script language="javascript" src="../lib Calendar/datetimepicker.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script src="../js/JS-GlobalFunction.js" type="text/javascript"></script>
    <script src="../js/JS-GlobalFunction1.js" type="text/javascript"></script>
    
    <script type="text/javascript">
        function clickView() {
            window.location="NewDaftarPO.php?status="+document.getElementById("lstStatus").value+
                            "&supplier="+document.getElementById("lstSupplier").value+
                            "&tanggal="+document.getElementById("txtTgl").value+
                            "&tanggal2="+document.getElementById("txtTgl2").value;
        }
        
        function clickDetail(PONumber) {
            window.location="printPO.php?nomor_po="+PONumber;
        }
        
        function clickSJ(PONumber) {
            var printWindow = window.open('newPrintSJ.php?nomor_po='+PONumber, 'printSJ', 'menubar=no,status=no,scrollbars=yes,width=900,height=600');
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
            };
        }

        // Inisialisasi Select2
        $(document).ready(function() {
            $('#lstSupplier').select2({
                placeholder: "Pilih atau Ketik Nama Vendor...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    
    <style type="text/css">
        .box { background: #fff; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; }
        .box h2 { background: #2E5E79; color: #fff; padding: 10px 20px; margin: 0; font-size: 16px; border-radius: 4px 4px 0 0; }
        .form td { padding: 8px; vertical-align: middle; }
        .myTable { border-collapse: collapse; margin-top: 10px; }
        .myTable th { padding: 10px; text-align: center; }
        .myTable td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 12px; }
        .select2-container--default .select2-selection--single {
            border-radius: 0px !important;
            height: 30px !important;
            border: 1px solid #aaa !important;
        }
    </style>
</head>
<body>

    <div class="box round first fullpage" style="padding:20px;">
        <h2>Daftar PO (Accurate System)</h2>
        <div class="block">
            <form onsubmit="return false;">
                <table class="form">
                    <tr>
                        <td style="width:10%;" class="col1"><label>Status</label></td>
                        <td class="col2">
                            <select name="lstStatus" id="lstStatus" style="width: 250px;">
                                <option <? if($status=="%"){?> selected="selected"<? }?> value="%">ALL</option>
                                <option <? if($status=="DRAFT"){?> selected="selected"<? }?> value="DRAFT">DRAFT</option>
                                <option <? if($status=="ONPROCESS"){?> selected="selected"<? }?> value="ONPROCESS">ONPROCESS</option>
                                <option <? if($status=="WAITING"){?> selected="selected"<? }?> value="WAITING">WAITING</option>
                                <option <? if($status=="FULLRECEIVED"){?> selected="selected"<? }?> value="FULLRECEIVED">FULLRECEIVED</option>
                                <option <? if($status=="CLOSED"){?> selected="selected"<? }?> value="CLOSED">CLOSED</option>
                                <option <? if($status=="REJECTED"){?> selected="selected"<? }?> value="REJECTED">REJECTED</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:10%;" class="col1"><label>Supplier</label></td>
                        <td class="col2" style="width: 400px;">
                            <select name="lstSupplier" id="lstSupplier">
                                <option value="%">-- Semua Supplier --</option>
                                <? foreach($vendors as $vRow) { ?>
                                    <option <? if($supplier==$vRow['vendorNo']){?> selected="selected"<? }?> value="<? echo $vRow['vendorNo'];?>"><? echo $vRow['name'];?></option>
                                <? } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="col1"><label>Tanggal (d-m-y)</label></td>
                        <td class="col2">
                            <input id="txtTgl" name="tanggal" type="text" size="15" value="<? echo $date;?>" readonly>
                            <a href="javascript:callCalendarDMY('txtTgl');"><img width="16" height="16" border="0" src="../lib Calendar/cal.gif"></a>
                             - 
                            <input id="txtTgl2" name="tanggal2" type="text" size="15" value="<? echo $date2;?>" readonly>
                            <a href="javascript:callCalendarDMY('txtTgl2');"><img width="16" height="16" border="0" src="../lib Calendar/cal.gif"></a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="button" value="View" onclick="clickView();" style="cursor:pointer; padding: 5px 20px; background: #2E5E79; color: #fff; border: none;">
                        </td>
                    </tr>
                </table>

                <div style="overflow:auto; max-height:450px; border-top: 1px solid #ddd; margin-top: 20px;">
                    <table class="myTable" style="width:100%;">
                        <thead style="background-color:#2E5E79; color:#FFF;">
                            <tr>
                                <th style="width:3%;">No.</th>
                                <th style="width:10%;">Tanggal</th>
                                <th style="width:15%;">No. PO</th>
                                <th style="width:30%;">Vendor</th>
                                <th style="width:10%;">Status</th>
                                <th style="width:15%;">Total</th>
                                <th style="width:17%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?
                        $no=0;
                        if(empty($poData)) {
                            echo "<tr><td colspan='7' align='center' style='padding:20px;'>Tidak ada data ditemukan.</td></tr>";
                        } else {
                            foreach($poData as $poRow) {
                                $no+=1;
                        ?>
                            <tr>
                                <td style="text-align:center;"><? echo $no;?></td>
                                <td style="text-align:center;"><? echo $poRow['transDate'];?></td>
                                <td><? echo $poRow['number'];?></td>
                                <td><? echo $poRow['vendor']['name'];?></td>
                                <td style="text-align:center;"><? echo $poRow['status'];?></td>
                                <td style="text-align:right;">
                                    <? echo number_format($poRow['totalAmount'], 2, ",", ".");?>
                                </td>
                                <td style="text-align:center;">
                                    <a style="cursor:pointer;" onclick="clickDetail('<? echo $poRow['number'];?>');">Detail</a>
                                    <? if($poRow['status'] != "REJECTED" && $poRow['status'] != "DRAFT") { ?>
                                         || <a style="cursor:pointer;" onclick="clickSJ('<? echo $poRow['number'];?>');">Surat Jalan</a>
                                    <? } ?>
                                </td>
                            </tr>
                        <? 
                            } 
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</body>
</html>