<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


function export() {

if(isset($_POST['button2'])) {
echo $_POST['platform'];
echo"<br>";
echo $_POST['platb'];
 $file_name= $_FILES['file']['name'];
if(!empty($file_name)){

$file_type= $_FILES['file']['type'];

$sam="sample";
$name=$sam.(mt_rand(100000,9999999));



	$path_to_file='/var/www/html/product/import/';
	//begin the information fields//

$file_size= $_FILES['file']['size'];
$file_tmp= $_FILES['file']['tmp_name'];
move_uploaded_file($file_tmp,$path_to_file.$name);



echo"<br> Upload Successful";
}
}
}


if(isset($_POST['button2'])) {
export();
}







// begin the nested functions to pick a export going from platform_export_choice to a export_wholesaler_option to a function with the specific export in it
if(isset($_GET['btn-plat_option'])) {
platform_export_choice();
}


//here we tell export page what platform was selected then will go to the options to determin what export to perform
function platform_export_choice() {
	if(isset($_GET['btn-plat_option'])) {
	$platform = $_GET['platform'];
		switch($platform) {
 				case "WayfairCOM":
							export_wayfaircom_options();
              break;
 				case "WayfairCA":
							export_wayfairca_options();
              break;
 				case "Ebay_DSCB":
							export_ebaydscb_options();
              break;
 				case "AmazonCA":
							export_amazonca_options();
              break;
 				case "AmazonCOM":
							export_amazoncom_options();
              break;
 				case "AmazonAU":
							export_amazonau_options();
              break;
 				case "WalmartCA":
							export_walmartca_options();
              break;
 				case "WalmartCOM":
							export_walmartcom_options();
              break;
 				case "Etsy":
							export_etsy_options();
              break;
 				case "Unbeatable":
							export_unbeatable_options();
              break;
 				case "Ebay_MDR":
							export_ebaymdr_options();
              break;
 				case "OverstockCA":
							export_overstockca_options();
              break;
 				case "OverstockCOM":
							export_overstockcom_options();
              break;
 				case "Ebay_MRMJS":
							export_ebaymrmjs_options();
              break;
						}
				}
		}

//determin what options are selected from the platform export page
function export_wayfaircom_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price":
								export_wayfaircom_price();
								break;
					case "Audit":
								export_wayfaircom_audit();
								break;
			}
	 }
}
function export_wayfairca_options(){


	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price":
								export_wayfairca_price();
								break;
					case "Audit":
								export_wayfairca_audit();
								break;
			}
	 }
 }
function export_ebaydscb_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price":
								export_ebaydscb_price();
								break;
					case "Inventory":
								export_ebaydscb_inventory();
								break;
					case "Price_Inventory":
								export_ebaydscb_price_inventory();
								break;
					case "Audit":
								export_ebaydscb_audit();
								break;
					}
	 		}
 }
function export_ebaymdr_options(){
 	if(isset($_GET['btn-plat_option'])) {
 		$plat_option = $_GET['platb'];
 			switch($plat_option) {
 					case "Price":
 								export_ebaymdr_price();
 								break;
 					case "Inventory":
 								export_ebaymdr_inventory();
 								break;
 					case "Price_Inventory":
 								export_ebaymdr_price_inventory();
 								break;
 					case "Audit":
 								export_ebaymdr_audit();
 								break;
 					}
 	 		}
  }
function export_ebaymrmjs_options(){
		if(isset($_GET['btn-plat_option'])) {
			$plat_option = $_GET['platb'];
				switch($plat_option) {
						case "Price":
									export_ebaymrmjs_price();
									break;
						case "Inventory":
									export_ebaymrmjs_inventory();
									break;
						case "Price_Inventory":
									export_ebaymrmjs_price_inventory();
									break;
						case "Audit":
									export_ebaymrmjs_audit();
									break;
						}
		 		}
	 }
function export_amazonca_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price_Inventory":
								export_amazonca_price_inventory();
								break;
					case "Audit":
								export_amazonca_audit();
								break;
					}
			}
}
function export_amazoncom_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price_Inventory":
								export_amazoncom_price_inventory();
								break;
					case "Audit":
								export_amazoncom_audit();
								break;
					}
			}
}
function export_amazonau_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price_Inventory":
								export_amazonau_price_inventory();
								break;
					case "Audit":
								export_amazonau_audit();
								break;
					}
			}
}
function export_walmartca_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price":
								export_walmartca_price();
								break;
					case "Inventory":
								export_walmartca_inventory();
								break;
					case "Audit":
								export_walmartca_audit();
								break;
					}
	 		}
}
function export_walmartcom_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price":
								export_walmartcom_price();
								break;
					case "Inventory":
								export_walmartcom_inventory();
								break;
					case "Audit":
								export_walmartcom_audit();
								break;
					}
	 		}}
function export_etsy_options(){
	export_etsy_audit();
	}
function export_unbeatable_options(){
	if(isset($_GET['btn-plat_option'])) {
		$plat_option = $_GET['platb'];
			switch($plat_option) {
					case "Price":
								export_unbeatable_price();
								break;
					case "Inventory":
								export_unbeatable_inventory();
								break;
					case "Audit":
								export_unbeatable_audit();
								break;
				}
 		}
}
function export_overstockca_options(){
	export_overstockca_audit();
	}
function export_overstockcom_options(){
  export_overstockcom_audit();
}

																				//All export
function export_wayfaircom_price(){
	echo "We will download something here";
}
function export_wayfaircom_audit(){
	echo "We will download something here";
}
function export_wayfairca_price(){
	echo "We will download something here";
}
function export_wayfairca_audit(){
	echo "We will download something here";
}
function export_ebaydscb_price(){
  $filename = 'ebay_dscb.csv';
  dwn_csv($filename);
}
function export_ebaydscb_inventory(){
	$filename = 'ebay_dscb.csv';
  dwn_csv($filename);

}
function export_ebaydscb_price_inventory(){

	$filename = 'ebay_dscb.csv';
  dwn_csv($filename);
}
function export_ebaydscb_audit(){
	$filename = 'ebay_dscb.csv';
  dwn_csv($filename);
}
function export_ebaymrmjs_price(){
	$filename = 'ebay_mrmjs.csv';
  dwn_csv($filename);
}
function export_ebaymrmjs_inventory(){
	$filename = 'ebay_mrmjs.csv';
  dwn_csv($filename);
}
function export_ebaymrmjs_price_inventory(){
	$filename = 'ebay_mrmjs.csv';
  dwn_csv($filename);
}
function export_ebaymrmjs_audit(){
	$filename = 'ebay_mrmjs.csv';
  dwn_csv($filename);
}
function export_ebaymdr_price(){
	$filename = 'ebay_mdr.csv';
  dwn_csv($filename);
}
function export_ebaymdr_inventory(){
	$filename = 'ebay_mdr.csv';
  dwn_csv($filename);
}
function export_ebaymdr_price_inventory(){
	$filename = 'ebay_mdr.csv';
  dwn_csv($filename);
}
function export_ebaymdr_audit(){
	$filename = 'ebay_mdr.csv';
  dwn_csv($filename);
}
function export_amazonca_price_inventory(){
	global $mysqli_product;
	//determine location of file//
	$filename = 'Amazon_ca.txt';
	$path_to_file='/var/www/html/product/exports/';
	//begin the information fields//
	header('Content-type: product/txt');
	header('Content-Disposition: attachment; filename=Price_Inventory_'.$filename);
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
}
function export_amazonca_audit(){
	global $mysqli_product;
	//determine location of file//
	$filename = 'Amazon_ca.txt';
	$path_to_file='/var/www/html/product/exports/';
	//begin the information fields//
	header('Content-type: product/txt');
	header('Content-Disposition: attachment; filename=Audit_'.$filename);
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
}
function export_amazoncom_price_inventory(){
	global $mysqli_product;
	//determine location of file//
	$filename = 'Amazon_com.txt';
	$path_to_file='/var/www/html/product/exports/';
	//begin the information fields//
	header('Content-type: product/txt');
	header('Content-Disposition: attachment; filename=Price_Inventory_'.$filename);
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
}
function export_amazoncom_audit(){
	global $mysqli_product;
	//determine location of file//
	$filename = 'Amazon_com.txt';
	$path_to_file='/var/www/html/product/exports/';
	//begin the information fields//
	header('Content-type: product/txt');
	header('Content-Disposition: attachment; filename=Audit_'.$filename);
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
}
function export_amazonau_price_inventory(){
	global $mysqli_product;
	//determine location of file//
	$filename = 'Amazon_au.txt';
	$path_to_file='/var/www/html/product/exports/';
	//begin the information fields//
	header('Content-type: product/txt');
	header('Content-Disposition: attachment; filename=Price_Inventory_'.$filename);
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
}
function export_amazonau_audit(){
	global $mysqli_product;
	//determine location of file//
	$filename = 'Amazon_au.txt';
	$path_to_file='/var/www/html/product/exports/';
	//begin the information fields//
	header('Content-type: product/txt');
	header('Content-Disposition: attachment; filename=Audit_'.$filename);
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
}
function export_walmartca_price(){
  if($this->input->post('qty_walmart'))
		{
			$queryInsert = "select * from prd_mpl_active_qtyprice";
			$queryInsert = $this->db->query($queryInsert);

			if ($queryInsert->num_rows() > 0)
			{
				$truncate = $this->db->truncate('plat_walmart_temp');

				$sku_header_string 			= 'What is the SKU of the item(s) you are updating inventory for? (e.g. 12345)';
				$new_quantity_string 		= 'What is the new inventory count? (e.g. 10)';
				$fulfilment_lag_time_string = 'The number of days between when the item is ordered and when it is shipped (e.g. 2)';

				$this->db->set('sku', convert_accented_characters(utf8_encode($sku_header_string)));
				$this->db->set('new_quantity', convert_accented_characters(utf8_encode($new_quantity_string)));
				$this->db->set('fulfilment_lag_time', convert_accented_characters(utf8_encode($fulfilment_lag_time_string)));
				$this->db->insert('plat_walmart_temp');
				$last_insert = $this->db->insert_id();
				if(!empty($last_insert))
				{
					foreach ($queryInsert->result() as $rowInsert)
					{
						$sku = $rowInsert->part_no;
						$qty = $rowInsert->qty;
						$lag = 4;

						$this->db->set('sku', $sku);
						$this->db->set('new_quantity', $qty);
						$this->db->set('fulfilment_lag_time', $lag);
						$this->db->insert('plat_walmart_temp');
					}
					$last_id = $this->db->insert_id();
					if(!empty($last_id))
					{
						$this->db->select('*');
						$query = $this->db->get('plat_walmart_temp');

						if ($query->num_rows() == 0)
						{
							$this->session->set_userdata('msg', '<font color="red">No record!</font>');
							redirect(base_url().'platform_inventory_price');
						}
						else
						{
							$fileName = 'Walmart_Inventory.xlsx';
							$this->load->library('excel');
							$objPHPExcel = new PHPExcel();
							$objPHPExcel->setActiveSheetIndex(0);

							$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SKU*');
							$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'New Quantity*');
							$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Fulfilment Lag Time*');

							$rowCount = 2;
							foreach ($query->result() as $row)
							{
								$sku 	 				= $row->sku;
								$new_quantity  			= $row->new_quantity;
								$fulfilment_lag_time	= $row->fulfilment_lag_time;

								$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $sku);
								$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $new_quantity);
								$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $fulfilment_lag_time);
								$rowCount++;
							}
							header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
							header('Content-Disposition: attachment;filename="'.$fileName.'"');
							header('Cache-Control: max-age=0');
							// If you're serving to IE 9, then the following may be needed
							header('Cache-Control: max-age=1');

							// If you're serving to IE over SSL, then the following may be needed
							header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
							header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
							header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
							header ('Pragma: public'); // HTTP/1.0

							$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
							$objWriter->save('php://output');
					//		exit;
            }
  }
  }
  }
  }
}
function export_walmartca_inventory(){
  global $mysqli_product;
  // HERE ADD THE downloadFile() function
  $dir ='/var/www/html/product/exports/';    // folder wth files for download
  $file = $dir.'walmartca.xlsx';
  // $_GET['file'] contains the name and extension of the file stored in 'download/'
  // if (isset($_GET['file'])) {
  //   $file = $dir . strip_tags($_GET['file']);
    downloadFile($file);
  }
function export_walmartca_audit(){
	echo "We will download something here";
}
function export_walmartcom_price(){
  echo "We will download something here";
}
function export_walmartcom_inventory(){
  echo "We will download something here";
}
function export_walmartcom_audit(){
	echo "We will download something here";
}
function export_etsy_audit(){
	echo "We will download something here";
}
function export_unbeatable_price(){
	echo "We will download something here";
}
function export_unbeatable_inventory(){
	echo "We will download something here";
}
function export_unbeatable_audit(){
	echo "We will download something here";
}
function export_overstockcom_audit(){
	echo "We will download something here";
}
function export_overstockca_audit(){
	echo "We will download something here";
}

//import to xlsx
function process_xlsx($fname, $table, $mapping, $tx = false) {
	global $pdo_product;

	$xlsx = SimpleXLSX::parse($fname);

	if (!$xlsx) {
		die('error parsing xlsx - '.SimpleXLSX::parseError());
	}

	$cols = implode(', ', array_map(function ($a) { return "`$a`"; }, array_values($mapping)));
	$vals = implode(', ', array_pad([], count($mapping), '?'));
	$keys = array_map('strtolower', array_keys($mapping));


	if ($tx) $pdo_product->beginTransaction();
	$insert = $pdo_product->prepare("INSERT INTO `$table` ($cols) VALUES ($vals);");

	$heading = [];
	foreach ($xlsx->rows() as $n => $row) {
		if ($n == 0) {
			$heading = array_map('strtolower', $row);
			continue;
		}

		$vals = array_combine($heading, $row);
		$match = array_intersect_key($vals, $mapping);
		$insert->execute(array_values($match));
	}
	if ($tx) $pdo_product->commit();






}
//exports
function dwn_csv($filename){
  global $mysqli_product;
  $path_to_file='/var/www/html/product/exports/';
  $file_wholesaler = substr($filename, 0 , strpos($filename,'.'));

  	//begin the information fields//
  	header('Content-type: product/csv');
  	header('Content-Disposition: attachment; filename='.$filename);
  	$out = '';
  	$fields = '';
  	//match these fields with the fields in the while//
  	$fields = array('sku','price','Quantity');

  	//obtain the data to put into a file//
    $sql = "SELECT * FROM `prd_mpl_active_qtyprice` join prd_mpl_active_info on prd_mpl_active_qtyprice.mdr_sku = prd_mpl_active_info.mdr_sku where prd_mpl_active_info.".$file_wholesaler." = 'Y'";
    $result = $mysqli_product->query($sql);
    //echo $sql;
  		foreach ($fields as $field)
  		{
  		$out .= '"'.$field.'",';
  		}
  		$out .="\n";
  	//match thesefields with the $fields
  		while ($row = $result->fetch_assoc()) {
  		$qty = $row['qty'];
  		$sku = $row['mdr_sku'];
      //prepare the price calculations by getting $sell_as,$exchange_rate,$platform_fee,$platform_profit,$shipping,$wholesaler_cost
      if(isset($_GET['btn-plat_option'])) {
    	$platform = $_GET['platform'];
      $sell_as = get_sellas($platform);
      echo $sell_as;
      $platform_fee = get_platform_fee($platform);
      $platform_profit = get_platform_profit($platform);
      $exchange_rate = get_exchange();
    }
      $shipping = get_shipping();
      $wholesaler_cost = get_wholesaer_cost();

      $price = price_calc($sell_as,$exchange_rate,$platform_fee,$platform_profit,$shipping,$wholesaler_cost);
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
}



function get_platform_fee($platform){
  global $mysqli_global;
  $sql = "SELECT * FROM `gbl_platforms` where platform = '$platform'";
  $result = $mysqli_global->query($sql);
  while ($row = $result->fetch_assoc()) {
  $platform_fee = $row['platform_fee'];
  }
  }

function get_platform_profit($platform){
  global $mysqli_global;
  $sql = "SELECT * FROM `gbl_platforms` where platform = '$platform'";
  $result = $mysqli_global->query($sql);
  while ($row = $result->fetch_assoc()) {
  $platform_fee = $row['platform_profit'];
  }
  }

function get_country(){

}

function get_sellas(){


}

function get_exchange(){


}

function get_shipping(){


}

function get_wholesaer_cost(){


}
//function price_calc($sell_as,$exchange_rate,$platform_fee,$platform_profit,$shipping,$wholesaler_cost){

//}

function price_calc($row){
  global $platform;
  global $mysqli_global;
  global $mysqli_product;
  $mdr_sku = $row['mdr_sku'];
  $wholesaler_cost = $row['wholesaler_cost'];
  if(isset($_GET['btn-plat_option'])) {
	 $platform = $_GET['platform'];
   //   echo $platform;
  $sql = "SELECT * FROM `gbl_platforms` where platform = '$platform'";
  $result = $mysqli_global->query($sql);
  while ($row = $result->fetch_assoc()) {
  $country = $row['country'];
  $platform_fee = $row['platform_fee'];
  $platform_profit = $row['platform_profit'];
  }
  /// retrieve sold as
  //retrieve name of sell as column

    switch($platform) {
        case "WayfairCOM":
        $sell_on = "sell_as_wayfair";
              break;
        case "WayfairCA":
        $sell_on = "sell_as_wayfair";
              break;
        case "Ebay_DSCB":
  //      echo $mdr_sku;
        $sql = "SELECT * FROM `prd_mpl_active_soldas` where mdr_sku = '$mdr_sku'";
        //        echo $sql;
        $result = $mysqli_product->query($sql);
            while ($row = $result->fetch_assoc()) {
            $sell_as = $row['sell_as_ebay'];
            // At this point sell as being returned
            // echo $sell_as;
            // echo "platform is ebay";
            }
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
  global $sell_as;
  //   echo $sell_as;
  //   exit;
  global $country;
  global $platform_fee;
  global $platform_profit;
  //retreive exchange rate
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
  //      echo $mdr_sku;
      exit;
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


    //       price_calc($sell_as,$exchange_rate,$platform_fee,$platform_profit,$shipping,$wholesaler_cost);

       $cost = $wholesaler_cost * $sell_as * 1.13;
                  //  echo $cost;
       $sell_price = ceil(($cost+$shipping)/((1-$platform_profit)-(1*$platform_fee))) * $exchange_rate;
                    // echo $sell_price;
                   return $sell_price;
      }

      //function price_calc($sell_as,$exchange_rate,$platform_fee,$platform_profit,$shipping,$wholesaler_cost){
    // retrieve and calculate cost

    // ***Note when get the exchange rate if getting for a CAN then exchange rate will be 1*/
  //}


?>
