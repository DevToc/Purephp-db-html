<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');

$pdo_product->exec('TRUNCATE TABLE `tmp_wpl_products`;');
$insert_product = $pdo_product->prepare('INSERT INTO `tmp_wpl_products` (`wholesaler_sku`, `wholesaler_cost`, `qty`, `wholesaler`) VALUES (?, ?, ?, ?);');

/* define the wholesalers who ran a ss for qty and price*/
$wholesalers_qty = array("vl", "nc" , "ai" , "ab", "ap" , "fp" , "ta", "lk");
$wholesalers_noqty = array("wt" , "va" , "sc" , "ho" , "cm" , "cj" , "bm" ,"hh" );
//The following wholesalers need a custom insert into the temp_wpl_products: ag //
//// mj has not been placed//

//Define the wholesaers that provide next_avail_date//
$wholesalers_nextavail = array("");


/*for testing 1 ss*/
//	$wholesaler = "wholesaler_abbreviation here";
						//run for an individual wholesaler//
/*
$sql = "SELECT * FROM prd_ss_".$wholesaler."_products";
$result = $mysqli_product->query($sql);
while ($row = $result->fetch_assoc()) {

	if($row['stock']= "yes" or $row['stock']= "In Stock" or $row['stock']= "Y" ){
		$qty = 30;
	}else{
		$qty = 0;
	}
$sku = $row['sku'];
$price = $row['price'];
echo $wholesaler."-".$sku." inserted\n";
$row = array($sku, $price, $qty, $wholesaler);
$insert_product->execute($row);
}


 */

							//these wholesalers DO have qty in the ss//
foreach($wholesalers_qty as $wholesaler)
{
    $sql = "SELECT * FROM prd_ss_".$wholesaler."_products join global.gbl_ss_info where wholesaler_abbreviation = '".$wholesaler."' and global.gbl_ss_info.ss_successful = 'Y'" ;
    echo $sql;
    $result = $mysqli_product->query($sql);
    while ($row = $result->fetch_assoc()) {
          $qty = $row['qty'];
          $sku = $row['sku'];
          $price = $row['price'];
        echo $wholesaler."-".$sku." inserted\n";
        $row = array($sku, $price, $qty, $wholesaler);
        $insert_product->execute($row);
    }
}

							//these wholesalers dont have qty in the ss//
foreach($wholesalers_noqty as $wholesaler)
{
    $sql = "SELECT * FROM prd_ss_".$wholesaler."_products join global.gbl_ss_info where wholesaler_abbreviation = '".$wholesaler."' and global.gbl_ss_info.ss_successful = 'Y'" ;
    $result = $mysqli_product->query($sql);
    while ($row = $result->fetch_assoc()) {
        $qty = rand(20,30);
        $sku = $row['sku'];
        $price = $row['price'];
        echo $wholesaler."-".$sku." inserted\n";
        $row = array($sku, $price, $qty, $wholesaler);
        $insert_product->execute($row);
    }
}

$sql = "SELECT * FROM `gbl_ss_info` WHERE `wholesaler_abbreviation` = 'ab' and `ss_successful` = 'Y'";
$result = $mysqli_global->query($sql);
while ($row = $result->fetch_assoc()) {
      $ran = $row['ss_successful'];
    if($ran = "Y"){





                    	//custom insert for wholesaler ag//
        $insert_option = $pdo_product->prepare('INSERT INTO `tmp_wpl_products` (`wholesaler_sku`, `wholesaler_cost`, `qty`, `wholesaler`, `wholesaler_option`) VALUES (?, ?, ?, ?, ?);');

        //getting the options
        $sql = "select * from prd_ss_ag_products join prd_ss_ag_options on prd_ss_ag_products.sku = prd_ss_ag_options.sku";
        $result = $mysqli_product->query($sql);
        while ($row = $result->fetch_assoc()) {
        $sku = $row['sku'];
        $qty = $row['qty'];
        $option = $row['option_value'];
        $price = $row['price_change'];
        echo "ag-".$sku."inserted\n";
        $row = array($sku, $price, $qty, 'ag', $option);
        $insert_option->execute($row);
        }


        $sql = "SELECT prd_ss_ag_products.* FROM prd_ss_ag_products left outer join `prd_ss_ag_options` on prd_ss_ag_products.sku = prd_ss_ag_options.sku where prd_ss_ag_options.sku is NULL";
        $result = $mysqli_product->query($sql);
            while ($row = $result->fetch_assoc()) {
            $sku = $row['sku'];
            $qty = $row['qty'];
            $option = ""/*$row['option_value']*/;
            $price = $row['price'];
            echo "ag-".$sku."inserted\n";
            $row = array($sku, $price, $qty, 'ag', $option);
            $insert_option->execute($row);
        }
    }
}

?>
