<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




require_once 'deathbycaptcha.php';

$user = 'mrmjstrading@gmail.com';
$pass = 'mrmj070468';



$up_dir = 'images/cj';

$base = 'https://www.scrchina.com';


	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_sc_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_sc_products` (`category`, `item`, `sku`, `price`, `price_old`, `add_info`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookies/cookies.scrchina.txt');
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookies/cookies.scrchina.txt');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	$log_url = 'https://www.scrchina.com/account/login';
	$content = http_req($log_url);

	if (!preg_match('#>My Account<#i', $content)) {
		$content = do_login($user, $pass, $content);
	}

	$content = http_req('https://www.scrchina.com/collections/whats-new');

	$skip = array('Monthly Specials', 'What\'s new?', '* Christmas *');

	if (!preg_match('#<div class="row">\s*<div class="span3">\s*<ul(.*?)</ul>#s', $content, $m)) die('categories not found');
	if (!preg_match_all('#<li><a[^>]*?href="([^"]*?)"[^>]*?>\s*(.*?)\s*</a>\s*</li>#s', $m[1], $m)) die('categories not found');
	$cats = array_combine($m[1], $m[2]);
	foreach ($cats as $url => $cat) {
		if (in_array($cat, $skip)) continue;
		echo "Category: $cat\n";
		scrape_category($url, $cat);
	}

function do_login($user, $pass, $content) {
global $dbc_user, $dbc_pass, $log_url, $ch;

	echo "logging in<br><br>\n";

	$params = array(
		'form_type' => 'customer_login', 
		'utf8' => "\xE2\x9C\x93", 
		'customer[email]' => $user, 
		'customer[password]' => $pass, 
	);

	if (preg_match('#siteKey:"([^"]*?)"#s', $content, $m)) {
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

		$captcha = $client->decode(null, $extra);
		if (!$captcha) die("can't solve\r\n");
	    $text = $client->get_text($captcha['captcha']);
		if (!$text) die("can't solve\r\n");
		$params['recaptcha-v3-token'] = $text;
	}

	$content = http_req($log_url, $params);

	if (!(preg_match('#>My Account<#i', $content))) die('auth failed!');

	curl_setopt($ch, CURLOPT_COOKIELIST, 'FLUSH');
	curl_setopt($ch, CURLOPT_COOKIELIST, 'RELOAD');
}


function scrape_category($url, $cat) {
global $base;
	$next = $url;
	while (1) {
		$content = http_req($base.$next);

		if (!preg_match_all('#<div class="details">\s*<a href="([^"]*?)"#s', $content, $m)) break;
		foreach ($m[1] as $prod_url) {
			scrape_product($base.$prod_url, $cat);
		}
  
		if (!preg_match('#<a href="([^"]*?)" title="">Next &raquo;</a>#s', $content, $m)) break;
		$next = $m[1];
	}
}



function scrape_product($url, $cat) {
global $insert_product, $up_dir;

	$content = http_req($url);
	$content = preg_replace('#\s*<!--[^>]*?-->\s*#s', '', $content);

	$sku = preg_match('#<h1 class="title">\s*(.*?)\s*</h1>#s', $content, $m) ? $m[1] : '';

	if (!$sku) return;
	echo "\t\t$sku\n";

	$price = preg_match('#<h2 class="price"[^>]*?>(.*?)</h2>#s', $content, $m) ? $m[1] : '';
	$price_old = preg_match('#<del[^>]*?>\s*(.*?)\s*</del>#si', $price, $m) ? $m[1] : '';
	$price = preg_replace('#<del[^>]*?>.*?</del>#si', '', $price);
	$price = preg_replace('#\s*</?span[^>]*?>\s*#si', '', $price);

	$item = $add_info = '';

	$desc = preg_match('#<div class="description">\s*(.*?)\s*</div>#s', $content, $m) ? $m[1] : '';
	$desc = preg_replace('#\s*<style type="text/css">.*?</style>\s*#si', '', $desc);
	$desc = preg_replace('#<span data-sheets-value=[^>]*?>(.*?)</span>#s', '\\1', $desc);

	if (preg_match('#^(?:<(?:p|span)>)?\s*(.*?)\s*(<.*?)\s*$#si', $desc, $m)) {
		$item = $m[1];
		$add_info = $m[2];
		$add_info = preg_replace('#^\s*</(?:p|span)>\s*#i', '', $add_info);
	} else {
		$item = $desc;
	}

	$img_url = preg_match('#<div class="image featured">\s*<a href="([^"]*?)"[^>]*?>\s*<img#s', $content, $m) ? $m[1] : '';
	if (preg_match('#^//#s', $img_url)) $img_url = 'https:'.$img_url;

		 
	if ($img_url) {
		$image = $img_url;
		$path = preg_replace('#\?.*?$#', '', $image);
		$info = pathinfo($path);
		$safe_sku = preg_replace('/[^A-Za-z0-9\-]/', '-', $sku);
		$image_file = "$safe_sku.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image, $path);
		if ($res) $img = $image_file;
}

	$row = array_map('html_entity_decode', array($cat, $item, $sku, $price, $price_old, $add_info, $img));

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

