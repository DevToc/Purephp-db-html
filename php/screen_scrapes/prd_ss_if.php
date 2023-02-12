<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


$up_dir = 'images/if';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}

	echo "<pre>\n";

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_if_products`;');
	$insert_product = $pdo_product->prepare('INSERT INTO `prd_ss_if_products` (`category`, `subcategory`, `sku`, `dimensions`, `description`, `material`, `qty`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	$content = http_req('https://www.ifdc.ca/');

	if (!preg_match('#(<nav.*?</nav>)#s', $content, $m)) die('categories not found');
	if (!preg_match_all('#<p[^>]*?>\s*(.*?)\s*</p>\s*</div>\s*</div>\s*</div>\s*(<ul aria-hidden="true".*?</ul>)#s', $m[1], $m)) die('categories not found');

	$cats = array_combine($m[1], $m[2]);
	foreach ($cats as $cat => $block) {
		echo "Category: $cat\r\n";
		$block = html_entity_decode($block);
		if (!preg_match_all('#<li><a[^>]*?href="([^"]*?)"[^>]*?>\s*(.*?)\s*</a>#s', $block, $m)) continue;
		$scats = array_combine($m[1], $m[2]);
		foreach ($scats as $href => $scat) {
			echo "\tSubcategory: $scat\r\n";
			scrape_category($href, $cat, $scat);
		}
	}

function scrape_category($url, $cat, $scat) {

	$content = http_req("$url?page=1000");

	if (!preg_match('#<script type="application/json" id="wix-warmup-data">(.*?)</script>#s', $content, $m)) die('no page data');
	$data = json_decode($m[1], true);
	if (!$data) die('no json data');

	$adata = $data['appsWarmupData'];
	$adata = $adata[array_keys($adata)[0]];
	$adata = $adata[array_keys($adata)[0]];
	$prods = $adata['catalog']['category']['productsWithMetaData'];
	foreach ($prods['list'] as $prod) {
		$avail = $prod['ribbon'];
		$href = 'https://www.ifdc.ca/product-page/'.$prod['urlPart'];
		$image = '';
		if ($prod['media']) {
			$m = $prod['media'][0];
			$image = "https://static.wixstatic.com/media/{$m['url']}/v1/fill/w_{$m['width']},h_{$m['height']},al_c,q_85/{$m['url']}";
		}
		scrape_item($href, $cat, $scat, $avail, $image);
	}
}

function scrape_item($url, $cat, $scat, $avail, $image_url) {
global $conn, $insert_product, $up_dir;
	$content = http_req($url);

	if (!preg_match('#<h1[^>]*?>\s*(.*?)\s*</h1>#s', $content, $m)) return;
	$sku = html_entity_decode($m[1]);
	echo "\t\t$sku\r\n";

	$content = html_entity_decode($content);
	$dimensions = $description = $material = '';
	if (preg_match_all('#<h2[^>]*?>\s*(.*?)\s*</h2><div[^>]*?><svg.*?</svg>\s*</div>\s*</button>\s*<div[^>]*?>\s*<div[^>]*?>\s*<div[^>]*?>\s*<div data-hook="info-section-description"[^>]*?>\s*(.*?)\s*</div>#s', $content, $m)) {
		$sections = array_combine($m[1], $m[2]);
		if (isset($sections['Dimensions'])) $dimensions = $sections['Dimensions'];
		if (isset($sections['Description'])) $description = $sections['Description'];
		if (isset($sections['Material & Colour'])) $material = $sections['Material & Colour'];
	}

	$img = '';
	if ($image_url) {
		$info = pathinfo($image_url);
		$fname = str_replace(['/', '"'], [';', "''"], $sku);
		$image_file = "$fname.".$info['extension'];
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($image_url, $path);
		if ($res) $img = $image_file;
	}

	$row = [$cat, $scat, $sku, $dimensions, $description, $material, $avail, $img];
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
