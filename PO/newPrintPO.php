<?php
/**
 * PRINT PURCHASE ORDER - 100% IDENTICAL TO newPrintSJ.php
 * Sesuai Instruksi: Barcode Dihapus, Site dari Warehouse, Tambah Kolom Harga & Total.
 */
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$nomor_po = isset($_GET['nomor_po']) ? $_GET['nomor_po'] : '';

$dataPO = array();
$dataVendor = array();
$dataBranch = array();

if ($nomor_po) {
    $poRes = $api->getPurchaseOrderDetail($nomor_po);
    if ($poRes['success'] && isset($poRes['data']['d'])) {
        $dataPO = $poRes['data']['d'];
        
        $bId = isset($dataPO['branchId']) ? $dataPO['branchId'] : null;
        if ($bId) {
            $bRes = $api->getBranchDetail($bId);
            if (isset($bRes['data']['d'])) {
                $dataBranch = $bRes['data']['d'];
            }
        }

        $vNo = isset($dataPO['vendor']['vendorNo']) ? $dataPO['vendor']['vendorNo'] : null;
        if ($vNo) {
            $vRes = $api->getVendorDetail(null, $vNo);
            if (isset($vRes['data']['d'])) {
                $dataVendor = $vRes['data']['d'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PURCHASE ORDER - <?php echo isset($dataPO['number']) ? $dataPO['number'] : ''; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; -webkit-print-color-adjust: exact; }
        table { border-collapse: collapse; width: 100%; }
        .table-item td, .table-item th { border: 0; padding: 6px 4px; }
        .line-bottom { border-bottom: 1px solid black; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td rowspan="4" width="220"><img src="images/Nuansa.jpg" width="200" height="75" /></td>
            <td rowspan="2" style="font-weight:bold; font-size:18px;">PURCHASE ORDER</td>
            <td width="100">Tanggal PO :</td>
            <td width="100">Tanggal Kirim :</td>
            <td width="100">Tanggal Exp :</td>
        </tr>
        <tr>
            <td valign="top"><?php echo isset($dataPO['transDateView']) ? $dataPO['transDateView'] : '-'; ?></td>
            <td valign="top"><?php echo isset($dataPO['shipDateView']) ? $dataPO['shipDateView'] : '-'; ?></td>
            <td>
                <?php 
                if (isset($dataPO['transDate']) && isset($dataPO['autoCloseRange'])) {
                    $poDate = str_replace('/', '-', $dataPO['transDate']);
                    $days = intval($dataPO['autoCloseRange']);
                    echo date('d M Y', strtotime($poDate . " + " . $days . " days"));
                    echo "<br>(" . $days . " Hari)";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td rowspan="2">RE-PRINT</td>
            <td rowspan="2" valign="middle"></td> <td>NO. PO :</td>
            <td>T.O.P :</td>
        </tr>
        <tr>
            <td><strong><?php echo isset($dataPO['number']) ? $dataPO['number'] : '-'; ?></strong></td>
            <td><?php echo isset($dataPO['paymentTerm']['name']) ? $dataPO['paymentTerm']['name'] : '-'; ?></td>
        </tr>
    </table>

    <table style="border: 1px solid black; margin-top:10px;">
        <tr>
            <td width="50%" valign="top" style="border-right: 2px solid black; padding:8px;">
                <table width="100%">
                    <tr><td width="30%">SUPPLIER:</td><td><strong><?php echo isset($dataPO['vendor']['name']) ? $dataPO['vendor']['name'] : '-'; ?></strong></td></tr>
                    <tr>
                        <td></td>
                        <td>
                            <?php 
                                $street = isset($dataVendor['billStreet']) ? trim($dataVendor['billStreet']) : '';
                                $city = isset($dataVendor['billCity']) ? trim($dataVendor['billCity']) : '';
                                $province = isset($dataVendor['billProvince']) ? trim($dataVendor['billProvince']) : '';
                                $output = $street;
                                if ($city !== '') $output .= ($output !== '' ? ', ' : '') . $city;
                                if ($province !== '') $output .= ($output !== '' ? ' - ' : '') . $province;
                                echo ($output !== '') ? $output : '-';
                            ?>
                        </td>
                    </tr>
                    <tr><td>&nbsp;</td><td></td></tr>
                    <tr>
                        <td>TELEPON:</td>
                        <td><?php echo isset($dataVendor['mobilePhone']) ? $dataVendor['mobilePhone'] : '-'; ?></td>
                    </tr>
                    <tr>
                        <td>EMAIL:</td>
                        <td><?php echo isset($dataVendor['email']) ? $dataVendor['email'] : '-'; ?></td>
                    </tr>
                    <tr>
                        <td>CONTACT:</td>
                        <td><?php echo isset($dataVendor['detailContact'][0]['name']) ? $dataVendor['detailContact'][0]['name'] : '-'; ?></td>
                    </tr>
                </table>
            </td>
            <td width="50%" valign="top" style="padding:8px;">
                <table width="100%">
                    <tr>
                        <td width="25%">TOKO :</td>
                        <td>
                            <strong>
                                <?php 
                                $street_b = isset($dataBranch['street']) ? trim($dataBranch['street']) : '';
                                $name_b   = isset($dataBranch['name']) ? trim($dataBranch['name']) : '';
                                if ($street_b !== '' && $name_b !== '') echo $street_b . ' - ' . $name_b;
                                elseif ($street_b !== '' || $name_b !== '') echo $street_b . $name_b;
                                else echo '-';
                                ?>
                            </strong>
                        </td>
                    </tr>
                    <tr><td>SHIP TO :</td><td><br><?php echo isset($dataPO['toAddress']) ? $dataPO['toAddress'] : '-'; ?></td></tr>
                    <?php if (isset($dataPO['shipment']['name'])) : ?>
                    <tr>
                        <td><br>EKSPEDISI :</td>
                        <td><br><strong><?php echo $dataPO['shipment']['name']; ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>

    <table class="table-item" style="margin-top:10px;">
        <tr style="font-weight:bold; text-align:center;">
            <th width="3%" class="line-bottom">NO.</th>
            <th width="25%" class="line-bottom" align="left">NAMA BARANG</th>
            <th width="10%" class="line-bottom">SITE</th>
            <th width="5%" class="line-bottom">QTY</th>
            <th width="12%" class="line-bottom">HARGA</th>
            <th width="15%" class="line-bottom">DISKON</th>
            <th width="15%" class="line-bottom">JUMLAH</th>
        </tr>
        <?php 
        $totalQty = 0;
        if (isset($dataPO['detailItem'])) {
            foreach ($dataPO['detailItem'] as $idx => $item) {
                $totalQty += $item['quantity'];
                $discPercent = (isset($item['lastItemDiscPercent']) && $item['lastItemDiscPercent'] != '') ? "(" . trim($item['lastItemDiscPercent']) . "%) " : "";
                $discCash = (isset($item['lastItemCashDiscount']) && $item['lastItemCashDiscount'] != 0) ? "(" . number_format($item['lastItemCashDiscount'], 0, ',', '.') . ")" : "";
                $displayDiscount = trim($discPercent . $discCash);
                ?>
                <tr>
                    <td align="center"><?php echo $idx + 1; ?></td>
                    <td><?php echo $item['detailName']; ?></td>
                    <td align="center"><?php echo isset($item['warehouse']['name']) ? $item['warehouse']['name'] : '-'; ?></td>
                    <td align="center"><?php echo number_format($item['quantity'], 0); ?></td>
                    <td align="right"><?php echo number_format($item['unitPrice'], 2, ',', '.'); ?></td>
                    <td align="center"><?php echo ($displayDiscount != '') ? $displayDiscount : ''; ?></td>
                    <td align="right"><?php echo number_format($item['totalPrice'], 2, ',', '.'); ?></td>
                </tr>
                <?php
            }
        }
        ?>
        <tr>
            <td colspan="3" class="line-bottom"></td>
            <td align="center" style="font-weight:bold;" class="line-bottom"><?php echo number_format($totalQty, 0); ?></td>
            <td colspan="2" class="line-bottom" align="right" style="font-weight:bold;">SUB TOTAL:</td>
            <td class="line-bottom" align="right" style="font-weight:bold;"><?php echo number_format($dataPO['subTotal'], 2, ',', '.'); ?></td>
        </tr>
        <?php if($dataPO['tax1Amount'] > 0): ?>
        <tr>
            <td colspan="6" align="right" style="font-weight:bold;">PPN (<?php echo $dataPO['tax1Rate']; ?>%):</td>
            <td align="right" style="font-weight:bold;"><?php echo number_format($dataPO['tax1Amount'], 2, ',', '.'); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td colspan="6" align="right" style="font-weight:bold; font-size:12px;">TOTAL AKHIR:</td>
            <td align="right" style="font-weight:bold; font-size:12px; border-bottom: 2px solid black;"><?php echo number_format($dataPO['totalAmount'], 2, ',', '.'); ?></td>
        </tr>
    </table>
    
    <table style="margin-top:15px;">
        <tr><td colspan="4">KET: <?php echo isset($dataPO['description']) ? $dataPO['description'] : '-'; ?></td></tr>
        <tr style="text-align:center;">
            <td width="25%" style="padding-top:20px;">Mengetahui</td>
            <td width="25%">&nbsp;</td>
            <td width="25%" style="padding-top:20px;">DATETIME: <?php echo date("d M Y H:i"); ?></td>
            <td width="25%" style="padding-top:20px;">
                USER: 
                <?php 
                    $createdBy = isset($dataPO['createdBy']) ? $dataPO['createdBy'] : '-';
                    $printUser = isset($dataPO['printUserName']) ? $dataPO['printUserName'] : '';
                    if ($printUser == '' || $printUser == 'Belum cetak/email') echo $createdBy;
                    else echo $printUser . ' / ' . $createdBy;
                ?>
            </td>
        </tr>
        <tr style="text-align:center; font-weight:bold;">
            <td>Buyer</td><td>Admin Buyer</td><td colspan="2"></td>
        </tr>
        <tr style="text-align:center;">
            <td style="padding-top:40px;">(__________________)</td>
            <td style="padding-top:40px;">(__________________)</td>
            <td colspan="2"></td>
        </tr>
    </table>

    <div style="border: 2px solid black; padding:8px; margin-top:15px; font-weight:bold;">
        PERHATIAN: Setelah barang dikirim, Supplier wajib mengambil SPB (Surat Penerimaan Barang), KECUALI Supplier Luar Kota
    </div>
</body>
</html>