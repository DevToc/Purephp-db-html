<?php
//Connect to databases//
include_once('/var/www/html/global/php/gbl_connect.php');
//determine location of file//
$filename = 'Amazon_ca.txt';
$path_to_file='/var/www/html/product/txt/';

//begin the information fields//
header('Content-type: product/txt');
header('Content-Disposition: attachment; filename='.$filename);
$out = '';
$fields = '';
//match these fields with the fields in the while//
$fields = array('sku','price','Quantity');

//obtain the data to put into a file//
$sql = "select * from prd_mpl_active_qtyprice";
$result = $mysqli_product->query($sql);

	foreach ($fields as $field)
	{
	$out .= '"'.$field.'",';
	}
	$out .="\n";

//match thesefields with the $fields
	while ($row = $result->fetch_assoc()) {
	$qty = $row['wholesaler_sku'];
	$sku = $row['wholesaler_cost'];
	$price = $row['qty'];
	$row = array($sku, $price, $qty);

	foreach($row as $value)
	{
	$out .='"'.$value.'",';
	}
	$out .="\n";
	}
//open and download the file created//
$f = fopen ($path_to_file.$filename,'w');
fputs($f, $out);
fclose($f);
readfile($path_to_file.$filename);

?>
