<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




require_once 'deathbycaptcha.php';
$user = '9998';
$pass = 'uofo';

$up_dir = 'images/nc';
$base = 'https://www.northwoodcollection.com';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_nc_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_nc_products` (`category`, `subcategory`, `sku`, `title`, `qty`, `comments`, `price`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookies/cookies.northwoodcoll.txt');
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookies/cookies.northwoodcoll.txt');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	$log_url = $base;
	$content = http_req($log_url);
	if (!preg_match('#>Logout<#i', $content)) {
		do_login($user, $pass, $content);
	}
	$content = http_req('https://www.northwoodcollection.com/Catalog/PublicCatalog');


	if (!preg_match('#<ul[^>]*?class="list-group">(.*?)</ul>#s', $content, $m)) die('categories not found');
	if (!preg_match_all('#<li.*?>(.*?)</li>#s', $m[1], $m)) die('categories not found');
	$items = $m[1];

	$todo = [];
	$cat = '';
	foreach ($items as $item) {
		$sub = preg_match('#style="margin-left:1em;"#', $item) ? true : false;
		if (!preg_match('#<a[^>]*?href="([^"]*?)">\s*(.*?)\s*</a>#s', $item, $m)) continue;
		$href = $base.html_entity_decode($m[1]);
		$title = $m[2];
		if (preg_match('#(?:all items|new items)#', $title)) continue;
		if (!$sub) {
			$cat = $title;
			$todo[$cat] = ['href' => $href, 'sub' => []];
		} else {
			$todo[$cat]['sub'][$title] = $href;
		}
	}

	foreach ($todo as $cat => $data) {
		echo "Category: $cat\r\n";
		if ($data['sub']) {
			foreach ($data['sub'] as $subcat => $href) {
				echo "\tSubcategory: $subcat\r\n";
				scrape_category($href, $cat, $subcat);
			}
		} else {
			scrape_category($data['href'], $cat, '');
		}
	}

function do_login($user, $pass, $content) {
global $dbc_user, $dbc_pass, $log_url, $ch;

	echo "logging in<br><br>\n";

	if (!preg_match("#'sitekey'\s*:\s*'([^']*?)'#s", $content, $m)) die("no recaptcha\r\n");
	$sitekey = $m[1];

	if (!preg_match('#<input name="__RequestVerificationToken" type="hidden" value="([^"]*?)"#', $content, $m)) die("no token\r\n");
	$token = $m[1];

	$client = new DeathByCaptcha_HttpClient($dbc_user, $dbc_pass);
	$client->is_verbose = true;

	$data = array(
	    'proxytype' => 'HTTP',
	    'googlekey' => $sitekey,
	    'pageurl'   => $log_url,
	);

	$extra = array(
		'type' => 4,
		'token_params' => json_encode($data),
	);

	echo "solving captcha...\r\n";

	$captcha = $client->decode(null, $extra, 60);
	if (!$captcha) die("can't solve\r\n");
    $text = $client->get_text($captcha['captcha']);
	if (!$text) die("can't solve\r\n");

	$params = array(
		'returnUrl' => '',
		'Name' => $user,
		'Password' => $pass,
		'g-recaptcha-response' => $text, 
		'__RequestVerificationToken' => $token,
	);

	$content = http_req('https://www.northwoodcollection.com/Home/Login', $params);
	if (!preg_match('#>Logout<#i', $content)) die('auth failed!');

	curl_setopt($ch, CURLOPT_COOKIELIST, 'FLUSH');
	curl_setopt($ch, CURLOPT_COOKIELIST, 'RELOAD');
	return $content;
}


function scrape_category($url, $cat, $scat) {
global $base;

	$content = http_req($url);
	while(true) {
		if (!preg_match_all('#<a name="[^"]*?" href="(/Catalog/Detailitem[^"]*?)"#s', $content, $m)) break;
		$prods = $m[1];
		foreach ($prods as $href) {
			$prod_url = $base.html_entity_decode($href);
			scrape_product($prod_url, $cat, $scat);
		}

		if (!preg_match('#<form id="pageform"[^>]*?>(.*?)</form>#s', $content, $m)) break;
		preg_match_all('#<input name="([^"]*?)"[^>]*?value="([^"]*?)"#', $m[1], $m);
		$params = array_combine($m[1], $m[2]);
		if (!preg_match('#<button([^>]*?)>\s*Next\s*</button>#s', $content, $m)) break;
		$btn = $m[1];
		if (preg_match('#disabled#', $btn)) break;
		$page = preg_match('#value="([^"]*?)"#', $btn, $m) ? $m[1] : '';
		$params['options.CurrentPage'] = $page;
		$content = http_req('https://www.northwoodcollection.com/Catalog/PublicCatalog', $params);
		echo "\t\t+ page\r\n";
	}
}

function scrape_product($url, $cat, $scat) {
global $insert_product, $up_dir, $base;
	$content = http_req($url);


	if (!preg_match('#<div[^>]*?>\s*(<p>\s*Item\s*\#.*?</div>)#s', $content, $m)) return;
	$block = $m[1];

	$sku = preg_match('#<p>Item\s*\#\s*<span>\s*(.*?)\s*</span>#s', $content, $m) ? $m[1] : '';
	echo "\t\t$sku\r\n";
	$price = preg_match('#<span>Price:\$?(.*?)</span>#', $block, $m) ? $m[1] : '';
	$title = preg_match('#<span>Price:[^<]*?</span></p>\s*<p>\s*(.*?)\s*</p>#', $block, $m) ? $m[1] : '';
	$available = preg_match('#<p>(\d+?) available\s*</p>#s', $block, $m) ? $m[1] : '';
	$imgurl = preg_match('#<img id="dtimage"[^>]*?src="([^"]*?)"#s', $content, $m) ? $m[1] : '';

	$comments = '';

	$image = '';
	if ($imgurl) {
			$image_file = $sku;
			$info = pathinfo($imgurl);
			$image_file.='.'.$info['extension'];
			$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
			$res = dl_file($base.$imgurl, $path);
			if ($res) $image = $image_file;
	}

	$row = array($cat, $scat, $sku, $title, $available, $comments, $price, $image);
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
