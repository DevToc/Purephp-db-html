<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




$email = 'mdrtradinginc@gmail.com';
$pass  = 'Mdr856268*';

$up_dir = 'images/va';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_va_products`;');
	$pdo_product->exec('TRUNCATE TABLE `prd_ss_va_options`;');

	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_va_options` (`category`, `subcategory`, `title`, `info`) VALUES (?, ?, ?, ?);");
	$update_product = $pdo_product->prepare("UPDATE `prd_ss_va_options` SET `image1` = ?, `image2` = ?, `image3` = ?, `image4` = ?, `image5` = ? WHERE `id` = ?;");
	$insert_option = $pdo_product->prepare("INSERT INTO `prd_ss_va_products` (`parent`, `size`, `sku`, `price`, `qty`, `next_avail_date`) VALUES (?, ?, ?, ?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:98.0) Gecko/20100101 Firefox/98.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY, 'localhost:8080');

	echo "<pre>\nlogging in\n";

	http_req('https://www.vianainc.com/login');

	$params = array(
		'vendor_email_id' => $email,
		'password'        => $pass,
	);

	curl_setopt($ch, CURLOPT_HEADER, true);
	$content = http_req('https://www.vianainc.com/home/checkAuth', $params);
	if (!(preg_match('#Refresh: 0;url=https://www.vianainc.com/vendors#i', $content))) die('auth failed!');
	curl_setopt($ch, CURLOPT_HEADER, false);

	$content = http_req('https://www.vianainc.com/vendors');

	if (!preg_match_all('#<button[^>]*?>\s*<a[^>]*?>\s*([^<]*?)\s*</a>\s*</button>\s*((?:<ul.*?</ul>\s*)+)#s', $content, $m)) die('menu not found');
	$tabs = array_combine($m[1], $m[2]);
	foreach ($tabs as $cat => $block) {
		if (preg_match('#LOOKBOOK#i', $cat)) continue;
		echo "Category: $cat\r\n";
		if (!preg_match_all('#<li[^>]*?>\s*<a[^>]*?href="([^"]*?)"[^>]*?data-row[^>]*?>\s*(.*?)\s*</a>#s', $block, $m)) die('here');
		$scats = array_combine($m[2], $m[1]);
		foreach ($scats as $scat => $url) {
			if (preg_match('#\bALL\b#', $scat)) continue;
			echo "\tSubcategory: $scat\r\n";
			scrape_category($url, $cat, $scat);
		}
	}

	echo "\n finished.\n";


function scrape_category($url, $cat, $scat = '') {
	$content = http_req($url);
	if (!preg_match_all('#<div class="figure">\s*<a[^>]*?href="([^"]*?)"#s', $content, $m)) {
		echo "\t\tno items\r\n";
	}
	foreach ($m[1] as $href) {
		scrape_item($href, $cat, $scat);
	}
}

function scrape_item($url, $cat, $scat) {
global $up_dir, $pdo_product, $insert_product, $update_product, $insert_option;
	$content = http_req($url);

	$title = preg_match('#<h2 class="productName[^"]*?">\s*(.*?)\s*</h2>#s', $content, $m) ? $m[1] : '';
	$info = preg_match('#(?:</h4>)\s*?<hr />\s*(.*?)\s*(?:<!--|<p class="text-gray|<p class="mb-1">AVAILABLE STOCK:)#s', $content, $m) ? $m[1] : '';
	$info = preg_replace('#\r\n\r\n#', "\r\n", $info);
	$info = html_entity_decode($info, ENT_QUOTES);

	$insert_product->execute([$cat, $scat, $title, $info]);
	$id = $pdo_product->lastInsertId();
	$iid = sprintf('%04d', $id);
	echo "\t\t$iid - $title\r\n";

	$images = array();
	if (preg_match_all('#<a data-image="([^"]*?)"#s', $content, $m)) {
		$imgs = $m[1];
		$n = 1;
		foreach ($imgs as $imgurl) {
			$image_file = preg_replace('#/#', '_', $iid);
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
	$update_product->execute([$images[0], $images[1], $images[2], $images[3], $images[4], $id]);

	if (preg_match('#<select name="pro_size"(.*?)</select>#s', $content, $m)) {
		if (preg_match_all('#(<option[^>]*?>)#', $m[1], $m)) {
			$options = $m[1];
			foreach ($options as $opt) {
				$price = preg_match('#value="([^"]*?)"#s', $opt, $m) ? $m[1] : '';
				$sku = preg_match('#data-sku="([^"]*?)"#s', $opt, $m) ? $m[1] : '';
				$size = preg_match('#data-size="(.*?)" data-qty#s', $opt, $m) ? $m[1] : '';
				$qty = preg_match('#data-qty="([^"]*?)"#s', $opt, $m) ? $m[1] : '';
				$eta = preg_match('#data-eta="(.*?)">#s', $opt, $m) ? $m[1] : '';
				$insert_option->execute([$id, $size, $sku, $price, $qty, $eta]);

			}
		}

	}
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
