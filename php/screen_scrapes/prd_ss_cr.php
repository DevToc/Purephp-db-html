<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');



$up_dir = 'images/cr';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}

	echo "<pre>\n";

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_cr_products`;');
	$pdo_product->exec('TRUNCATE TABLE `prd_ss_cr_options`;');
	$insert_product = $pdo_product->prepare('INSERT INTO `prd_ss_cr_products` (`category`, `subcategory`, `title`, `sku`, `feature`, `description`, `additional`, `option1`, `option2`, `option3`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
	$insert_option = $pdo_product->prepare('INSERT INTO `prd_ss_cr_options` (`prod_id`, `title`, `sku`, `value1`, `value2`, `value3`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?);');



	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	$content = http_req('https://www.creative-homedecor.com/shop/');

	if (!preg_match('#(<aside.*?</aside>)#s', $content, $m)) die('no categories');
	if (!preg_match_all('#(<li.*?>.*?(?:<ul[^>]*?>.*?</ul>\s*)?</li>)#s', $m[1], $m)) die('no categories');
	$items = $m[1];
	foreach ($items as $block) {
		if (!preg_match('#<li[^>]*?><a href="([^"]*?)">\s*(.*?)\s*</a>#s', $block, $m)) continue;
		$href = $m[1];
		$cat = $m[2];
		echo "Category: $cat\r\n";
		if (preg_match('#<ul class=\'children\'>(.*?)</ul>#s', $block, $m)) {
			preg_match_all('#<li[^>]*?><a href="([^"]*?)">\s*(.*?)\s*</a>#', $m[1], $m);
			$scats = array_combine($m[1], $m[2]);
			foreach ($scats as $href => $scat) {
				echo "\tSubcategory: $scat\r\n";
				scrape_category($href, $cat, $scat);
			}
		} else {
			scrape_category($href, $cat, '');
		}
	}

function scrape_category($url, $cat, $scat) {

	$next = $url.'page/1/';
	while (1) {
		$content = http_req($next);
		if (!preg_match_all('#<h3 class="product-title"><a href="([^"]*?)">#s', $content, $m)) break;
		$items = $m[1];
		foreach ($items as $href) {
			scrape_item($href, $cat, $scat);
		}
		if (!preg_match('#<link rel="next" href="([^"]*?)"#s', $content, $m)) break;
		$next = $m[1];
		echo "\t+page\r\n";
	}
}

function scrape_item($url, $cat, $scat) {
global $pdo_product, $insert_product, $insert_option, $up_dir;

	if (!preg_match('#product/([^/]*?)/#', $url, $m)) return;
	$href = $m[1];
	echo "\t\t$href\r\n";

	$content = http_req($url);

	$variations = [];
	$options = ['', '', ''];
	if (preg_match('#<table class="variations"(.*?)</table>#s', $content, $m)) {
		$table = $m[1];
		$options = [];
		$opt_names = [];
		if (preg_match('#<form class="variations_form cart"[^>]*?data-product_variations="([^"]*?)"#s', $content, $m)) {
			$val = html_entity_decode($m[1]);
			$vars = json_decode($val, true);
		}
		if (preg_match_all('#<td class="label"><label[^>]*?>\s*(.*?)\s*</label>\s*</td>\s*<td class="value">\s*(<select.*?</select>)#s', $table, $m)) {
			for ($n = 0; $n < count($m[1]); $n++) {
				$name = preg_match('#<select[^>]*?name="([^"]*?)"#', $m[2][$n], $mm) ? $mm[1] : '';
				preg_match_all('#<option value="([^"]+?)"[^>]*?>\s*(.*?)\s*</option>#s', $m[2][$n], $mm);
				$keys = array_map('html_entity_decode', $mm[1]);
				$vals = array_map('html_entity_decode', $mm[2]);
				$options[$name] = array_combine($keys, $vals);
				$opt_names[$name] = $m[1][$n];
			}
		}
		foreach ($vars as $var) {
			if (!$var['variation_is_visible']) continue;
			$opt_vals = [];
			$attrs = $var['attributes'];
			foreach ($attrs as $k => $v) {
				if (!$v) {
					$val = array_values($options[$k])[0];
				} else {
					if (!isset($options[$k][$v])) continue 2;
					$val = $options[$k][$v];
				}
				$opt_vals[] = $val;
			}
			$opt_vals = array_pad($opt_vals, 3, '');
			$title = $var['image']['title'];
			$image = $var['image']['url'];
			$sku = $var['sku'];
			if (!$sku) {
				$sku = "$href-".$var['variation_id'];
			}
			$image_file = '';
			if ($image) {
				$image_file = get_image($image, "$sku.jpg");
			}
			$variations[] = [$title, $sku, $opt_vals[0], $opt_vals[1], $opt_vals[2], $image_file];
		}
		$options = array_pad(array_values($opt_names), 3, '');
	}

	$title = preg_match('#<h1[^>]*?>\s*(.*?)\s*</h1>#s', $content, $m) ? $m[1] : '';

	$desc = $features = $add = '';
	if (preg_match('#<div class="woocommerce-product-details__short-description">(.*?)</div>#s', $content, $m)) {
		$features = $m[1];
		$features = preg_replace('#\s*<p><strong>Feature</strong></p>\s*#', '', $features);
		$features = preg_replace('#</?(?:span|div)[^>]*?>#', '', $features);
		$features = trim($features);
	}
	if (preg_match('#<div[^>]*?id="tab-description"[^>]*?>\s*(?:\s*<div[^>]*?>\s*){1,}(.*?)</div>#s', $content, $m)){
		$desc = trim($m[1]);
		$desc = preg_replace('#</?(?:span|div)[^>]*?>#', '', $desc);
	}
	if (preg_match('#<div class="woocommerce-product-attributes shop_attributes">(.*?)</div>\s*</div>\s*</div>\s*</div>#s', $content, $m)) {
		if (preg_match_all('#<div class="attr-title woocommerce-product-attributes-item__label">(.*?)</div>\s*<div class="attr-excerpt woocommerce-product-attributes-item__value">(.*?)</div>#s', $m[1], $m)) {
			$attrs = array_combine($m[1], $m[2]);
			$vals = [];
			foreach ($attrs as $k => $v) {
				$vals[] = "$k: $v";
			}
			$add = implode("\r\n", $vals);
		}
	}
	$sku = '';
	if (preg_match('#data-product_sku="([^"]+?)"#', $content, $m)) {
		$sku = $m[1];
	} else {
		$pid = preg_match('#<input type="hidden" name="product_id" value="([^"]*?)"#s', $content, $m) ? $m[1] : '';
		$sku = "$href-$pid";
	}

	$image = preg_match('#<img[^>]*?class="single-product-img wp-post-image"[^>]*?data-src="([^"]*?)"#s', $content, $m) ? $m[1] : '';
	$image_file = '';
	if ($image) {
		$image_file = get_image($image, "$sku.jpg");
	}

	$image = '';
	$row = [$cat, $scat, $title, $sku, $features, $desc, $add, $options[0], $options[1], $options[2], $image_file];
	$insert_product->execute($row);
	$prod_id = $pdo_product->lastInsertId();

	foreach ($variations as $var) {
		array_unshift($var, $prod_id);
		$insert_option->execute($var);

	}
}

function get_image($image_url, $fname) {
global $up_dir;

	if (!$image_url) return '';

	$path = "$up_dir/$fname";
	if (file_exists($path)) return $fname;

	$res = dl_file($image_url, $path);
	if ($res) return $fname;
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
	$success = ($info['http_code'] == 200) ? 1 : 0;

	if ($success) {
		$fh = fopen($fname, 'wb');
	    if ($fh == FALSE) {
			echo 'cant open file';
			return;
		}
		fwrite($fh, $content);
		fclose($fh);
	}
	return $success;
}
