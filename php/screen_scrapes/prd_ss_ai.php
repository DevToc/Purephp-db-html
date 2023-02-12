<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


$email = 'mdrtradinginc@gmail.com';
$pass  = 'Mdr856268*';

$up_dir = 'images/ai';
$base = '';


	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ai_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_ai_products` (`category`, `subcategory`, `title`, `price`, `sku`, `size`, `notes`, `moq`, `qty`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:98.0) Gecko/20100101 Firefox/98.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY, 'localhost:8080'); 

	echo "<pre>\nlogging in\n";

	$log_url = 'https://actionimports.b2bdirect.io/account/login';
	$content = http_req($log_url);
	if (!preg_match('#<input name="__RequestVerificationToken" type="hidden" value="([^"]*?)"#', $content, $m)) die('no token');
	$token = $m[1];


	$params = array(
		'__RequestVerificationToken' => $token, 
		'Username' => $email, 
		'Password' => $pass, 
	);

	$content = http_req('https://actionimports.b2bdirect.io/account/login?ReturnUrl=', $params, ['Referer: '.$log_url]);
	if (!(preg_match('#>\s*Logout\s*</a>#i', $content))) die('auth failed!');


	if (!preg_match('#var app = headerVueApp\(\s*(\[.*?\]), [^\[\]]*?\);#s', $content, $m)) die('menu not found');
	$data = json_decode($m[1], true);
	if (!$data) die('menu problem');
	process_cats($data, []);



function process_cats($data, $stack) {
	foreach ($data as $i) {
		$name = $i['Name'];
		$id = $i['CategoryID'];
		$sc = $i['SubCategories'];
		$cstack = array_merge($stack, [$name]);

		if ($sc) {
			process_cats($sc, $cstack);
		} else {                                                           
			$url = 'https://actionimports.b2bdirect.io/categories/'.$id.'/'.urlencode($name);
			$path = implode(' > ', $cstack);
			echo "Category: $path\r\n";
			list($cat, $scat) = array_pad($cstack, 2, '');
			scrape_category($url, $cat, $scat);
			
		}
	}
}

function scrape_category($url, $cat, $scat) {
global $up_dir, $insert_product;


	$page = 1;
	while (1) {
		$content = http_req($url.'/products?page='.$page.'&sortOn=ItemName&direction=asc&filters=UDF16:,UDF17:,UDF18:,UDF19:,UDF20:', false, array('Accept: application/json, text/plain, */*'));

		$data = json_decode($content, true);
		if (!$data || !isset($data['Products'])) {
			echo "no products\r\n";
			break;
		}
		foreach ($data['Products'] as $prod) {
			$title = $prod['ItemName'];
			$price = $prod['Price'];
			$sku = $prod['ItemID'];
			echo "\t$sku\r\n";
			$size = '';
			$notes = $prod['Notes'];
			$moq = $prod['OrderMinimumQuantity'];
			$qty = $prod['OnHandQuantity'];

			$img = '';
			$imgurl = $prod['ImageURL'];
			if ($imgurl) {
				$image_file = preg_replace('#/#', '_', $sku);
				$path = preg_replace('#\?.*?$#', '', $imgurl);
				$info = pathinfo($path);
				$image_file.='.'.$info['extension'];
				$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
				$res = dl_file($imgurl, $path);
				if ($res) $img = $image_file;
			}
			$row = array($cat, $scat, $title, $price, $sku, $size, $notes, $moq, $qty, $img);
			$insert_product->execute($row);
		}
		if ($data['PageSize'] * $data['CurrentPage'] >= $data['TotalRecords']) break;
		$page++;
		echo "+page\r\n";
	}
}

function http_req($url, $postfields = false, $headers = false) {
global $ch;

	curl_setopt($ch, CURLOPT_URL, $url);

	if ($postfields) {
		if (is_array($postfields)) $postfields = http_build_query($postfields);
		curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	}

	if ($headers) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}

	$content = curl_exec($ch);

	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array());

	return $content;
}

function dl_file($url, $fname) {
global $ch;

	curl_setopt($ch, CURLOPT_URL, $url);
	$content = curl_exec($ch);

	$info = curl_getinfo($ch);
	$success = ($info['http_code'] == 200) ? 1 : '';

	if ($success) {
		$fh = fopen($fname, 'wb');
	    if ($fh == FALSE) return 'cant open file';
		fwrite($fh, $content);
		fclose($fh);
	}
	return $success;
}
