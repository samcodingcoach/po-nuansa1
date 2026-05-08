<?php
session_start();
include('../StructureIndex/head-library.php');
include('../Connection/validateSession.php');
require_once("../classes/AccurateAPI.php");

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "%";
$supplier = isset($_REQUEST['supplier']) ? $_REQUEST['supplier'] : "%";

// 1. Ambil Tanggal dari Request (Format d-m-Y)
if(!isset($_REQUEST['tanggal'])) {
    $date = date("d-m-Y", mktime(date("H"),date("i"),date("s"),date("m")-1,date("d"),date("Y")));
} else { $date = $_REQUEST['tanggal']; }

if(!isset($_REQUEST['tanggal2'])) {
    $date2 = date("d-m-Y");
} else { $date2 = $_REQUEST['tanggal2']; }

// 2. Konversi Tanggal ke Format Accurate (d/m/Y) untuk dikirim via AJAX
$ajax_start = str_replace('-', '/', $date);
$ajax_end = str_replace('-', '/', $date2);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Purchase Order</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script language="javascript" src="../lib Calendar/calendar.js"></script>
    <script language="javascript" src="../lib Calendar/datetimepicker.js"></script>

    <script type="text/javascript">
        var currentPage = 1;

        function clickView() {
            // Saat klik view, ambil nilai tanggal terbaru dari input dan ganti - jadi /
            var tgl1 = $('#txtTgl').val().replace(/-/g, '/');
            var tgl2 = $('#txtTgl2').val().replace(/-/g, '/');
            var supp = $('#lstSupplier').val();
            var stat = $('#lstStatus').val();

            // Reset ke halaman 1
            currentPage = 1;
            $('#bodyTablePO').html('<tr><td colspan="7" align="center">Loading data...</td></tr>');
            
            loadDataPO(tgl1, tgl2, supp, stat);
        }

        function loadDataPO(t1, t2, sp, st) {
            // Gunakan parameter default jika tidak didefinisikan (untuk load awal)
            var fDate = t1 || '<?= $ajax_start ?>';
            var tDate = t2 || '<?= $ajax_end ?>';
            var vNo = sp || '<?= $supplier ?>';
            var vStatus = st || '<?= $status ?>';

            $.ajax({
                url: 'list_po.php',
                type: 'GET',
                data: {
                    page: currentPage,
                    vendorNo: vNo,
                    fromDate: fDate,
                    toDate: tDate,
                    status: vStatus
                },
                success: function(response) {
                    if (currentPage === 1) $('#bodyTablePO').empty();
                    
                    if (response.success && response.data.d.length > 0) {
                        $.each(response.data.d, function(i, item) {
                            var row = `<tr>
                                <td align="center">${((currentPage - 1) * 100) + (i + 1)}</td>
                                <td align="center">${item.transDate}</td>
                                <td>${item.number}</td>
                                <td>${item.vendor.name}</td>
                                <td align="center">${item.status}</td>
                                <td align="right">${parseFloat(item.totalAmount).toLocaleString('id-ID')}</td>
                                <td align="center">
                                    <span class="action-link" onclick="clickDetail('${item.number}')">Detail</span>
                                    ${(item.status !== 'REJECTED' && item.status !== 'DRAFT') ? ` | <span class="action-link" onclick="clickSJ('${item.number}')">Surat Jalan</span>` : ''}
                                </td>
                            </tr>`;
                            $('#bodyTablePO').append(row);
                        });

                        if (response.pagination && response.pagination.more) {
                            $('#btnLoadMore').show();
                        } else {
                            $('#btnLoadMore').hide();
                        }
                    } else {
                        if (currentPage === 1) $('#bodyTablePO').html('<tr><td colspan="7" align="center">Data tidak ditemukan</td></tr>');
                        $('#btnLoadMore').hide();
                    }
                },
                error: function() {
                    $('#bodyTablePO').html('<tr><td colspan="7" align="center" style="color:red;">Terjadi kesalahan koneksi ke API</td></tr>');
                }
            });
        }

        function nextBatch() {
            currentPage++;
            var tgl1 = $('#txtTgl').val().replace(/-/g, '/');
            var tgl2 = $('#txtTgl2').val().replace(/-/g, '/');
            loadDataPO(tgl1, tgl2, $('#lstSupplier').val(), $('#lstStatus').val());
        }

        $(document).ready(function() {
            // Load data saat pertama kali buka halaman
            loadDataPO();

            // Select2 Vendor
            $('#lstSupplier').select2({
                placeholder: "--- Pilih Supplier ---",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '../Vendor/list.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { search: params.term, page: params.page || 1 }; },
                    processResults: function (data) {
                        return { 
                            results: $.map(data.data, function (obj) { return { id: obj.vendorNo, text: obj.name }; }),
                            pagination: { more: (data.pagination && data.pagination.more) }
                        };
                    }
                }
            });
        });

        function clickDetail(no) { window.location="printPO.php?nomor_po="+no; }
        function clickSJ(no) { 
            var win = window.open('newPrintSJ.php?nomor_po='+no, '_blank');
            win.focus();
        }
    </script>

    <style>
        .myTable th { background-color:#2E5E79; color:#FFF; padding:10px; }
        .myTable td { padding:8px; border-bottom:1px solid #ddd; font-size:12px; }
        .action-link { color: #2E5E79; cursor: pointer; text-decoration: underline; font-weight: bold; }
        #btnLoadMore { margin: 20px; padding: 8px 20px; cursor: pointer; display:none; background: #2E5E79; color: white; border: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="box round first fullpage" style="padding:20px;">
        <h2>Daftar PO (Murni Paging Accurate)</h2>
        <div class="block">
            <table class="form">
                <tr>
                    <td style="width:10%;">Status</td>
                    <td>
                        <select id="lstStatus" style="width:250px;">
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
                            <option value="<?= $supplier ?>" selected><?= ($supplier == "%") ? "All" : $supplier ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Tanggal (d-m-y)</td>
                    <td>
                        <input id="txtTgl" type="text" size="15" value="<?= $date ?>" readonly>
                        <a onclick="callCalendarDMY('txtTgl');" style="cursor:pointer;"><img src="../lib Calendar/cal.gif"></a>
                        -
                        <input id="txtTgl2" type="text" size="15" value="<?= $date2 ?>" readonly>
                        <a onclick="callCalendarDMY('txtTgl2');" style="cursor:pointer;"><img src="../lib Calendar/cal.gif"></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="button" value="View" onclick="clickView();" style="padding: 5px 20px; cursor:pointer;"></td>
                </tr>
            </table>

            <div style="margin-top:20px; overflow:auto; max-height:600px;">
                <table class="myTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>No.</th><th>Tanggal</th><th>No. PO</th><th>Vendor</th><th>Status</th><th>Total</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bodyTablePO">
                        </tbody>
                </table>
                <div align="center">
                    <button id="btnLoadMore" onclick="nextBatch();">Tampilkan 100 Data Berikutnya</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>