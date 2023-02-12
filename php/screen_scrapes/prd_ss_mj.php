<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');

require_once 'deathbycaptcha.php';

$user = 'mdrtradinginc@gmail.com';
$pass = 'Mdr856268*';


$up_dir = 'images/mj';




	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_mj_products`;');
	$pdo_product->exec('TRUNCATE TABLE `prd_ss_mj_options`;');

	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_mj_products` (`category`, `subcategory`, `title`, `sku`, `price`, `stock`, `description`, `more_info`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
	$insert_option = $pdo_product->prepare("INSERT INTO `prd_ss_mj_options` (`parent_sku`, `sku`, `title`, `price`, `option1_name`, `option1_value`, `option2_name`, `option2_value`, `option3_name`, `option3_value`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");



	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookies/cookies.maryltd.txt');
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookies/cookies.maryltd.txt');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	$log_url = 'https://www.maryltd.com/customer/account/login/';
	$content = http_req($log_url);
	if (!preg_match('#>Sign Out<#i', $content)) {
		$content = do_login($user, $pass, $content);
	}

	if (!preg_match('#<li\s*?class="level0[^>]*?>\s*<a[^>]*?><span>Jewellery</span></a>(.*?)<li\s*?class="level0#s', $content, $m)) die('menu not found');
	$nav = $m[1];
	$nav = preg_replace('#<ul class="level4 submenu">(.*?)</ul>#s', '', $nav);
	$nav = preg_replace('#<ul class="level3 submenu">(.*?)</ul>#s', '', $nav);
	$nav = preg_replace('#<ul class="level2 submenu">(.*?)</ul>#s', '', $nav);
	if (!preg_match_all('#<li\s*?class="level1[^>]*?>\s*(<a href="[^"]*?"[^>]*?>\s*<span>.*?</span>\s*</a>)\s*(<ul[^>]*?>.*?</ul>)?#s', $nav, $m)) die('categories not found');
	$matches = $m;
	for ($i=0; $i < count($matches[1]); $i++) {
		if (!preg_match('#<a href="([^"]*?)"\s*><span>(.*?)</span></a>#', $matches[1][$i], $m)) continue;
		$cat = html_entity_decode($m[2], ENT_COMPAT, 'UTF-8');
		$cat_url = $m[1];		
		echo "Category: $cat\r\n";

		if ($matches[2][$i]) {
			if (!preg_match_all('#<a href="([^"]*?)"\s*><span>(.*?)</span></a>#', $matches[2][$i], $m)) continue;
			$scats = array_combine($m[1], $m[2]);
			foreach ($scats as $scat_url => $scat) {
				$scat = html_entity_decode($scat, ENT_COMPAT, 'UTF-8');

				echo "\tSubcategory: $scat\r\n";
				scrape_category($scat_url, $cat, $scat);
			}
		} else {
			scrape_category($cat_url, $cat);
		}
	}
	echo "\r\nfinished.\r\n";	


function do_login($user, $pass, $content) {
global $dbc_user, $dbc_pass, $log_url, $ch;

	echo "logging in<br><br>\n";
	if (!preg_match('#"sitekey":"([^"]*?)"#s', $content, $m)) die("no recaptcha\r\n");
	$sitekey = $m[1];
	$form_key = (preg_match('#<input name="form_key" type="hidden" value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';

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

	$captcha = $client->decode(null, $extra);
	if (!$captcha) die("can't solve\r\n");
    $text = $client->get_text($captcha['captcha']);
	if (!$text) die("can't solve\r\n");

	$params = array(
		'form_key' => $form_key,
		'login[username]' => $user,
		'login[password]' => $pass,
		'g-recaptcha-response' => $text,
		'recaptcha-validate-' => '', 
		'send' => ''
	);


	$content = http_req('https://www.maryltd.com/customer/account/loginPost/', $params);

	if (!(preg_match('#>Sign Out<#i', $content))) die('auth failed!');

	curl_setopt($ch, CURLOPT_COOKIELIST, 'FLUSH');
	curl_setopt($ch, CURLOPT_COOKIELIST, 'RELOAD');

	return $content;
}



function scrape_category($url, $cat, $scat = '') {

	$content = http_req($url.'?product_list_limit=all');
	if (!preg_match_all('#<a class="product-item-link"\s*href="([^"]*?)">#s', $content, $m)) return;
	$urls = $m[1];

	foreach ($urls as $url) {
		scrape_product($url, $cat, $scat);
	}

}

function scrape_product($url, $cat, $scat) {
global $insert_product, $insert_option, $up_dir;

	$content = http_req($url);

	$sku = preg_match('#<div class="value" itemprop="sku">\s*(.*?)\s*</div>#s', $content, $m) ? $m[1] : '';

	if (!$sku) return;
	echo "\t\t$sku\n";

	$title = preg_match('#<span[^>]*?itemprop="name">\s*(.*?)\s*</span>#s', $content, $m) ? $m[1] : '';
	$price = preg_match('#<meta itemprop="price" content="([^"]*?)"#s', $content, $m) ? $m[1] : '';
	$stock = preg_match('#<div class="stock[^>]*?>\s*(?:<span[^>]*?>)?\s*(.*?)\s*(?:</span>)?\s*</div>#s', $content, $m) ? $m[1] : '';
	$desc = preg_match('#<div class="product attribute overview">\s*<div class="value">\s*(.*?)\s*</div>#s', $content, $m) ? $m[1] : '';

	$add = '';
	if (preg_match('#<caption class="table-caption">More Information</caption>(.*?)</table>#s', $content, $m)) {
		if (preg_match_all('#<th class="col label" scope="row">\s*(.*?)\s*</th>\s*<td class="col data"[^>]*?>\s*(.*?)\s*</td>#s', $m[1], $m)) {
			$points = array_combine($m[1], $m[2]);
			$res = [];
			foreach ($points as $k => $v) {
				$res[]= "$k: $v";
			}
			if ($res) $add = implode("\r\n", $res);
		}
	}

	$img = '';
	if (preg_match('#<img[^>]*?class="gallery-placeholder__image"\s*src="([^"]*?)"#s', $content, $m)) {
		$image = $m[1];
		$path = preg_replace('#\?.*?$#', '', $image);
		$info = pathinfo($path);
		$safe_sku = preg_replace('/[^A-Za-z0-9\-]/', '-', $sku);
		$image_file = "$safe_sku.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image, $path);
		if ($res) $img = $image_file;
	}

	$row = array_map('html_entity_decode', array($cat, $scat, $title, $sku, $price, $stock, $desc, $add, $img));
	$insert_product->execute($row);

	if (preg_match('#<script type="text/x-magento-init">(\s*\{\s*"\#product_addtocart_form".*?)</script>#s', $content, $m)) {
		$data = json_decode($m[1], true);
		if (!isset($data['#product_addtocart_form']['configurable'])) return;
		$data = $data['#product_addtocart_form']['configurable']['spConfig'];
		foreach ($data['index'] as $id => $opts) {
			$name = $data['dynamic']['name'][$id]['value'];
			$csku = $data['dynamic']['sku'][$id]['value'];
			$options = [];
			foreach ($opts as $k => $v) {
				$type = $data['attributes'][$k]['label'];
				$value = '';
				foreach ($data['attributes'][$k]['options'] as $o) {
					if ($o['id'] == $v) {
						$value = $o['label'];
						break;
					}
				}
				$options[] = $type;
				$options[] = $value;
			}
			$options = array_pad($options, 6, '');
			$price = $data['optionPrices'][$id]['finalPrice']['amount'];
			$img = '';
			if (isset($data['images'][$id])) {
				$image = $data['images'][$id][0]['full'];
				$path = preg_replace('#\?.*?$#', '', $image);
				$info = pathinfo($path);
				$safe_sku = preg_replace('/[^A-Za-z0-9\-]/', '-', $csku);
				$image_file = "$safe_sku.".$info['extension'];
				$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
				$res = dl_file($image, $path);
				if ($res) $img = $image_file;
			}
			$row = array_merge([$sku, $csku, $name, $price], $options, [$img]);
			$insert_option->execute($row);
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

