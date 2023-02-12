<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


$sql = "delete FROM tmp_wpl_products where wholesaler_cost <= 0";

$result = $pdo_product->exec($sql);

$sql = "delete FROM tmp_wpl_products join prd_mpl_blacklist where tmp_wpl_products.wholesaler_sku = prd_mpl_blacklist.wholesaler_sku";
$result = $pdo_product->exec($sql);


$sql = "delete FROM tmp_wpl_products join prd_mpl_new_products where tmp_wpl_products.wholesaler_sku = prd_mpl_new_products.wholesaler_sku";
$result = $pdo_product->exec($sql);





/*
$pdo_product->exec('delete FROM tmp_wpl_products where wholesaler_cost <= 0');
$pdo_product->exec('delete FROM tmp_wpl_products join prd_mpl_blacklist where tmp_wpl_products.wholesaler_sku = prd_mpl_blacklist.wholesaler_sku');
$pdo_product->exec('delete FROM tmp_wpl_products join prd_mpl_discontinued where tmp_wpl_products.wholesaler_sku = prd_mpl_discontinued.wholesaler_sku');
$pdo_product->exec('delete FROM tmp_wpl_products join prd_mpl_backorder where tmp_wpl_products.wholesaler_sku = prd_mpl_backorder.wholesaler_sku');
$pdo_product->exec('delete FROM tmp_wpl_products join prd_mpl_new_products where tmp_wpl_products.wholesaler_sku = prd_mpl_new_products.wholesaler_sku');
 */
?>
