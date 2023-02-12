<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');



$user = 'mrmjstrading@gmail.com';
$password = 'mrmj070468';

$base = 'https://www.vlimportstore.com/';
$up_dir = 'images/vl';
	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_vl_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_vl_products` (`category`, `subcategory`, `item`, `description`, `price`, `qty`, `sku`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	echo "logging in<br><br>\n";

	http_req('https://www.vlimportstore.com/myaccount.asp');

	$params = array(
		'catalogid' => '0',
		'email' => $user,
		'password' => $password,
		'submitted' => '',
	);

	$content = http_req('https://www.vlimportstore.com/login.asp?ordertracking=1', $params);
	if (!(preg_match('#>Logout<#i', $content))) die('auth failed!');

	$content = http_req('https://www.vlimportstore.com/category_index.asp');

	if (!preg_match('#<span[^>]*?>\s*Category\s*</span>(.*?)</ul>#si', $content, $m)) die('categories not found');
	if (!preg_match_all('#<li><a[^>]*?href="([^"]*?)"[^>]*?>\s*(.*?)\s*</a>#s', $m[1], $m)) die('categories not found');
	$cats = array_combine($m[2], $m[1]);
	foreach ($cats as $cat => $href) {
		echo "Category: $cat\n";
		scrape_category($base.$href, array($cat));
	}
function scrape_category($url, $cat) {
global $base;

	$content = http_req($url);
	$content = preg_replace('#<!--[^>]*?-->#s', '', $content);

	if (preg_match_all('#<li>\s*<div class="sub-categories">\s*<a href="([^"]*?)">\s*<span class="name">\s*(.*?)\s*</span>#s', $content, $m)) {
		$scats = array_combine($m[1], $m[2]);
		foreach ($scats as $href => $scat) {
			echo "\tSubcategory: $scat\n";
			$cats = $cat;
			$cats[] = $scat;
			scrape_category($base.$href, $cats);
		}
		return;
	}

	if (preg_match('#<a href="([^"]*?)" class="category-viewall">View All</a>#s', $content, $m)) {
		$content = http_req($base.$m[1]);
	}

	$cat = array_pad($cat, 2, '');

	if (preg_match_all('#<div class="name"><a href="([^"]*?)">#s', $content, $m)) {
		$items = $m[1];
		foreach ($items as $href) {
			scrape_product($base.$href, $cat[0], $cat[1]);
		}
	}
}


function scrape_product($url, $cat, $subcat) {
global $insert_product, $up_dir, $base;
	$content = http_req($url);

	$sku = preg_match('#<div class="product-id">Part Number:<span id="product_id">\s*(.*?)\s*</span></div>#s', $content, $m) ? $m[1] : '';
	if (!$sku) return;
	echo "\t\t$sku\n";

	$item = preg_match('#<h1 itemprop="name" class="page_headers">\s*(.*?)\s*</h1>#s', $content, $m) ? $m[1] : '';
	$qty = preg_match('#<div id="availability">(.*?) in stock</div>#s', $content, $m) ? $m[1] : '';
	$price = preg_match('#<span itemprop="price" id="price">\s*(.*?)\s*</span>#s', $content, $m) ? $m[1] : '';
	$desc = preg_match('#<div class="item" itemprop="description">\s*(.*?)\s*</div>\s*</div>\s*<div id="tab-#s', $content, $m) ? $m[1] : '';
	$img_url = $img = '';


	if (preg_match('#<li class="prod-thumb image1"><a href="([^"]*?)"#si', $content, $m)) {
		$img_url = $m[1];
	} elseif (preg_match('#<div class="main-image"><a href="([^"]*?)"#si', $content, $m)) {
		$img_url = $m[1];
	}
		 
	if ($img_url) {
		$image = $base.$img_url;
		$path = preg_replace('#\?.*?$#', '', $image);
		$info = pathinfo($path);
		$image_file = "$sku.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image, $path);
		if ($res) $img = $image_file;
	}
	$row = array_map('html_entity_decode', array($cat, $subcat, $item, $desc, $price, $qty, $sku, $img));
	$insert_product->execute($row);
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
	curl_setopt($ch, CURLOPT_POST, false);
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

?>
