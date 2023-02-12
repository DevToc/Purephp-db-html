<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




global $platform;
global $mysqli_global;
global $mysqli_product;

$mdr_sku = $row['mdr_sku'];
//  echo $platform;
$sql = "SELECT * FROM `gbl_platforms` where platform = '.$platform.'";
$result = $mysqli_global->query($sql);
while ($row = $result->fetch_assoc()) {
$country = $row['country'];
$platform_fee = $row['platform_fee'];
$platform_profit = $row['platform_profit'];
}
//    global $country;
  global $platform_fee;
  global $platform_profit;
      switch($country) {
          case "US":
              $sql = "SELECT * FROM `gbl_xe` where cur_exch = 'CAD_USD'";
              $result = $mysqli_global->query($sql);
                  while ($row = $result->fetch_assoc()) {
                  $exchange_rate = $row['cur_rate'];
                  }
                  break;
          case "AU":
              $sql = "SELECT * FROM `gbl_xe` where cur_exch = 'CAD_AUD'";
              $result = $mysqli_global->query($sql);
                  while ($row = $result->fetch_assoc()) {
                  $exchange_rate = $row['cur_rate'];
                  }
                  break;
          default:
              $exchange_rate = 1;
          }

//  - retrieve shipping cost for sku
switch($country) {
  case "US":
      $sql = "SELECT * FROM `prd_product_size` where mdr_sku = '$mdr_sku'";
      $result = $mysqli_global->query($sql);
          while ($row = $result->fetch_assoc()) {
          $shipping = $row['shipping_cost_com'];
          }
          break;
  case "CA":
      $sql = "SELECT * FROM `prd_product_size` where mdr_sku = '$mdr_sku'";
      $result = $mysqli_global->query($sql);
          while ($row = $result->fetch_assoc()) {
            $shipping = $row['shipping_cost_ca'];
          }
          break;
  default:
            $shipping = 25;
}

  /// retrieve sold as
  if(isset($_GET['btn-plat_option'])) {
  $platform = $_GET['platform'];
  switch($platform) {
      case "WayfairCOM":
      $sell_on = "sell_as_wayfair";
            break;
      case "WayfairCA":
      $sell_on = "sell_as_wayfair";
            break;
      case "Ebay_DSCB":
      $sell_on = "sell_as_ebay";
            break;
      case "AmazonCA":
      $sell_on = "sell_as_amazon";
            break;
      case "AmazonCOM":
      $sell_on = "sell_as_amazon";
            break;
      case "AmazonAU":
      $sell_on = "sell_as_amazon";
            break;
      case "WalmartCA":
      $sell_on = "sell_as_walmart";
            break;
      case "WalmartCOM":
      $sell_on = "sell_as_walmart";
            break;
      case "Etsy":
      //    $sell_on = "sell_as_amazon";
            break;
      case "Unbeatable":
      $sell_on = "sell_as_unbeatable";
            break;
      case "Ebay_MDR":
      $sell_on = "sell_as_ebay";
            break;
      case "OverstockCA":
      $sell_on = "sell_as_overstock";
            break;
      case "OverstockCOM":
      $sell_on = "sell_as_overstock";
            break;
      case "Ebay_MRMJS":
      $sell_on = "sell_as_ebay";
            break;
          }
        }
        global $sell_on;
//          $sell_on = "'".$sell_on."'";
  $sql = "SELECT mdr_sku,'$sell_on' FROM `prd_mpl_active_soldas` where mdr_sku = '$mdr_sku'";
  $result = $mysqli_product->query($sql);
      while ($row = $result->fetch_assoc()) {
        $sell_as = $row[$sell_on];
      }
      global $sell_as;
  // retrieve and calculate cost
    $cost = $row['wholesaler_cost'] * $sell_as * 1.13;

    $sell_price = ceil(($cost+$shipping)/((1-$platform_profit)-(1*$platform_fee))) * $exchange_rate;


  // ***Note when get the exchange rate if getting for a CAN then exchange rate will be 1*/



function price_calc(){

}
?>
