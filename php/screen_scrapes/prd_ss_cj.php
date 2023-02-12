<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




$email = 'mdrtradinginc@gmail.com';
$pass  = 'Mdr856268*';

$up_dir = 'images/cj';
$base = 'https://www.shopzio.com';


	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_cj_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_cj_products` (`category`, `sku`, `title`, `price`, `size_l`, `size_m`, `moq`,  `image1`, `image2`, `image3`, `image4`, `image5`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
//	curl_setopt($ch, CURLOPT_PROXY, 'localhost:8000'); 

	echo "<pre>\nlogging in\n";

	http_req('https://www.shopzio.com/home/login');
	$params = array(
		'LoginEmail'    => $email,
		'LoginPassword' => $pass,
		'ReturnURL'     => '',
	);
	$content = http_req('https://www.shopzio.com/home/login/0', $params);
	if (!(preg_match('#>Logout<#i', $content))) die('auth failed!');

	$content = http_req('https://www.shopzio.com/brands/detail/1084');
	if (!preg_match_all('#<h6[^>]*?>\s*<a[^>]*?href="([^"]*?)">(.*?)</a>\s*</h6>#si', $content, $m)) die('categories not found');
	$cats = array_combine($m[2], $m[1]);
	foreach ($cats as $cat => $url) {
		$cat = html_entity_decode($cat);
		$url = html_entity_decode($url);
		$url = preg_replace_callback('#category=([^&]+?)(?=&)#', 'url_conv', $url);
		echo "Category: $cat\n";
		scrape_category($base.$url, $cat);
	}

function scrape_category($url, $cat) {
global $base;
	$next = $url;
	$pages = $basep = '';
	$page = 1;
	while (1) {
		$content = http_req($next);

		if (preg_match_all('#<h6[^>]*?>\s*<a[^>]*?href="([^"]*?)">(.*?)</a>\s*</h6>#si', $content, $m)) {
			$cats = array_combine($m[2], $m[1]);
			foreach ($cats as $scat => $url) {
				$scat = html_entity_decode($scat);
				$url = html_entity_decode($url);
				$url = preg_replace_callback('#category=([^&]+?)(?=&)#', 'url_conv', $url);
				echo "\tSubcategory: $scat\n";
				scrape_category($base.$url, $cat);
			}
			break;
		}

		scrape_items($content, $cat);
		if (!$pages) {
			if (preg_match('#paginationComponent\(document, (\{.*?\})\);#s', $content, $m)) {
				$block = $m[1];
				$pages = preg_match('#"total": "([^"]*?)"#', $block, $m) ? $m[1] : '';
				$basep = preg_match('#"baseURL": \'([^\'"]*?)\'#', $block, $m) ? 'https://www.shopzio.com'.html_entity_decode($m[1]) : '';
				$basep = str_replace(' ', '%20', $basep);
			}
		}
		$page++;
		if ($page > $pages) break;
		echo "page $page of $pages\r\n";
		$next = str_replace('{0}', $page, $basep);
	}
}

function scrape_items($content, $cat) {
global $up_dir, $insert_product;

	if (!preg_match('#var productData = (\[\{.*?\}\]);#si', $content, $m)) {
		echo "products not found\n";
		return;
	}
	$data = json_decode($m[1], true);
	if (!$data) {
		echo "products not found\n";
		return;
	}

	$headings = array('ItemID', 'ItemName', 'Price', 'Udf3', 'Dimensions', 'OrderMinimumQuantity');
	foreach ($data as $product) {
		$row = array($cat);
		foreach ($headings as $key) {
			$row[] = $product[$key];
		}
		$sku = $row[1];
		echo "\t$sku\n";
		$row[4] = preg_replace('#^"|"$#', '', $row[4]);
		$row[5] = preg_replace('#^"|"$#', '', $row[5]);
		$pname = $product['PhotoName'];
		$icount = $product['AdditionalImageCount'];
		$images = array();

		for ($n = 0; $n <= $icount; $n++) {
			$suff = $n > 0 ? "${pname}_${n}_lg.jpg" : "${pname}_lg.jpg";
			$imgurl = 'https://repziocdn.global.ssl.fastly.net/productimages/1084/'.$suff;
			$image_file = $sku.'-'.($n+1).'.jpg';						
			$res = dl_file($imgurl, $up_dir.DIRECTORY_SEPARATOR.$image_file);
			if ($res) $images[] = $image_file;
			if ($n == 4) break;
		}
		$images = array_pad($images, 5, '');
		$resultrow = array_merge($row, $images);
		$insert_product->execute($resultrow);
	}
}

function url_conv($matches) {
	return 'category='.urlencode($matches[1]);
}

function http_req($url, $postfields = false) {
global $ch;

	curl_setopt($ch, CURLOPT_URL, $url);
	$post = false;
	if ($postfields) {
		$post = true;
		if (is_array($postfields)) $postfields = http_build_query($postfields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	}

	curl_setopt($ch, CURLOPT_POST, $post);
	$content = curl_exec($ch);
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
