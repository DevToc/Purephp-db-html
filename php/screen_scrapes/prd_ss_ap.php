<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


$user = 'mrmjstrading@gmail.com';
$password = 'mrmj070468';

$up_dir = 'images/ap';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ap_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_ap_products` (`category`, `subcategory`, `sku`, `qty`, `out_of_stock_date`, `price`, `description`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	echo "logging in<br><br>\n";

	$log_url = 'https://www.apexelegance.com/login.php';
	http_req($log_url);

	$params = array(
		'login_email' => $user,
		'login_pass' => $password
	);

	$content = http_req($log_url.'?action=check_login', $params);
	if (!(preg_match('/>\s*Sign ?out\s*</i', $content))) die('auth failed!');

	if (!preg_match('#<h3>Categories</h3>\s*(.*?)</div>#si', $content, $m)) die('categories not found');
	$str = $m[1];
	$blocks = array();
	$pos = $level = $start = 0;
	while (preg_match('#<(/?li)#', $str, $m, PREG_OFFSET_CAPTURE, $pos)) {
		list($type, $offset) = $m[1];
		if ($type == 'li') {
			if ($level == 0) $start = $offset;
			$level++;
		} else {
			$level--;
			if ($level == 0) $blocks[] = array($start-1, $offset-$start);
		}
		$pos = $offset;
	}

	foreach ($blocks as $blk) {
		list($off, $length) = $blk;
		$block = substr($str, $off, $length);
		if (!preg_match('#<li[^>]*?>\s*<a href="(.*?)">(.*?)</a>#', $block, $m)) continue;
		$cat_url = $m[1];
		$cat_name = $m[2];
		if ($pos = strpos($block, '<ul>')) {
			$block = substr($block, $pos+1);
			$block = preg_replace('#<ul>.*?</ul>\s*#s', '', $block);
			if (!preg_match_all('#<a href="(.*?)">(.*?)</a>#si', $block, $m)) continue;
			$subcats = array_combine($m[1], $m[2]);
			foreach ($subcats as $url => $name) {
				scrape_category($url, $cat_name, $name);
			}
		} else {
			scrape_category($cat_url, $cat_name);
		}
	}


function scrape_category($url, $cat, $subcat = '') {
	$page = 1;
	echo "Category: $cat\n";

	while (true) {              
		$content = http_req($url."?sort=alphaasc&page=".$page);
		if (!preg_match_all('#<a href="([^"]*?)" class="\s*?pname\s*?"#', $content, $m)) break;
		foreach ($m[1] as $purl) {
			scrape_product($purl, $cat, $subcat);
		}
		if (!preg_match('#<a[^>]*?class="nav-next"#', $content)) break;
		echo "+page\n";
		$page++;
	}
}

function scrape_product($url, $cat, $subcat) {
global $insert_product, $up_dir;

	$content = http_req($url);
	$sku = preg_match('#<h1>\s*([^<]*?)\s*</h1>#s', $content, $m) ? $m[1] : '';
	if (!$sku) return;

	echo "$sku\n";

	$desc = preg_match('#<h2[^>]*?>Product Description</h2>\s*(?:<div[^>]*?>\s*)*\s*(.*?)\s*</div>#s', $content, $m) ? $m[1] : '';
	$desc = preg_replace('/<p>/', "\r\n", $desc);
	$desc = preg_replace('/<\/p>/', '', $desc);
	$desc = strip_tags($desc);
	$desc = trim($desc);

	$price = '';
	if (preg_match('#<em[^>]*?ProductPrice[^>]*?>(.*?)</em>#si', $content, $m)) {
		$str = preg_replace('#<strike>.*?</strile>#si', '', $m[1]);
		$price = preg_match('#\$(.*?)\s*(?:ea|$)#s', $str, $m) ? $m[1] : '';
	}

	$stock = preg_match('#<meta property="og:availability" content="instock"#s', $content, $m) ? 'Y' : 'N';
	$stockdate = preg_match('#<div class="Label">OUT OF STOCK:</div>\s*<div class="Value">\s*(.*?)\s*</div>#s', $content, $m) ? $m[1] : '';

	$img = '';
	if (preg_match('#<div[^>]*?class="ProductThumbImage"[^>]*?>(.*?)</div>#si', $content, $m)) {
		$image = preg_match('#src="([^"]*?)"#s', $m[1], $m) ? $m[1] : '';
		if (!$image) $image = preg_match('#([^"]*?)#s', $m[1], $m) ? $m[1] : '';
		$path = preg_replace('#\?.*?$#', '', $image);
		$info = pathinfo($path);
		$sku_safe = preg_replace('# .*?$#', '', $sku);
		$image_file = "$sku_safe.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image, $path);
		if ($res) $img = $image_file;
	}

	$row = array_map('html_entity_decode', array($cat, $subcat, $sku, $stock, $stockdate, $price, $desc, $img));
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
