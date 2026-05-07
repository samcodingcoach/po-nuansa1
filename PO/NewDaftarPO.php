<?php
session_start();
include('../StructureIndex/head-library.php');
include('../Connection/validateSession.php');

// Memanggil class AccurateAPI
require_once("../classes/AccurateAPI.php");
$api = new AccurateAPI();

// 1. Konfigurasi Session & Filter (Identik dengan DaftarPO.php)
if($_SESSION['restricted_nuansa1']=="%")
{
    $restricted="%";
}
else
{
    $restricted=$_SESSION['restricted_nuansa1'];
}

if(!isset($_REQUEST['status']))
{
    $status="%";
}
else
{
    $status=$_REQUEST['status'];
}

if(!isset($_REQUEST['supplier'])&&$_SESSION['restricted_nuansa1']=="%")
{
    $supplier="%";
}
else if(!isset($_REQUEST['supplier'])&&$_SESSION['restricted_nuansa1']!="%")
{
    $supplier=trim($_SESSION['restricted_nuansa1']);
}
else
{
    $supplier=$_REQUEST['supplier'];
}

// 2. Manajemen Tanggal
if(!isset($_REQUEST['tanggal']))
{
    $date=date("d-m-Y", mktime(date("H"),date("i"),date("s"),date("m")-1,date("d"),date("Y")));
}
else
{
    $date=$_REQUEST['tanggal'];
}

if(!isset($_REQUEST['tanggal2']))
{
    $date2=date("d-m-Y");
}
else
{
    $date2=$_REQUEST['tanggal2'];
}

// ======================================================================
// INTEGRASI ACCURATE API
// ======================================================================

// Konversi format d-m-Y ke d/m/Y untuk Accurate API
$api_start = str_replace('-', '/', $date);
$api_end = str_replace('-', '/', $date2);

// Susun Parameter API
$params = array(
    'filter.transDate.op'     => 'BETWEEN',
    'filter.transDate.val[0]' => $api_start,
    'filter.transDate.val[1]' => $api_end,
    'sp.pageSize'             => 500
);

// Filter Berdasarkan vendorNo jika dipilih
if ($supplier != "%") {
    $params['filter.vendorNo'] = $supplier;
}

// Ambil Data Purchase Order[cite: 6]
$resPO = $api->getPurchaseOrderList($params);
$poData = ($resPO['success'] && isset($resPO['data']['d'])) ? $resPO['data']['d'] : array();

// Ambil Data Vendor untuk Dropdown[cite: 6]
$resVendor = $api->getVendorList(array('sp.pageSize' => 1000));
$vendors = ($resVendor['success'] && isset($resVendor['data']['d'])) ? $resVendor['data']['d'] : array();

?>

    <script language="javascript" src="../lib Calendar/calendar.js"></script>
    <script language="javascript" src="../lib Calendar/datetimepicker.js"></script>
    <script src="../js/JS-GlobalFunction.js" type="text/javascript"></script>
    <script src="../js/JS-GlobalFunction1.js" type="text/javascript"></script>
    <script type="text/javascript">
        function clickView()
        {
            window.location="NewDaftarPO.php?status="+document.getElementById("lstStatus").value+"&supplier="+document.getElementById("lstSupplier").value+"&tanggal="+document.getElementById("txtTgl").value+"&tanggal2="+document.getElementById("txtTgl2").value;
        }
        
        function clickDetail(PONumber)
        {
            window.location="printPO.php?nomor_po="+PONumber;
        }
        
        // REVISI: Mengarah ke newPrintSJ.php dan memicu window print
        function clickSJ(PONumber)
        {
            var printWindow = window.open('newPrintSJ.php?nomor_po='+PONumber, 'printSJ', 'menubar=no,status=no,scrollbars=yes,width=900,height=600');
            
            // Pemicu print otomatis saat window selesai load
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
            };
        }
        
        function clickCancelledPO()
        {
            window.open('formCancelledPO.php','popup_form','menubar=no,status=no,top=100%,left=100');
        }
    </script>
    
    <style type="text/css">
        #progress-bar { width: 400px; }
        .myTable th { text-align: center; vertical-align: middle; }
    </style>
    
    <div class="box round first fullpage" style="padding:20px;">
        <h2>
            Daftar PO (API System)
            <? if($_SESSION['restricted_nuansa1']=="%") { ?>
                <a style="font-size:9px; cursor:pointer;" onclick="clickCancelledPO();">(Cancelled PO)</a>
            <? } ?>
        </h2>
        <div class="block ">
            <form>
                <table class="form">
                    <tr>
                        <td style="width:10%;" class="col1"><label>Status</label></td>
                        <td class="col2">
                            <select name="lstStatus" id="lstStatus">
                                <option <? if($status==1){?> selected="selected"<? }?> value="1">New</option>
                                <option <? if($status==2){?> selected="selected"<? }?> value="2">Released</option>
                                <option <? if($status==3){?> selected="selected"<? }?> value="3">Change Order</option>
                                <option <? if($status==4){?> selected="selected"<? }?> value="4">Received</option>
                                <option <? if($status==5){?> selected="selected"<? }?> value="5">Closed</option>
                                <option <? if($status==6){?> selected="selected"<? }?> value="6">Cancelled</option>
                                <option <? if($status=="%"){?> selected="selected"<? }?> value="%">All</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:5%;" class="col1"><label>Supplier</label></td>
                        <td class="col2">
                            <select name="lstSupplier" id="lstSupplier" <? if($_SESSION['restricted_nuansa1']!="%"){?> disabled="disabled"<? }?>>
                            <? if($_SESSION['restricted_nuansa1']=="%") { ?>
                                <option <? if($supplier=="%"){?> selected="selected"<? }?> value="%">All</option>
                            <? } ?>
                            <? foreach($vendors as $vRow) { ?>
                                <option <? if($supplier==$vRow['vendorNo']){?> selected="selected"<? }?> value="<? echo $vRow['vendorNo'];?>"><? echo $vRow['name'];?></option>
                            <? } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:5%;" class="col1"><label>Tanggal (d-m-y)</label></td>
                        <td class="col2">
                            <input id="txtTgl" type="text" size="20" name="txtTgl" value="<? echo $date;?>" readonly>
                            <a onclick="callCalendarDMY('txtTgl');" style="cursor:pointer;" ><img width="16" height="16" border="0" src="../lib Calendar/cal.gif"></a>
                             - 
                            <input id="txtTgl2" type="text" size="20" name="txtTgl2" value="<? echo $date2;?>" readonly>
                            <a onclick="callCalendarDMY('txtTgl2');" style="cursor:pointer;" ><img width="16" height="16" border="0" src="../lib Calendar/cal.gif"></a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="button" value="View" name="btnView" id="btnView" onclick="clickView();">
                        </td>
                    </tr>
                </table>

                <div style="overflow:auto; max-height:450px;">
                <table class="myTable" style="width:100%;">
                    <thead height="23" style="background-color:#2E5E79; color:#FFF;">
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
                    <tbody id="DetailBarang">
                    <?
                    $no=0;
                    foreach($poData as $poRow)
                    {
                        // Filter Status Mapping manual karena API mengembalikan String[cite: 6]
                        if($status != "%") {
                            $stMap = array(1=>'New', 2=>'Released', 3=>'Change Order', 4=>'Received', 5=>'Closed', 6=>'Cancelled');
                            if($poRow['statusName'] != $stMap[$status]) continue;
                        }

                        $no+=1;
                    ?>
                        <tr>
                            <td style="text-align:center;"><? echo $no;?></td>
                            <td style="text-align:center;"><? echo $poRow['transDate'];?></td>
                            <td><? echo $poRow['number'];?></td>
                            <td><? echo $poRow['vendor']['name'];?></td>
                            <td style="text-align:center;"><? echo $poRow['statusName'];?></td>
                            <td style="text-align:right;">
                                <? echo number_format($poRow['totalAmount'], 2, ",", ".");?>
                            </td>
                            <td style="text-align:center;">
                                <a style="cursor:pointer;" onclick="clickDetail('<? echo $poRow['number'];?>');">Detail</a>
                                <?
                                if($poRow['statusName'] != "Cancelled" && $poRow['statusName'] != "Batal")
                                {
                                ?>
                                 || 
                                <a style="cursor:pointer;" onclick="clickSJ('<? echo $poRow['number'];?>');">Surat Jalan</a>
                                <?
                                }
                                ?>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                    </tbody>
                </table>
                </div>
            </form>
        </div>
    </div>