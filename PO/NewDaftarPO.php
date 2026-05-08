<?php
session_start();
include('../StructureIndex/head-library.php');
include('../Connection/validateSession.php');
require_once("../classes/AccurateAPI.php");

$api = new AccurateAPI();

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "%";
$supplier = isset($_REQUEST['supplier']) ? $_REQUEST['supplier'] : "%";

// Manajemen Tanggal
if(!isset($_REQUEST['tanggal'])) {
    $date = date("d-m-Y", mktime(date("H"),date("i"),date("s"),date("m")-1,date("d"),date("Y")));
} else { $date = $_REQUEST['tanggal']; }

if(!isset($_REQUEST['tanggal2'])) {
    $date2 = date("d-m-Y");
} else { $date2 = $_REQUEST['tanggal2']; }

// Integrasi Accurate PO
$api_start = str_replace('-', '/', $date);
$api_end = str_replace('-', '/', $date2);

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
<html>
<head>
    <title>Daftar Purchase Order</title>
    <script language="javascript" src="../lib Calendar/calendar.js"></script>
    <script language="javascript" src="../lib Calendar/datetimepicker.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        function clickView() {
            window.location="NewDaftarPO.php?status="+$('#lstStatus').val()+
                            "&supplier="+$('#lstSupplier').val()+
                            "&tanggal="+$('#txtTgl').val()+
                            "&tanggal2="+$('#txtTgl2').val();
        }

        $(document).ready(function() {
            $('#lstSupplier').select2({
                placeholder: "--- Pilih Supplier ---",
                allowClear: true, // Tombol X untuk reset
                width: '100%',
                ajax: {
                    url: '../Vendor/list.php', 
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term, // Kata kunci dikirim ke PHP
                            page: params.page || 1
                        };
                    },
                    processResults: function (response, params) {
                        params.page = params.page || 1;
                        var mapped = $.map(response.data, function (obj) {
                            return {
                                id: obj.vendorNo, // Value saat form dikirim
                                text: obj.name    // Teks yang tampil di dropdown & kotak select
                            };
                        });
                        return {
                            results: mapped,
                            pagination: { more: response.pagination.more }
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
    <style>
        .myTable th { background-color:#2E5E79; color:#FFF; padding:10px; text-align:center; }
        .myTable td { padding:8px; border-bottom:1px solid #ddd; font-size:12px; }
        .select2-container--default .select2-selection--single { height:32px !important; border-radius:0px !important; }
    </style>
</head>
<body>
    <div class="box round first fullpage" style="padding:20px;">
        <h2>Daftar PO (Accurate System)</h2>
        <div class="block">
            <table class="form">
                <tr>
                    <td style="width:10%;">Status</td>
                    <td>
                        <select id="lstStatus">
                            <option value="%" <?= ($status=="%")?'selected':'' ?>>ALL</option>
                            <option value="DRAFT" <?= ($status=="DRAFT")?'selected':'' ?>>DRAFT</option>
                            <option value="ONPROCESS" <?= ($status=="ONPROCESS")?'selected':'' ?>>ONPROCESS</option>
                            <option value="WAITING" <?= ($status=="WAITING")?'selected':'' ?>>WAITING</option>
                            <option value="FULLRECEIVED" <?= ($status=="FULLRECEIVED")?'selected':'' ?>>FULLRECEIVED</option>
                            <option value="CLOSED" <?= ($status=="CLOSED")?'selected':'' ?>>CLOSED</option>
                            <option value="REJECTED" <?= ($status=="REJECTED")?'selected':'' ?>>REJECTED</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Supplier</td>
                    <td style="width:400px;">
                        <select id="lstSupplier">
                            <option value="%">All</option>
                            <? if($supplier != "%") { ?>
                                <option value="<?= $supplier ?>" selected><?= $supplier ?></option>
                            <? } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>
                        <input id="txtTgl" type="text" size="15" value="<?= $date ?>" readonly>
                        <a onclick="callCalendarDMY('txtTgl');" style="cursor:pointer;"><img src="../lib Calendar/cal.gif"></a>
                        -
                        <input id="txtTgl2" type="text" size="15" value="<?= $date2 ?>" readonly>
                        <a onclick="callCalendarDMY('txtTgl2');" style="cursor:pointer;"><img src="../lib Calendar/cal.gif"></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="button" value="View" onclick="clickView();"></td>
                </tr>
            </table>

            <div style="margin-top:20px; overflow:auto; max-height:500px;">
                <table class="myTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>No.</th><th>Tanggal</th><th>No. PO</th><th>Vendor</th><th>Status</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <? $no=0; foreach($poData as $poRow) { $no++; ?>
                        <tr>
                            <td align="center"><?= $no ?></td>
                            <td align="center"><?= $poRow['transDate'] ?></td>
                            <td><?= $poRow['number'] ?></td>
                            <td><?= $poRow['vendor']['name'] ?></td>
                            <td align="center"><?= $poRow['status'] ?></td>
                            <td align="right"><?= number_format($poRow['totalAmount'], 2, ",", ".") ?></td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>