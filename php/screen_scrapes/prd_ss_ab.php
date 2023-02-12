<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');



$user = 'mdrtradinginc@gmail.com';
$password = 'Mdr856268*';

$up_dir ='images/ab';



	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";




	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ab_products`;');



	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_ab_products` (`category`, `subcategory`, `title`, `description`, `sku`, `qty`, `moq`, `upc`, `size`, `colour`, `material`, `price`, `price_per`, `prev_price`, `image1`, `image2`, `image3`, `image4`, `image5`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	echo "logging in<br><br>\n";

	$content = http_req('https://www.abbottcollection.com/');

	$params = array(
		'user_name' => $user,
		'user_password' => $password,
		'login' => 'true'
	);

	$headers_def = array('Content-Type: application/json;charset=utf-8', 'Origin: https://www.abbottcollection.com');

	$content = http_req('https://www.abbottcollection.com/wp-json/abbott/v1/accountLogin', json_encode($params), $headers_def);
	if (!(preg_match('/"account_name"/i', $content))) die('auth failed!');

	$content = http_req('https://www.abbottcollection.com/');
	if (!preg_match('#<ul class="menu categories">\s*(.*?)\s*</li>\s*<!-- Mobile product menu -->#s', $content, $m)) die('no menu');
	if (!preg_match_all('#<li[^>]*?>\s*(<a class="drawer-trigger" href="[^"]*?">.*?</a>)\s*(<div class="subcat-dropdown"[^>]*?>\s*<ul>(.*?)</ul>\s*</div>\s*)?</li>#s', $m[1], $m)) die('no categories');
	$cats = array_combine($m[1], $m[2]);
	foreach ($cats as $catblock => $scatblock) {
		if (!preg_match('#<a[^>]*?href="([^"]*?)"[^>]*?>(.*?)</a>#s', $catblock, $m)) continue;
		$cat_href = $m[1];
		$cat = $m[2];
		echo "Category: $cat\r\n";
		if ($scatblock && preg_match_all('#<li[^>]*?>\s*<a href="([^"]*?)"[^>]*?>(.*?)</a>#s', $scatblock, $m)) {
			$scats = array_combine($m[1], $m[2]);
			foreach ($scats as $scat_href => $scat) {
				echo "\tSubcategory: $scat\r\n";
				scrape_category($scat_href, $cat, $scat);
			}
		} else {
			scrape_category($cat_href, $cat, '');
		}
	}





function scrape_category($url, $cat, $scat) {
global $headers_def;
	$content = http_req($url);
	if (!preg_match('#ng-init="[^"]*?termId = (\d+)[^"]*?"#s', $content, $m)) {
		echo "category id not found\r\n";
		return;
	}
	$term_id = $m[1];

	$offset = 0;
	while(true) {
		$post_str = '{"term_id":'.$term_id.',"limit":64,"offset":'.$offset.',"order":"all","sort":"ASC"}';
		$content = http_req('https://www.abbottcollection.com/wp-json/abbott/v1/catalogueProducts', $post_str, $headers_def);
		$data = json_decode($content, true);

		if (!$data) {
			echo "unexpected response\r\n";
			break;
		}

		foreach ($data['products'] as $product) {
			scrape_product($product, $product['sku'], $cat, $scat);
		}

		$offset += 64;
		if ($offset >= $data['total']) break;
	}
}

function scrape_product($prod, $sku, $cat, $scat) {
global $insert_product, $up_dir, $headers_def;

	$sku = $prod['sku'];
	echo "\t\t$sku\n";
	$prod_id = $prod['id'];
	if (!$prod_id) return;
	
	$content = http_req('https://www.abbottcollection.com/wp-json/abbott/v1/loadFile?route_to=modal-product', json_encode(array('product_id' => $prod_id)), $headers_def);
	$content = json_decode($content, true);
	if (!$content) {
		echo "unexpected response\r\n";
		return;
	}

	$content2 = http_req('https://www.abbottcollection.com/wp-json/abbott/v1/getProductById', json_encode(array('id' => $prod_id)), $headers_def);
	$data = json_decode($content2, true);
	if (!$data) {
		echo "unexpected response\r\n";
		return;
	}

	$desc = $data['post_content'];
	$title = $data['post_title'];

	$future = '';
	if (preg_match_all("#<div ng-if=\"account.accountLoggedIn && '([^']*?)' == '([^']*?)'\" class=\"ng-cloak [^\"]*? stock-status\">\s*(.*?)\s*(?:<img[^>]*?>)?\s*</div>#s", $content, $m)) {
		$stocks = array_combine($m[2], $m[3]);
		if (isset($stocks[$data['stock_status']])) {
			$future = $stocks[$data['stock_status']];
		}
	} else {
		@$future = $data['stock_status'];
		if (isset($data['stock_timing'])) $future.=' '.$data['stock_timing'];
	}

	$moq = $data['minimum'];

	$meta = array();
	if (preg_match('#<section class="product-meta">(.*?)</section>#s', $content, $m)) {
		if (preg_match_all('#<tr[^>]*?>\s*<td[^>]*?>(.*?)</td>\s*<td[^>]*?>\s*(.*?)\s*</td>\s*</tr>#s', $m[1], $m)) {
			$meta = array_combine($m[1], $m[2]);
		}
	}
	$upc = isset($meta['UPC']) ? $meta['UPC'] : '';
	$size = isset($meta['Size']) ? $meta['Size'] : '';
	$colour = isset($meta['Colour']) ? $meta['Colour'] : '';
	$material = isset($meta['Material']) ? $meta['Material'] : '';

	$price = '';
	if (isset($data['price_list'])) {
		$price = $data['price_list']['pricepoints'][0];
	} else {
		$price = $prod['price'];
	}

	$price_per = $prod['sold_in'];
	$price_prev = $data['original_price_cad'] ? $data['original_price_cad'] : '';

	$images = array();
	if (preg_match_all('#<img[^>]*?class="product-image"[^>]*?src="([^"]*?)"#s', $content, $m)) {
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
			if ($n>5) break;
		}
	}
	$images = array_pad($images, 5, '');

	$row = array($cat, $scat, $title, $desc, $sku, $future, $moq, $upc, $size, $colour, $material, $price, $price_per, $price_prev, $images[0], $images[1], $images[2], $images[3], $images[4]);
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
