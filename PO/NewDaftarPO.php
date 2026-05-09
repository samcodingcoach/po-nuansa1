<?php
session_start();
include('../StructureIndex/head-library.php');
include('../Connection/validateSession.php');
require_once("../classes/AccurateAPI.php");

// 1. Logika Session Restricted (Hak Akses)
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "%";
if(!isset($_REQUEST['supplier']) && $_SESSION['restricted_nuansa1'] == "%") {
    $supplier = "%";
} else if(!isset($_REQUEST['supplier']) && $_SESSION['restricted_nuansa1'] != "%") {
    $supplier = trim($_SESSION['restricted_nuansa1']);
} else {
    $supplier = $_REQUEST['supplier'];
}

// 2. Manajemen Tanggal (Format HTML5 adalah YYYY-MM-DD)
if(!isset($_REQUEST['tanggal'])) {
    $date = date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));
} else { 
    $date = $_REQUEST['tanggal']; 
}

if(!isset($_REQUEST['tanggal2'])) {
    $date2 = date("Y-m-d");
} else { 
    $date2 = $_REQUEST['tanggal2']; 
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Purchase Order</title>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        var currentPage = 1;
        var pageSize = 100; // Samakan dengan pageSize di AccurateAPI.php/list_po.php

        function clickView() {
            currentPage = 1;
            $('#bodyTablePO').html('<tr><td colspan="7" align="center">Memuat data...</td></tr>');
            loadDataPO();
        }

        function loadDataPO() {
            var tgl1_raw = $('#txtTgl').val().split("-");
            var tgl2_raw = $('#txtTgl2').val().split("-");
            
            var tgl1 = tgl1_raw[2] + '/' + tgl1_raw[1] + '/' + tgl1_raw[0];
            var tgl2 = tgl2_raw[2] + '/' + tgl2_raw[1] + '/' + tgl2_raw[0];
            
            var supp = $('#lstSupplier').val();
            var stat = $('#lstStatus').val();

            $.ajax({
                url: 'list_po.php',
                type: 'GET',
                data: {
                    page: currentPage,
                    vendorNo: supp,
                    fromDate: tgl1,
                    toDate: tgl2,
                    status: stat
                },
                success: function(response) {
                    if (currentPage === 1) $('#bodyTablePO').empty();
                    
                    if (response.success && response.data.d.length > 0) {
                        $.each(response.data.d, function(i, item) {
                            var nomorUrut = ((currentPage - 1) * pageSize) + (i + 1);
                            var row = `<tr>
                                <td align="center">${nomorUrut}</td>
                                <td align="center">${item.transDate}</td>
                                <td>${item.number}</td>
                                <td>${item.vendor.name}</td>
                                <td align="center">${item.status}</td>
                                <td align="right">${parseFloat(item.totalAmount).toLocaleString('id-ID')}</td>
                                <td align="center">
                                    <span class="action-link" onclick="clickDetail('${item.number}')">Detail</span>
                                    ${(item.status !== 'REJECTED' && item.status !== 'DRAFT') ? ` | <span class="action-link" onclick="clickSJ('${item.number}')">Cetak SJ</span>` : ''}
                                </td>
                            </tr>`;
                            $('#bodyTablePO').append(row);
                        });
                        if (response.pagination && response.pagination.more) $('#btnLoadMore').show(); else $('#btnLoadMore').hide();
                    } else {
                        if (currentPage === 1) $('#bodyTablePO').html('<tr><td colspan="7" align="center">Data tidak ditemukan</td></tr>');
                        $('#btnLoadMore').hide();
                    }
                }
            });
        }

        function nextBatch() { currentPage++; loadDataPO(); }

        $(document).ready(function() {
            loadDataPO();

            // Inisialisasi Select2
            $('#lstSupplier').select2({
                placeholder: "--- Pilih Supplier ---",
                allowClear: true,
                width: '350px',
                ajax: {
                    url: '../Vendor/list.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { 
                        return { 
                            search: params.term, 
                            page: params.page || 1 
                        }; 
                    },
                    processResults: function (data) {
                        return { 
                            results: $.map(data.data, function (obj) { 
                                // FORMAT: vendorNo - name
                                return { id: obj.vendorNo, text: obj.vendorNo + ' - ' + obj.name }; 
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    }
                }
            });
        });

        function clickDetail(no) { window.open('newPrintPO.php?nomor_po='+no, '_blank');  }
        function clickSJ(no) { window.open('newPrintSJ.php?nomor_po='+no, '_blank'); }
    </script>

    <style>
        .box h2 { background: #2E5E79; color: #fff; padding: 10px; margin: 0; font-size: 16px; }
        .block { padding: 15px; }
        .form td { padding: 8px 5px; vertical-align: middle; }
        .col1 { width: 130px; font-weight: bold; font-size: 13px; }
        .myTable th { background-color:#2E5E79; color:#FFF; padding:10px; text-align:center; }
        .myTable td { padding:8px; border-bottom:1px solid #ddd; font-size:12px; }
        .action-link { color: #2E5E79; cursor: pointer; text-decoration: underline; font-weight: bold; }
        input[type="date"] { padding: 4px; border: 1px solid #aaa; font-family: inherit; }
        .select2-container--default .select2-selection--single { border-radius: 0; height: 30px; border: 1px solid #aaa; }
        #btnLoadMore { margin: 20px; padding: 10px 25px; cursor: pointer; background: #2E5E79; color: white; border: none; display:none; }
    </style>
</head>
<body>
    <div class="box round first fullpage" style="padding:20px;">
        <h2>Daftar PO (Accurate: Pesanan Pembelian)</h2>
        <div class="block">
            <form onsubmit="return false;">
                <table class="form" style="width: auto; margin-left: 0;">
                    <tr>
                        <td class="col1">Status</td>
                        <td>
                            <select id="lstStatus" style="width: 250px;">
                                <option value="%" <?= ($status=="%")?'selected':'' ?>>ALL</option>
                                <option value="DRAFT" <?= ($status=="DRAFT")?'selected':'' ?>>DRAFT</option>
                                <option value="ONPROCESS" <?= ($status=="ONPROCESS")?'selected':'' ?>>ONPROCESS</option>
                                <option value="WAITING" <?= ($status=="WAITING")?'selected':'' ?>>WAITING</option>
                                <option value="FULLRECEIVED" <?= ($status=="FULLRECEIVED")?'selected':'' ?>>FULLRECEIVED</option>
                                <option value="CLOSED" <?= ($status=="CLOSED")?'selected':'' ?>>CLOSED</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="col1">Supplier</td>
                        <td>
                            <select id="lstSupplier" <?= ($_SESSION['restricted_nuansa1'] != "%") ? 'disabled="disabled"' : '' ?>>
                                <?php if($supplier != "%") { ?>
                                    <option value="<?= $supplier ?>" selected="selected"><?= $supplier ?></option>
                                <?php } else { ?>
                                    <option value="%">All</option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="col1">Tanggal</td>
                        <td>
                            <input id="txtTgl" type="date" value="<?= $date ?>">
                            s/d
                            <input id="txtTgl2" type="date" value="<?= $date2 ?>">
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="button" value="View Data PO" onclick="clickView();" style="padding: 6px 25px; cursor: pointer; background: #2E5E79; color: white; border: none; font-weight: bold;">
                        </td>
                    </tr>
                </table>

                <div style="margin-top:25px; overflow:auto; max-height:600px;">
                    <table class="myTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th>No.</th><th>Tanggal</th><th>No. PO</th><th>Vendor</th><th>Status</th><th>Total</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bodyTablePO"></tbody>
                    </table>
                    <div align="center">
                        <button id="btnLoadMore" onclick="nextBatch();">Tampilkan 100 Data Berikutnya</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>