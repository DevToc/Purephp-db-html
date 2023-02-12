<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');

$bo_date = '03/31/2023';

/* define the wholesalers who ran a ss*/
$wholesalers_ss = array();
$sql = "SELECT gbl_ss_info.wholesaler_abbreviation from gbl_ss_info join gbl_wholesaler_info on gbl_ss_info.wholesaler_abbreviation = gbl_wholesaler_info.wholesaler_abbreviation where gbl_wholesaler_info.update_price_method = 'S' ORDER BY `wholesaler_abbreviation` DESC";
$result = $mysqli_global->query($sql);
while($row = $result->fetch_assoc()) {
	echo $row['wholesaler_abbreviation']."\n<br>";
  	$wholesalers_ss[] = $row['wholesaler_abbreviation'];
}



/* Wholesaler AB */
//cleaning qty//
	$update_Repo = "UPDATE prd_ss_ab_products set qty = 0  where qty <> 'In Stock'";
	$run_query= $mysqli_product->query($update_Repo);
	$update_Repo = "UPDATE prd_ss_ab_products set qty = 30 where qty = 'In Stock'";
	$run_query= $mysqli_product->query($update_Repo);
	$update_Repo = "UPDATE prd_ss_ab_products set qty = 3 where qty like '%Limited%'";
	$run_query= $mysqli_product->query($update_Repo);
//cleaning pricing//
	$update_AB = "UPDATE prd_ss_ab_products set wholesaler_cost = prev_price where prev_price <> ''";
	$run_query = $mysqli_product->query($update_AB);
	echo "Finished Abbott cleaning\n";
/* Wholesaler AG */
/*update the options on prd_ss_ag_options*/
		$update_ag = "UPDATE prd_ss_ag_options SET price_change = replace(price_change,'$', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'\"','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = '16x16' where option_value like '%�� %'";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'â€�','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = 'coveronly' where option_value like '%Cover Only%'";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = '8x11ftOval' where option_value like '%Ft.Oval%'";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = 'xx' where option_value like '%img%'";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'70 Round','70x70')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'60 Round','60x60')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' Oval','Oval')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' Sq.','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' SQ.','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' Rect.','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' (No Border)','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Ribbed ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Tassels - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Fused ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Chindi ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Small - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Large - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Regular - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Jumbo - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Mini - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'For 2 Sliced','2Slice')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'For 4 Sliced','4Slice')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' Zip Cover with Insert','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Rocker Set (Chair Pad+Back)','RockerSet')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Bath Towel - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Hand Towel + ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Face Cloth - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Bath Sheet - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Size: ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' Runner','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Placemats ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Mini ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Medium - ','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,' (TxBxS)','')";
		$run_query = $mysqli_product->query($update_AG);
		$update_AG = "UPDATE prd_ss_ag_options set option_value = replace(option_value,'Oval','oval')";
		$run_query = $mysqli_product->query($update_AG);
//cleaning products table//
//cleaning price//
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'$', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/Set', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/set', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/Pair', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/Ea.', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/EA.', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'Ea.', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/DZ.', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/Dz.', '')";
		$run_query= $mysqli_product->query($update_ag);
		$update_ag = "UPDATE prd_ss_ag_products SET price = replace(price,'/Dz,', '')";
		$run_query= $mysqli_product->query($update_ag);

//cleaning qty//
		$update_Repo = "UPDATE prd_ss_ag_products set qty = 0 where qty <> 'In Stock'";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ag_products set qty = 27 where qty = 'In Stock'";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished Apex Group Cleaning\n";

/* Wholesaler AI */
//price scraped clean//
//cleaning qty//
		$update_Repo = "UPDATE prd_ss_ai_products set qty = 0 where qty >= 0 and qty < 11";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ai_products left outer join prd_mpl_discontinued on prd_ss_ai_products.sku = prd_mpl_discontinued.wholesaler_sku set prd_ss_ai_products.qty = 0 where prd_ss_ai_products.sku is NULL";
		$run_query= $mysqli_product->query($update_Repo);
echo "Finished Action Imports Cleaning\n";


/* Wholesaler AP */
//price scraped clean//
//cleaning qty//
		$update_Repo = "UPDATE prd_ss_ap_products set qty = 30 where prd_ss_ap_products.qty = 'Y'";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ap_products set qty = 0 where prd_ss_ap_products.qty = 'Y' and out_of_stock_date <> ''";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ap_products set qty = 0 where prd_ss_ap_products.qty = 'N' and out_of_stock_date = ''";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ap_products set qty = 0 where prd_ss_ap_products.qty = 'N' and out_of_stock_date <> ''";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ap_products left outer join prd_mpl_discontinued on prd_ss_ap_products.sku = prd_mpl_discontinued.wholesaler_sku set prd_ss_ap_products.qty = 0 where prd_ss_ap_products.sku is NULL";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished Apex\n";
/* Wholesaler BM */
//price scraped clean//
//qty from file//
echo "Finished Boxman Cleaning\n";

/* Wholesaler CJ from shopzio*/
//price scraped clean//
//qty not provided//
echo "Finished CJ Cleaning\n";

/* Wholesaler CM*/
$update_ag = "UPDATE prd_ss_cm_products SET price = replace(price,',', '')";
$run_query= $mysqli_product->query($update_ag);


/* Wholesaler FP */
//price scraped clean//
//cleaning qty//
		$update_Repo = "UPDATE prd_ss_fp_products set qty = 0 where qty <> 'In stock'";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_fp_products set qty = 30 where qty = 'In stock'";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished Forpost\n";

/* Wholesaler HH*/
//price scraped clean//
//cleaning qty//
echo "Finished HH Cleaning\n";

/* Wholesaler HO */
//price scraped clean//
//qty not provided//
echo "Finished Homestead cleaning\n";
/* Wholesaler IV */
//price/qty provided in files//



/* Wholesaler LK */
//price scraped clean//
//updateing qty//
		$update_Repo = "UPDATE prd_ss_lk_products set qty = 0 where qty not like '%Available%'";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_lk_products set qty = 5 where qty like '%Available%'";
		$run_query= $mysqli_product->query($update_Repo);

		echo "Finished LKQ cleaning\n";


/* Wholesaler NC */
//price scraped clean//
//cleaning qty//
		$update_Repo = "UPDATE prd_ss_nc_products set qty = 0 where qty < 6";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished Northwood Cleaning\n";

/* Wholesaler SC */
//qty not provided//
//updateing price//
		$update_SC = "UPDATE prd_ss_sc_products SET price = replace(price,'$', '')";
		$run_query= $mysqli_product->query($update_SC);
		$update_SC = "UPDATE prd_ss_sc_products SET price = replace(price,',', '')";
		$run_query= $mysqli_product->query($update_SC);
		echo "Finished SCR China\n";
/* Wholesaler TA */
//updateing price//
		$update_TA = "UPDATE `prd_ss_ta_products` SET price = replace(price,'$','')";
		$run_query= $mysqli_product->query($update_TA);
//updateing qty//
		$update_Repo = "UPDATE prd_ss_ta_products set qty = 0 where qty like '%out of stock%'";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_ta_products set qty = 5 where qty not like '%out of stock%'";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished Tiger Auto Cleaning\n";


/* Wholesaler VA */
//updateing qty//

	$update_Repo = "UPDATE prd_ss_va_products set qty = 0, next_avail_date =  '".$next_avail_date."' where qty < 4";
	$run_query= $mysqli_product->query($update_Repo);
	echo "Finished Viana Cleaning\n";

/* Wholesaler VL */
		$update_VL = "UPDATE `prd_ss_vl_products` SET price = replace(price,'$','')";
		$run_query= $mysqli_product->query($update_VL);
		$update_VL = "UPDATE `prd_ss_vl_products` SET item = replace(item, ',', ' - ' ), category = replace(category, ',', ' - ' ),subcategory = replace(subcategory, ',', ' - ' ), price = replace(price, ',', '')";
		$run_query= $mysqli_product->query($update_VL);
		$update_Repo = "UPDATE prd_ss_vl_products set qty = 0 where prd_ss_vl_products.qty >= 0 and prd_ss_vl_products.qty < 11";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_vl_products set qty = 0 where prd_ss_vl_products.description like '%2022%'";
		$run_query= $mysqli_product->query($update_Repo);
		$update_Repo = "UPDATE prd_ss_vl_products left outer join prd_mpl_discontinued on prd_ss_vl_products.sku = prd_mpl_discontinued.wholesaler_sku set prd_ss_vl_products.qty = 0 where prd_ss_vl_products.sku is NULL";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished V&L Imports\n";
/* Wholesaler WT */
		$update_WT = "UPDATE prd_ss_wt_products SET price = replace(price,'$', '')";
		$run_query= $mysqli_product->query($update_WT);
		$update_WT = "UPDATE prd_ss_wt_products SET description = replace(description, ',', ' -- '), name = replace(name,',', ' -- '),image = replace(image,',', ' -- '), sku = replace(sku,',', ' -- '), price = replace(price,' / PC', '')";
		$run_query= $mysqli_product->query($update_WT);
		$update_WT = "UPDATE prd_ss_wt_products SET price = replace(price,' / Set', '')";
		$run_query= $mysqli_product->query($update_WT);
		$update_Repo = "UPDATE prd_ss_wt_products left outer join prd_mpl_discontinued on prd_ss_vl_products.sku = prd_mpl_discontinued.wholesaler_sku set prd_ss_vl_products.qty = 0 where prd_ss_vl_products.sku is NULL";
		$run_query= $mysqli_product->query($update_Repo);
		echo "Finished W2 Trading Cleaning\n";



//the end of actions to wholesalers//




?>
