
<ul class="nav main">
    <li class="ic-form-style" onclick="onclickMenuHeader('../StructureIndex/contentIndex.php');">
    	<a><span>Home</span></a>
    </li>
    <li class="ic-form-style">
    	<a><span>1. Transaksi</span></a>
        <ul>
        	<li onclick="onclickMenuHeader('../PO/DaftarPO.php');"><a onclick="">1.1. PURCHASE ORDER</a></li>
		    <li onclick="onclickMenuHeader('../PO/NewDaftarPO.php');"><a onclick="">1.1.2 ACCURATE PO</a></li>
        	<li onclick="onclickMenuHeader('../AP/DaftarAP.php');"><a>1.2. REKAP TAGIHAN</a></li>
        	<li onclick="onclickMenuHeader('../Product/formProduct.php');"><a>1.3. NEW PRODUCT</a></li>
            <!--<li onclick="onclickMenuHeader('../StructureIndex/OnProgress.php');"><a>1.4. CAMPAIGN</a></li>-->
        	<li onclick="onclickMenuHeader('../Campaign/DaftarCampaign.php');"><a>1.4. CAMPAIGN</a></li>
        	<li onclick="onclickMenuHeader('../PriceList/DaftarPriceList.php');"><a>1.5. PRICE LIST</a></li>
        	<li onclick="onclickMenuHeader('../SPB/DaftarSPB.php');"><a>1.6. SPB</a></li>
        	<li onclick="onclickMenuHeader('../KaryawanBlacklist/DaftarKaryawanBlacklist.php');"><a>1.7. KARYAWAN BLACKLIST</a></li>
        	<li onclick="onclickMenuHeader('../RTH1/DaftarRTH.php');"><a>1.8. RTH</a></li>
        	<li onclick="onclickMenuHeader('../OutPO/DaftarOutPO.php');"><a>1.9. OUTSTANDING PO</a></li>
		

        </ul>
    </li>
    <li class="ic-form-style">
    	<a><span>2. Configuration</span></a>
        <ul>
        	<li onclick="onclickMenuHeader('../Password/ChangePassword.php');"><a onclick="">2.1. CHANGE PASSWORD</a></li>
            <? if(strpos($_SESSION['menu_nuansa1'],"ADMIN")!=""){?>
            <li onclick="onclickMenuHeader('../News/DaftarNews.php');"><a onclick="">2.2. NEWS</a></li>
            <li onclick="onclickMenuHeader('../CP/DaftarCP.php');"><a onclick="">2.3. CONTACT PERSON</a></li>
            <li onclick="onclickMenuHeader('../GrupUser/DaftarGrupUser.php');"><a onclick="">2.4. GRUP USER</a></li>
            <? }?>
        </ul>
    </li>
</ul>