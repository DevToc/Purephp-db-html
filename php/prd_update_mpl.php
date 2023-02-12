<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


	$sql = "UPDATE `tmp_wpl_products` 
	join `prd_mpl_active_qtyprice` on prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku 
	set prd_mpl_active_qtyprice.qty = tmp_wpl_products.qty
	WHERE prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku 
	AND prd_mpl_active_qtyprice.wholesaler = tmp_wpl_products.wholesaler";
	$result = $mysqli_product->query($sql);

	$sql = "UPDATE `tmp_wpl_products` 
	JOIN `prd_mpl_active_qtyprice` on prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku 
	SET prd_mpl_active_qtyprice.qty = tmp_wpl_products.qty 
	WHERE prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku and prd_mpl_active_qtyprice.wholesaler = tmp_wpl_products.wholesaler 
	AND  prd_mpl_active_q	typrice.wholesaler_option = tmp_wpl_products.wholesaler_option";
	$result = $mysqli_product->query($sql);





/*


	UPDATE `tmp_wpl_products` 
	JOIN `prd_mpl_active_qtyprice` on prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku 
	SET prd_mpl_active_qtyprice.qty = tmp_wpl_products.qty 
	WHERE prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku 
	AND prd_mpl_active_qtyprice.wholesaler = tmp_wpl_products.wholesaler"

UPDATE `tmp_wpl_products` join `prd_mpl_active_qtyprice` on prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku set prd_mpl_active_qtyprice.qty = 500 WHERE prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku and prd_mpl_active_qtyprice.wholesaler = tmp_wpl_products.wholesaler

UPDATE `tmp_wpl_products` join `prd_mpl_active_qtyprice` on prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku set prd_mpl_active_qtyprice.qty = 500 WHERE prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku and prd_mpl_active_qtyprice.wholesaler = tmp_wpl_products.wholesaler and  prd_mpl_active_qtyprice.wholesaler_option = tmp_wpl_products.wholesaler_option
	
works for ag options
UPDATE `tmp_wpl_products` join `prd_mpl_active_qtyprice` on prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku set prd_mpl_active_qtyprice.qty = 500 WHERE prd_mpl_active_qtyprice.wholesaler_sku = tmp_wpl_products.wholesaler_sku

 */
?>
