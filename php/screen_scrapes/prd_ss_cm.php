<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


require_once 'deathbycaptcha.php';

$user = 'mdrtradinginc@gmail.com';
$pass = 'Mdr856268*';


$up_dir = 'images/cm';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_cm_products`;');
	$insert_product = $pdo_product->prepare("INSERT INTO `prd_ss_cm_products` (`category`, `subcategory`, `item`, `sku`, `price`, `sale_price`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookies/cookies.cmc.txt');
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookies/cookies.cmc.txt');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	$log_url = 'https://cmcwholesale.ca/login/';
	$content = http_req($log_url);
	if (!preg_match('#>\s*Logout\s*<#is', $content)) do_login($content);

	if (!preg_match('#<a[^>]*?>Shop</a>\s*<ul(.*?)</li>\s*</ul>\s*</nav>#s', $content, $m)) die('menu not found');
	$block = html_entity_decode($m[1]);
	if (!preg_match_all('#<li class="menu-item[^>]*?>(<a[^>]*? class="elementor-sub-item">.*?</a>)(?:\s*<ul[^>]*?>(.*?)</ul>)?#s', $block, $m)) die('menu not found');
	$cats = array_combine($m[1], $m[2]);
	foreach ($cats as $main => $subs) {
		if (!preg_match('#<a[^>]*?href="([^"]*?)"[^>]*?>\s*(.*?)\s*</a>#s', $main, $m)) continue;
		$href = $m[1];
		$cat = $m[2];
		if (!preg_match('#product-category#', $href)) continue;
		echo "Category: $cat\r\n";
		if (!preg_match_all('#<li[^>]*?><a[^>]*?href="([^"]*?)"[^>]*?>(.*?)\s*</a>#', $subs, $m)) {
			scrape_category($href, $cat, '');
			continue;
		}
		$scats = array_combine($m[1], $m[2]);
		foreach ($scats as $url => $scat) {
			$scat = preg_replace('#^\W+#', '', $scat);
			echo "\tSubcategory: $scat\r\n";
			scrape_category($url, $cat, $scat);
		}
	}

function do_login($content) {
global $user, $pass, $dbc_user, $dbc_pass, $log_url;

	echo "logging in<br><br>\n";
	$params = [];
	if (preg_match('#(<form method="post".*?</form>)#s', $content, $m)) {
		if (preg_match_all('#(<input[^>]*?>)#s', $m[1], $m)) {
			$fields = $m[1];
			foreach ($fields as $f) {
				$type = preg_match('#type="([^"]*?)"#', $f, $m) ? $m[1] : '';
				$name = preg_match('#name="([^"]*?)"#', $f, $m) ? $m[1] : '';
				$val  = preg_match('#value="([^"]*?)"#', $f, $m) ? $m[1] : '';
				if (in_array($type, ['checkbox', 'submit'])) continue;
				$params[$name] = $val;
			}
		}
	}
	if (!$params || !isset($params['form_id'])) die('form not found');

	$fid = $params['form_id'];
	$params["username-$fid"] = $user;
	$params["user_password-$fid"] = $pass;

	if (!preg_match('#<div[^>]*?data-sitekey="([^"]*?)"#s', $content, $m)) die("no recaptcha\r\n");
	$sitekey = $m[1];


	$client = new DeathByCaptcha_HttpClient($dbc_user, $dbc_pass);
	$client->is_verbose = true;

	$data = array(
	    'proxytype' => 'HTTP',
	    'googlekey' => $sitekey,
	    'pageurl'   => $log_url
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

	$params['g-recaptcha-response'] = $text;
	$params['rememberme'] = 1;

	$content = http_req($log_url, $params);
	if (!(preg_match('/>Logout</', $content))) die('auth failed!');
}

function scrape_category($url, $cat, $scat) {
global $up_dir, $insert_product;

	$next = $url;
	while ($next) {
		$content = http_req($next);
		if (!preg_match_all('#(<li class="product.*?</li>)#s', $content, $m)) die('here');
		$items = $m[1];
		foreach ($items as $item) {
			$item = html_entity_decode($item);
			$img = preg_match('#<img[^>]*?src="([^"]*?)"#s', $item, $m) ? $m[1] : '';	
			$img_full = preg_replace('#-512x512(?=\.)#', '', $img);
			$title = preg_match('#<h2[^>]*?>(.*?)</h2>#s', $item, $m) ? $m[1] : '';
			$sku = preg_match('#data-product_sku="([^"]*?)"#s', $item, $m) ? $m[1] : '';
			echo "$sku\r\n";
			$price = $sale_price = '';
			if (preg_match('#<span class="price">(.*?)</a>#s', $item, $m)) {
				$block = $m[1];
				if (preg_match('#<del[^>]*?>(.*?)</del>(.*?)$#', $block, $m)) {
					$block = $m[1];
					$sale_price = preg_match('#<bdi>\s*<span[^>]*?>[^<]*?</span>(.*?)</bdi>#s', $m[2], $m) ? $m[1] : '';
				}
				$price = preg_match('#<bdi>\s*<span[^>]*?>[^<]*?</span>(.*?)</bdi>#s', $block, $m) ? $m[1] : '';
			}

			$image = '';
			if ($img) {
				$info = pathinfo($img);
				$image_file = "$sku.".$info['extension'];
				$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;

				if (dl_file($img_full, $path)) {
					$image = $image_file;
				} elseif(dl_file($img, $path)) {
					$image = $image_file;
				}

			}

			$row = [$cat, $scat, $title, $sku, $price, $sale_price, $image];
			$insert_product->execute($row);
		}

		if (!preg_match('#<a class="next page-numbers" href="([^"]*?)"#s', $content, $m)) break;
		$next = $m[1];
		echo "\t+page\r\n";
	}
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
