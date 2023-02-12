<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');



$user = 'mdrtradinginc@gmail.com';
$password = 'Mdr856268*';

$up_dir = 'images/ag';


	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ag_products`;');
	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ag_options`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_ag_products` (`category`, `subcategory`, `item`, `sku`, `qty`, `price`, `dimensions`, `description`, `option`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
	$insert_option = $pdo_product->prepare("INSERT INTO `prd_ss_ag_options` (`sku`, `option_value`, `price_change`) VALUES (?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY, 'localhost:8080'); 

	echo "logging in<br><br>\n";

	$log_url = 'https://apexgroupcanada.com/index.php?route=account/login';
			    
	$content = http_req($log_url);

	$params = array(
		'email' => $user, 
		'password' => $password, 
		'redirect' => 'https://apexgroupcanada.com/index.php?route=common/home',
	);

	$content = http_req($log_url, $params);
	if (!(preg_match('#>My Account<#i', $content))) die('auth failed!');
	if (!preg_match('#<ul id="cat_accordion">(.*?)</ul></div>#s', $content, $m)) die('categories not found');
	$block = html_entity_decode($m[1]);
	if (!preg_match_all('#<li[^>]*?><a[^>]*?href="([^"]*?)">\s*(.*?)\s*</a>\s*(<span class="down"></span><ul>.*?</ul>\s*)?</li>#s', $block, $m)) die('categories not found');

	for ($n = 0; $n < count($m[1]); $n++) {
		$href = $m[1][$n];
		$cat = $m[2][$n];
		echo "\tCategory: $cat\n";
		if (isset($m[3][$n]) && preg_match_all('#<li[^>]*?><a[^>]*?href="([^"]*?)">\s*(.*?)\s*</a></li>#s', $m[3][$n], $sm)) {
			$scats = array_combine($sm[1], $sm[2]);
			foreach ($scats as $href => $scat) {
				echo "\t\tSubcategory: $scat\n";
				scrape_category($href, $cat, $scat);
			}
		} else {
			scrape_category($href, $cat);
		}
	}



function scrape_category($url, $cat, $scat = '') {
	$next = $url.'&limit=100';
	while (1) {
		$content = http_req($next);
		if (!preg_match('#<div[^>]*?class="[^"]*?product-list[^"]*?"[^>]*?>(.*?)$#s', $content, $m)) break;
		if (!preg_match_all('#<h4><a href="([^"]*?)"#s', $m[1], $m)) break;

		foreach ($m[1] as $prod_url) {
			scrape_product(html_entity_decode($prod_url), $cat, $scat);
		}
		if (!preg_match('#<a href="([^"]*?)">&gt;</a>#s', $content, $m)) break;
		$next = html_entity_decode($m[1]);
	}
}




function scrape_product($url, $cat, $scat) {
global $insert_product, $insert_option, $up_dir, $base;
	$content = http_req($url);

	$sku = preg_match('#<li><b>Product Code:</b>\s*(.*?)\s*</li>#s', $content, $m) ? $m[1] : '';

	if (!$sku) return;
	echo "\t\t$sku\n";


	$title = preg_match('#<h1>\s*(.*?)\s*</h1>#s', $content, $m) ? $m[1] : '';
	$avail = preg_match('#<li><b>Availability:</b>\s*<span[^>]*?>(.*?)</span></li>#s', $content, $m) ? $m[1] : '';


	$price_main = '';
	if (preg_match('#<li class="price">\s*(?:\s*<span class="price-old">[^<]*?</span>\s*)?<span class="real">(.*?)</span>#s', $content, $m)) {
		$price_main = $m[1];
	}

	$size = preg_match('#<li><b>Dimension:</b>\s*(.*?)\s*</li>#s', $content, $m) ? $m[1] : '';
	$desc =	preg_match('#<div[^>]*?id="tab-description"[^>]*?>\s*(?:<p[^>]*?>\s*)(.*?)\s*(?:</p>\s*)</div>#s', $content, $m) ? $m[1] : '';



	$img_url = preg_match('#<img[^>]*?itemprop="image"[^>]*?data-zoom-image="([^"]*?)"#s', $content, $m) ? $m[1] : '';
	if (preg_match('#no_image-#', $img_url)) $img_url = '';

	$img = '';
	if ($img_url) {
		$image = $img_url;
		$path = preg_replace('#\?.*?$#', '', $image);
		$info = pathinfo($path);
		$image_file = "$sku.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image, $path);
		if ($res) $img = $image_file;
	}

	$opt_name = '';

	$content = preg_replace('#<!-- .*? -->#si', '', $content);
	if (preg_match('#(<h3>Available Options</h3>(.*?)<ul class="nav nav-tabs">)#s', $content, $m)) {
		$opts = str_replace('&nbsp;', ' ', $m[1]);
		$opts = html_entity_decode($opts);
		if (preg_match('#<label class="control-label"><h4>\s*(.*?)\s*</h4>#s', $opts, $m)) {
			$opt_name = $m[1];
			if (preg_match_all('#<tr>\s*<td>\s*<div class="checkbox">\s*<label>\s*<input[^>]*?>\s*(.*?)\s*</label>\s*</div>\s*</td>\s*<td>\s*(?:\()?(.*?)(?:\))??\s*</td>#si', $opts, $m)) {
				$opts = array_combine($m[1], $m[2]);
				foreach ($opts as $val => $price) {
					$insert_option->execute(array($sku, $val, $price));
				}
			}
		}
	}

	$row = array_map('html_entity_decode', array($cat, $scat, $title, $sku, $avail, $price_main, $size, $desc, $opt_name, $img));
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

	curl_setopt($ch, CURLOPT_POST, false);
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
