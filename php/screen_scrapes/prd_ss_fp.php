<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




$user = 'mdrtradinginc@gmail.com';
$password = 'Mdr856268*';

$up_dir = 'images/fp';


	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_fp_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_fp_products` (`category`, `sku`, `title`, `price`, `qty`, `future`, `size`, `length`, `width`, `height`, `description`, `image1`, `image2`, `image3`, `image4`, `image5`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	echo "logging in<br><br>\n";

	$log_url = 'https://www.forpost-trade.ca/my-account/';

	$content = http_req($log_url);
	$nonce = (preg_match('#<input type="hidden"[^>]*?name="woocommerce-login-nonce"[^>]*?value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';

	$have = array();
	$params = array(
		'username' => $user, 
		'password' => $password, 
		'woocommerce-login-nonce' => $nonce, 
		'_wp_http_referer' => '/my-account/', 
		'login' => 'Log in', 
	);

	$content = http_req($log_url, $params);
	if (!(preg_match('/>Logout</i', $content))) die('auth failed!');
	$content = http_req('https://www.forpost-trade.ca/?currency=CAD');

	if (!preg_match('#<li[^>]*?>\s*<a[^>]*?>\s*Catalog\s*</a>(.*?)<li[^>]*?>\s*<a[^>]*?>\s*About Us\s*#s', $content, $m)) die('catalog not found');
	if (!preg_match_all('#<li[^>]*?>\s*<a[^>]*?>\s*([^<]*?)\s*</a>\s*<ul class="sub-menu">(.*?)</ul>#s', $m[1], $m)) die('categories not found');
	$cats = array_combine($m[1], $m[2]);

	foreach ($cats as $cat => $block) {
		$block = html_entity_decode($block);
//		if (!preg_match('#Collection#i', $cat)) continue;
		echo "Category: $cat\r\n";
		if (!preg_match_all('#<li[^>]*?>\s*<a[^>]*?href="([^"]*?)"[^>]*?>\s*([^<]*?)\s*</a>#', $block, $m)) continue;
		$scat = array_combine($m[1], $m[2]);
		foreach ($scat as $href => $scat) {
			echo "\tSubcategory: $scat\r\n";
			scrape_category($href, $cat, $scat);
		}
	}



function scrape_category($url, $cat, $scat) {
	$next = $url;
	while (true) {
		$content = http_req($next);
		preg_match_all('#<div class="product-item__thumbnail">(.*?</li>)#s', $content, $m);
		$items = $m[1];
		foreach ($items as $i) {
			if (!preg_match('#data-product_sku="([^"]*?)"#', $i, $m)) continue;
			$sku = $m[1];
			$href = (preg_match('#<a href="([^"]*?)" class="title"#s', $i, $m)) ? $m[1] : '';
			scrape_product($href, $scat, $sku);
		}
		if (!preg_match('#<li><a class="next page-numbers" href="([^"]*?)"#', $content, $m)) break;
		$next = $m[1];
	}
}

function scrape_product($url, $cat, $sku1) {
global $insert_product, $up_dir, $have;

	if(array_key_exists($sku1, $have)) return;
	$content = http_req($url);
	$sku = (preg_match('#<span class="sku">\s*(.*?)\s*</span>#si', $content, $m)) ? $m[1] : '';
	if (!$sku) return;
	$title = (preg_match('#<h1 class="product_title entry-title">(.*?)</h1>#si', $content, $m)) ? $m[1] : '';
	$size = $len = $wid = $hei = '';
	if (preg_match('#<th[^>]*?>\s*Dimensions\s*</th>\s*<td[^>]*?>\s*(.*?)\s*</td>#si', $content, $m)) {
		$size = preg_replace('#&times;#', 'x', $m[1]);
		if (preg_match('#^(\S+) x (\S+) x (\S+)\s*(?:in|cm\s*)?$#i', $size, $m)) {
			$len = $m[1];
			$wid = $m[2];
			$hei = $m[3];
		}
	}
	$price = '';
	if (preg_match('#<p class="price">(.*?)</p>#s', $content, $m)) {
		$block = preg_replace('#</?bdi>#', '', $m[1]);
		$block = preg_replace('#<span class="woocommerce-Price-currencySymbol">.*?</span>#s', '', $block);
		if (preg_match('#<span class="woocommerce-Price-amount amount">\s*(?:&nbsp;)?\s*(.*?)\s*</span>#s', $block, $m)) {
			$price = $m[1];
		}
	}
	$avail = (preg_match('#<p class="stock[^"]*?">\s*(.*?)\s*</p>#si', $content, $m)) ? $m[1] : '';
	$desc = (preg_match('#<div[^>]*?id="panel_description"[^>]*?>\s*(.*?)\s*</div>#si', $content, $m)) ? $m[1] : '';
	$future = (preg_match('#"product-soon-label"#', $content)) ? 'Y' : 'N';

	$images = array();
	if (preg_match('#<figure class="woocommerce-product-gallery__wrapper">(.*?)</figure>#s', $content, $m)) {
		$img_block = $m[1];
		$imgs = preg_match_all('#<a href="([^"]*?)">\s*<img#s', $img_block, $m) ? $m[1] : array();
		$n = 1;
		foreach ($imgs as $imgurl) {
			$image_file = "{$sku}_$n.jpg";
			$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
			$res = dl_file($imgurl, $path);
			if ($res) $images[]= $image_file;
			$n++;
			if ($n>5) break;
		}
	}
	$images = array_pad($images, 5, '');

	$row = array($cat, $sku, $title, $price, $avail, $future, $size, $len, $wid, $hei, $desc, $images[0], $images[1], $images[2], $images[3],$images[4] );
	print_r($row);
	$insert_product->execute($row);
	$have[$sku1] = 1;
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
