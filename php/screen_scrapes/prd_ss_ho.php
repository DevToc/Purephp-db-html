<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


$user = 'mdrtradinginc@gmail.com';
$password = 'Mdr856268*';

$up_dir = 'images/ho';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ho_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_ho_products` (`category`, `subcategory`, `sku`, `price`, `title`, `description`, `image1`, `image2`, `image3`, `image4`, `image5`, `image6`, `image7`, `image8`, `image9`, `image10`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
#	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	echo "logging in<br><br>\n";

	$content = http_req('https://homesteadheath.com/login.php');

	if (!preg_match('#"csrf_token":"([^"]*?)"#s', $content, $m)) die('no token');
	$token = $m[1];

	$params = array(
		'login_email' => $user, 
		'login_pass' => $password, 
		'authenticity_token' => $token, 
	);

	$content = http_req('https://homesteadheath.com/login.php?action=check_login', $params);
	if (!(preg_match('/>Sign out</i', $content))) die('auth failed!');

	if (!preg_match('#<ul class="navPages-list">(.*?)</ul>\s*<ul class="navPages-list navPages-list--user">#s', $content, $m)) die('categories not found');
	if (!preg_match_all('#<li class="navPages-item">\s*(<a[^>]*?>\s*.*?(?:<i class="icon[^>]*?>.*?</i>)?</a>)\s*(<div[^>]*?>(.*?)</div>)?#s', $m[1], $m)) die('categories not found');
	$cats = array_combine($m[1], $m[2]);
	foreach ($cats as $main => $sub) {
		if (!preg_match('#<a[^>]*?href="([^"]*?)"[^>]*?>\s*(.*?)(?:\s*<i class="icon.*?</i>)?\s*</a>#s', $main, $m)) continue;
		$href = $m[1];
		$cat = $m[2];
		if (preg_match('#View All#i', $cat)) continue;
		echo "Category: $cat\r\n";
		if ($sub && preg_match_all('#<li class="navPage-subMenu-item">\s*<a[^>]*?href="([^"]*?)"[^>]*?>\s*(.*?)(?:\s*<i class="icon.*?</i>)?\s*</a>#s', $sub, $m)) {
			$subcats = array_combine($m[1], $m[2]);
			$cc = 0;
			foreach ($subcats as $href => $scat) {
				if ($cc++ == 0 && preg_match('#^All #', $scat)) continue;
				echo "\tSubcategory: $scat - $href\r\n";
				scrape_category($href, $cat, $scat);
			}
		} else {
			scrape_category($href, $cat, '');
		}
	}

function scrape_category($url, $cat, $scat) {
global $base;

	$next = $url;
	while(true) {
		$content = http_req($next);
		if (!preg_match_all('#<h4 class="card-title">\s*<a href="([^"]*?)"#s', $content, $m)) break;
		$prods = $m[1];
		foreach ($prods as $prod_url) {
			scrape_product($prod_url, $cat, $scat);
		}
		if (!preg_match('#<link rel="next" href="([^"]*?)"#s', $content, $m)) break;
		$next = html_entity_decode($m[1]);
	}
}

function scrape_product($url, $cat, $scat) {
global $insert_product, $up_dir;

	$content = http_req($url);

	$sku = preg_match('#<h1[^>]*?itemprop="name"[^>]*?>(.*?)</h1>#s', $content, $m) ? $m[1] : '';
	if (!$sku) return;

	echo "\t\t$sku\r\n";

	$price = preg_match('#<meta itemprop="price" content="([^"]*?)">#s', $content, $m) ? $m[1] : '';
	$title = $desc = '';
	if (preg_match('#<div class="tab-content is-active" id="tab-description">\s*(.*?)\s*(?: <!--[^>]*?-->\s*)?</div>#s', $content, $m)) {
		$desc = $m[1];
		$title = preg_match('#^<p>(.*?)</p>#s', $desc, $m) ? strip_tags($m[1]) : '';
	}


	$images = array();
	if (preg_match_all('#<a\s*class="productView-thumbnail-link"\s*href="([^"]*?)(\?c=\d)?"#s', $content, $m)) {
		$imgs = $m[1];
		$n = 1;
		foreach ($imgs as $imgurl) {
			$image_file = preg_replace('#/#', '_', $sku);
			if ($n > 1) $image_file.="_$n";
			$path = preg_replace('#\?.*?$#', '', $imgurl);
			$info = pathinfo($path);
			$image_file.='.'.$info['extension'];
			$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
			$res = dl_file($imgurl, $path);
			if ($res) $images[]= $image_file;
			$n++;
			if ($n > 10) break;
		}
	}
	$images = array_pad($images, 10, '');


	$row = array($cat, $scat, $sku, $price, $title, $desc, $images[0], $images[1], $images[2], $images[3], $images[4], $images[5], $images[6], $images[7], $images[8], $images[9]);
	$row = array_map('html_entity_decode', $row);
	$insert_product->execute($row);
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
