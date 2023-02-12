<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');



// Get information from one database//

$sql = "SELECT sku,price FROM ss_wt_products";
$result = $mysqli_product->query($sql);


//select the information and define it as a value// 
if ($result->num_rows > 0) {
    //output data of each row
    while($row = $result->fetch_assoc()) {
	  //  echo "sku: " . $row["sku"]. " - Name: " . $row["price"]. " " . $row["price"];
	    $price = $row["price"];
    }
} else {
    echo "0 results";
}


//update a database with values received//

$update  = "update ss_wt_products_ord SET ";
$update .= "`price` 	= ".$price." ";
//echo $update;
$result = $mysqli_order->query($update);


?>
