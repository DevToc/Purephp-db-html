<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


require_once 'deathbycaptcha.php';
$dbc_user = 'domaitsolutions@gmail.com';
$dbc_pass = 'Fralor1*';

$up_dir = 'images/iv';
$base = 'https://www.invintage.ca';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_iv_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_iv_products` (`title`, `category`, `subcategory`, `sku`, `description`, `price`, `size`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookies.invintage.txt');
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookies.invintage.txt');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	$log_url = 'https://www.invintage.ca/account/login';
	$content = http_req($log_url);
	if (!preg_match('#>Log Out<#i', $content)) {
		do_login($user, $pass, $content);
	}

	if (!preg_match_all('#<li class="mobile-nav__link"[^>]*?>\s*(<a[^>]*?>.*?</a>\s*(?:<ul class="mobile-nav__sublist">.*?</ul>)?\s*</li>)#s', $content, $m)) die('categories not found');
	foreach ($m[1] as $cblock) {
		if (!preg_match('#<a href="([^"]*?)"[^>]*?>\s*(.*?)\s*<#s', $cblock, $m)) die;
		$url = $m[1];
		$cat = $m[2];
//		if (!preg_match('#Table Top|Wall Decor|Furniture#i', $cat)) continue;
		if (!preg_match('#/collections/#', $url)) continue;
		echo "Category: $cat\n";

		if (preg_match_all('#<li class="mobile-nav__sublist-link">\s*<a href="([^"]*?)">\s*(.*?)\s*</a>#s', $cblock, $m)) {
			$scats = array_combine($m[1], $m[2]);
			foreach ($scats as $url => $scat) {
				echo "\tSubcategory: $scat\n";
				scrape_cat($url, $cat, $scat);
			}
		} else {
			scrape_cat($url, $cat);
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

	$captcha = $client->decode(null, $extra);
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

	curl_setopt($ch, CURLOPT_COOKIELIST, 'FLUSH');
	curl_setopt($ch, CURLOPT_COOKIELIST, 'RELOAD');
}


function scrape_cat($url, $cat, $scat = '') {
global $base;
	while (1) {
		$content = http_req($base.$url);
		if (!preg_match_all('#<div[^>]*?>\s*<a href="([^"]*?)" class="grid-link#s', $content, $m)) break;
		foreach ($m[1] as $href) {
			scrape_item($base.$href, $cat, $scat);
		}
		if (!preg_match('#<li><a href="([^"]*?)" title="Next &raquo;">&rarr;</a></li>#s', $content, $m)) break;
		$url = $m[1];
		echo "+page\n";
	}
}


function scrape_item($url, $cat, $scat = '') {
global $insert_product, $up_dir;

	$content = http_req($url);
	if (!preg_match('#<script[^>]*?id="ProductJson-product-template">\s*(.*?)\s*</script>#s', $content, $m)) return;
	$data = json_decode($m[1], true);
	$title = $data['title'];
	$desc = preg_replace('#</?p>#', '', $data['description']);
	$var = $data['variants'][0];
	$sku = $var['sku'];
	echo "\t\t$sku\n";
	$price = sprintf ('%.2f', $var['price'] / 100);

	$image = '';
	if (count($data['images']) > 0) {
		$imgurl = $data['images'][0];
		if (preg_match('#^/#', $imgurl)) $imgurl = 'https:'.$imgurl;
		$image_file = "$sku.jpg";
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($imgurl, $path);
		if ($res) $image = $image_file;
	}

	$size = '';
	if (count($data['options']) > 0) {
		foreach ($data['options'] as $index => $type) {
			if ($type == 'Size') {
				$size = $var['options'][$index];
				break;
			}
		}
	}
	$row = array($title, $cat, $scat, $sku, $desc, $price, $size, $image);
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

?>
