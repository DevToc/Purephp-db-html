<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');

$pdo_global->exec('update gbl_ss_info set gbl_ss_info.ss_successful = "N"');

/* define the wholesalers who ran a ss*/
$wholesalers_ss = array();
$sql = "SELECT gbl_ss_info.wholesaler_abbreviation from gbl_ss_info join gbl_wholesaler_info on gbl_ss_info.wholesaler_abbreviation = gbl_wholesaler_info.wholesaler_abbreviation where gbl_wholesaler_info.update_price_method = 'S' ORDER BY `wholesaler_abbreviation` DESC";
$result = $mysqli_global->query($sql);
while($row = $result->fetch_assoc()) {
	echo $row['wholesaler_abbreviation']."\n<br>";
  	$wholesalers_ss[] = $row['wholesaler_abbreviation'];
}

//Does these actions for every wholesaler//
foreach($wholesalers_ss as $wholesaler)
{
		// Remove Duplicates from Screen Scrapes //
$sql = "delete t1 FROM prd_ss_".$wholesaler."_products t1 JOIN prd_ss_".$wholesaler."_products t2 ON t2.sku = t1.sku AND t2.ID < t1.ID";
$result = $pdo_product->exec($sql);
echo $wholesaler." Removed Duplicates\n";//

		// Counts Number Of Sku From Screen Scrapes //
$sql = "SELECT COUNT(sku) FROM prd_ss_".$wholesaler."_products";
$result = $mysqli_product->query($sql);
while ($row = $result->fetch_assoc()) {
	$count = $row['COUNT(sku)'];
}
	echo $wholesaler."-".$count."\n";



//Confirm if ss was successful//
$sql = "update gbl_ss_info set gbl_ss_info.ss_successful = 'Y' where ((95/100)*gbl_ss_info.active_records) < $count and gbl_ss_info.wholesaler_abbreviation ='$wholesaler'";
$pdo_global->exec($sql);

}
//the end of actions to wholesalers//

//put a record in the errorlog to show if a ss did not run the night before//
		$row = array();
		$insert_product = $pdo_product->prepare('INSERT INTO `prd_errorlog` (`function_name`, `error`, `date`) VALUES (?, ?, ?);');
		$sql = "SELECT gbl_ss_info.wholesaler_name, gbl_ss_info.ss_successful from gbl_ss_info join gbl_wholesaler_info on gbl_ss_info.wholesaler_abbreviation = gbl_wholesaler_info.wholesaler_abbreviation where gbl_wholesaler_info.update_price_method = 'S' and gbl_ss_info.ss_successful = 'N' ";
    $result = $mysqli_global->query($sql);
    while ($row = $result->fetch_assoc()) {
        $wholesaler = $row['wholesaler_name'];
				$date = date('Y-m-d H:i:s');
				$error = "The screen scrape for ".$wholesaler." did not run";
				$function_name	= "Screen Scrape";
        $row = array($function_name, $date, $error);
				echo $error;
		 	 $insert_product->execute($row);
		}
