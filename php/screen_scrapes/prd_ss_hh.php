<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


require_once 'deathbycaptcha.php';

$user = 'marco_Angelucci@hotmail.com';
$pass = 'Mdr856268*';

$dbc_user = 'domaitsolutions@gmail.com';
$dbc_pass = 'Fralor1*';

$up_dir = 'images/hh';
$base = 'https://www.order.huihome.ca';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";
	
	$pdo_product->exec('TRUNCATE TABLE `prd_ss_hh_products`;');
	$pdo_product->exec('TRUNCATE TABLE `prd_ss_hh_options`;');
	$insert_option = $pdo_product->prepare('INSERT INTO `prd_ss_hh_options`(`category`, `subcategory`, `title`, `description`, `option1`, `option2`, `option3`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);'); 
	$insert_product = $pdo_product->prepare('INSERT INTO `prd_ss_hh_products` (`prod_id`, `var_name`, `title`, `sku`, `value1`, `value2`, `value3`, `price`, `sale_price`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookies/cookies.huihome.txt');
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookies/cookies.huihome.txt');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	$log_url = 'https://www.order.huihome.ca/account/login';
	$content = http_req($log_url);
	if (!preg_match('#>Log Out<#i', $content)) {
		do_login($user, $pass, $content);
	}
	if (!preg_match('#<ul[^>]*?id="main-menu"(.*?)</ul>#s', $content, $m)) die('categories not found');
	if (!preg_match_all('#<li[^>]*?>\s*(.*?)</li>#s', $m[1], $m)) die('categories not found');
	$blocks = $m[1];

	foreach ($blocks as $block) {
		if (!preg_match('#<a href="([^"]*?)"[^>]*?>\s*(.*?)\s*<#s', $block, $m)) continue;
		$href = $m[1];
		$cat = $m[2];
		if ($href == '#') {
			if (!preg_match_all('#<a href="(/[^"]*?)"[^>]*?>\s*(.*?)\s*</a>#', $block, $m)) continue;
			echo "Category: $cat\r\n";
			$scats = array_combine($m[1], $m[2]);
			foreach ($scats as $href => $scat) {
				echo "\tSubcategory: $scat\r\n";
				scrape_category($base.$href, $cat, $scat);
			}
		} else {
			if (!preg_match('#/collections/#', $href)) continue;
			echo "Category: $cat\r\n";
			scrape_category($base.$href, $cat, '');
		}
	}

function do_login($user, $pass, $content) {
global $dbc_user, $dbc_pass, $log_url, $ch;

	echo "logging in<br><br>\n";

	if (!preg_match('#siteKey:"([^"]*?)"#s', $content, $m)) die("no recaptcha\r\n");
	$sitekey = $m[1];

	$client = new DeathByCaptcha_HttpClient($dbc_user, $dbc_pass);
	$client->is_verbose = true;

	$data = array(
	    'proxytype' => 'HTTP',
	    'googlekey' => $sitekey,
	    'pageurl'   => $log_url,
		'action' => 'customer_login',
		'min_score' => '0.3', 
	);

	$extra = array(
		'type' => 5,
		'token_params' => json_encode($data),
	);

	echo "solving captcha...\r\n";

	$captcha = $client->decode(null, $extra, 60);
	if (!$captcha) die("can't solve\r\n");
    $text = $client->get_text($captcha['captcha']);
	if (!$text) die("can't solve\r\n");

	$params = array(
		'form_type' => 'customer_login', 
		'utf8' => "\xE2\x9C\x93", 
		'customer[email]' => $user, 
		'customer[password]' => $pass,
		'recaptcha-v3-token' => $text,
	);

	$content = http_req($log_url, $params);
	if (!preg_match('#>Log Out<#i', $content)) die('auth failed!');
 

}


function scrape_category($url, $cat, $scat) {
global $base;
	$content = http_req($url);
	if (!preg_match_all('#<div[^>]*?>\s*<a href="([^"]*?)" id="product-#s', $content, $m)) return;
	$items = $m[1];
	foreach ($items as $href) {
		scrape_item($base.$href, $cat, $scat);
	}
}

function scrape_item($url, $cat, $scat) {
global $pdo_product, $insert_product, $insert_option;

	$content = http_req($url);
/*
	if (!preg_match('#var meta = (\{.*?\});#s', $content, $m)) die('here');
	$meta = $m[1];
	$data = json_decode($meta, true);
*/

	if (!preg_match('#new Shopify\.OptionSelectors\(\'product-select\', { product:(.*?\})\);#s', $content, $m)) return;
	$prod = $m[1];
	$prod = preg_replace('#, onVariantSelected.*?$#', '', $prod);
	$data = json_decode($prod, true);

	$title = $data['title'];
	echo "\t\t$title\r\n";

	$desc = $data['description'];
	list($opt1, $opt2, $opt3) = array_pad($data['options'], 3, '');

	$img = $data['featured_image'];
	$image = get_image($img);

	$row = [$cat, $scat, $title, $desc, $opt1, $opt2, $opt3, $image];
	$insert_option->execute($row);

	$prod_id = $pdo_product->lastInsertId();

	foreach ($data['variants'] as $var) {
		$var_title = $var['title'];
		$var_name = $var['name'];

		list($var_opt1, $var_opt2, $var_opt3) = array_pad($var['options'], 3, '');

		$var_price = $var['price'] / 100;
		$var_sale_price = $var['compare_at_price'] / 100;
		$var_img = $var['featured_image']['src'];

		$var_price = $var['price'] / 100;
		$var_sale_price = $var['compare_at_price'] / 100;
		$var_img = $var['featured_image']['src'];

		$var_sku = $var['sku'];
		if (!$var_sku) {
			if (preg_match('# - (?:SKU: ?)?([A-Z\d]{10,}) - #', $var_name, $m)) {
				$var_sku = $m[1];
			} else {
				$var_sku = $var['id'];
			}
		}
		$image = get_image($var_img);

		$row = [$prod_id, $var_title, $var_name, $var_sku, $var_opt1, $var_opt2, $var_opt3, $var_price, $var_sale_price, $image];
		$insert_product->execute($row);


	}

}

function get_image($image_url) {
global $up_dir;

	if (!$image_url) return '';
	if (preg_match('#^//#', $image_url)) $image_url = 'https:'.$image_url;

	$name = basename($image_url);
	$name = preg_replace('#\?.*?$#', '', $name);
	$path = "$up_dir/$name";
	if (file_exists($path)) return $name;

	$res = dl_file($image_url, $path);
	if ($res) return $name;
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
