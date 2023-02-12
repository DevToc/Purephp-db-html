<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


$user = 'mrmjstrading@gmail.com';
$password = 'Mdr856268*';

$up_dir = 'images/bm';


	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_bm_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_bm_products` (`category`, `subcategory`, `item`, `sku`, `size`, `price`, `add_info`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	echo "logging in<br><br>\n";

	$log_url = 'http://www.boxman.ca/wp-login.php';
	$content = http_req($log_url);
	if (!preg_match('#<input[^<]*?name="wp-submit"#s', $content)) die('login form not found');

	$params = array(
		'log' => $user, 
		'pwd' => $password, 
		'wp-submit' => 'Log In', 
		'redirect_to' => 'http://www.boxman.ca/wp-admin/', 
		'testcookie' => '1',
	);


	$content = http_req($log_url, $params);
	if (!(preg_match('#>My Account<#i', $content))) die('auth failed!');

	if (!preg_match('#<ul id="top-menu" class="nav">(.*?)</ul>\s*</nav>#s', $content, $m)) die('categories not found1');
	if (!preg_match_all('#(<li id="menu-item[^>]*?>\s*<a[^>]*?>[^<]*?</a>\s*(?:<ul class="sub-menu">.*?</ul>\s*|(?R))?</li>)#s', $m[1], $m)) die('categories not found');
	$catsets = $m[1];

	foreach ($catsets as $cs) {
		if (!preg_match('#^<li[^>]*?>\s*<a href="([^"]*?)">\s*(.*?)\s*</a>#', $cs, $m)) continue;
		$cat = $m[2];
		$cat_href = $m[1];
		echo "Category: $cat\n";
		if (preg_match('#^.*?<ul class="sub-menu">(.*)</li>$#s', $cs, $m)) {
			$bl = preg_replace('#<ul[^>]*?>.*?</ul>#s', '', $m[1]);
			preg_match_all('#<li[^>]*?>\s*<a href="([^"]*?)">\s*(.*?)\s*</a>#', $bl, $m);
			$scats = array_combine($m[2], $m[1]);
			foreach ($scats as $scat => $scat_href) {
				echo "\tSubcategory: $scat\n";
				scrape_category($scat_href, $cat, $scat);
			}
		} else {
			scrape_category($cat_href, $cat);
		}
	}

function scrape_category($url, $cat, $scat = '') {
	$next = $url;
	while (1) {
		$content = http_req($next);
		if (!preg_match_all('#<li class="[^>]*?">\s*<a href="([^"]*?)" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">#s', $content, $m)) break;

		foreach ($m[1] as $prod_url) {
			scrape_product($prod_url, $cat, $scat);
		}

		if (!preg_match('#<li><a class="next page-numbers" href="([^"]*?)">&rarr;</a></li>#s', $content, $m)) break;
		$next = $m[1];
	}
}



function scrape_product($url, $cat, $scat) {
global $insert_product, $up_dir, $base;
	echo "$url\r\n";
	$content = http_req($url);

	$sku = preg_match('#<span class="sku_wrapper">SKU: <span class="sku">\s*(.*?)\s*</span></span>#s', $content, $m) ? $m[1] : '';
	if (!$sku) return;
	echo "\t\t$sku\n";

	$title = preg_match('#<h1 class="product_title entry-title">\s*(.*?)\s*</h1>#s', $content, $m) ? $m[1] : '';
	$price = preg_match('#<p class="price"><span class="woocommerce-Price-amount amount">\s*(?:<bdi>\s*)?<span class="woocommerce-Price-currencySymbol">[^<]*?</span>\s*(.*?)\s*</#s', $content, $m) ? $m[1] : '';

	$size = preg_match('#<p>Size:</p>(.*?)</div>#s', $content, $m) ? $m[1] : '';
	$size = preg_replace('#\s*[\r\n]+?\s*#s', '', $size);
	$size = preg_replace('#\s*</?span[^>]*?>\s*#', '', $size);
	$size = preg_replace('#\s*(?:</?p>)+\s*#', "\r\n", $size);
	$size = trim($size);

	$additional = '';
	if (preg_match('#<table class="[^"]*?shop_attributes[^"]*?"[^>]*?>(.*?)</table>#s', $content, $m)) {
		$vals = array();
		preg_match_all('#<tr[^>]*?>(.*?)</tr>#s', $m[1], $m);
		$rows = $m[1];
		foreach ($rows as $row) {
			$k = preg_match('#<th[^>]*?>\s*(.*?)\s*</th>#s', $row, $m) ? $m[1] : '';
			$v = preg_match('#<td[^>]*?>\s*(.*?)\s*</td>#s', $row, $m) ? $m[1] : '';
			if ($k && $v) {
				$vals[]="$k: $v";
			}
		}
		$additional = implode("\r\n", $vals);
	}

	$img_url = preg_match('#<div[^>]*?woocommerce-product-gallery__image"><a[^>]*?href="([^"]*?)"#s', $content, $m) ? $m[1] : '';
		 
	if ($img_url) {
		$image = $base.$img_url;
		$path = preg_replace('#\?.*?$#', '', $image);
		$info = pathinfo($path);
		$image_file = "$sku.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image, $path);
		if ($res) $img = $image_file;
	}

	$row = array_map('html_entity_decode', array($cat, $scat, $title, $sku, $size, $price, $additional, $img));

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
