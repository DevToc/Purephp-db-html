<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');



$user = 'mrmjstrading@gmail.com';
$password = 'mrmj070468';

$up_dir = 'images/wt';

	if (!file_exists($up_dir)) {
		if (!mkdir($up_dir, 0777, true)) {
		    die('Failed to create folder');
		}
	}
	echo "<pre>\n";

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_wt_products`;');
	$insert_product = $pdo_product->prepare('INSERT INTO `prd_ss_wt_products` (`category`, `subcategory`, `sku`, `name`, `price`, `sale_price`, `size`, `description`, `image`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , '127.0.0.1:8080');

	echo "logging in<br><br>\n";

	$log_url = 'http://w2trading.net/login.aspx';
	$content = http_req($log_url);

	$vst = (preg_match('#<input[^>]*?id="__VIEWSTATE" value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';
	$vstg = (preg_match('#<input[^>]*?id="__VIEWSTATEGENERATOR" value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';

	$params = array(
		'__EVENTTARGET' => '', 
		'__EVENTARGUMENT' => '', 
		'__VIEWSTATE' => $vst, 
		'__VIEWSTATEGENERATOR' => $vstg, 
		'ctl00$contentMain$txtUsername' => $user, 
		'ctl00$contentMain$txtPassword' => $password, 
		'ctl00$contentMain$btnLogin' => 'Login'		
	);

	$content = http_req($log_url, $params);
	if (!(preg_match('/>Log out</i', $content))) die('auth failed!');
	$base = 'http://w2trading.net';

	$head = rx('#<div class="globalheader">(.*?)</div>#s', $content);
	if (!preg_match_all('#<a href="([^"]*?itemlisting.aspx\?catid=[^"]*?)"*><span>\s*(.*?)\s*</span>#s', $head, $m)) die('categories not found');

	$cats = array_combine($m[1], $m[2]);
	foreach ($cats as $href => $cat) {
		echo "Category: $cat\n";
		$content = http_req($base.$href);
		$sub = rx('#<span id="ctl00_lblSubMenu">\s*<ul>(.*?)</span>#s', $content);
		if (preg_match_all('#<a href=\'([^\']*?)\'>\s*(.*?)\s*</a>#s', $sub, $m)) {
			$scats = array_combine($m[1], $m[2]);
			foreach ($scats as $href => $scat) {
				echo "\tSubcategory: $scat\n";
				scrape_subcat($base.$href, $cat, $scat);

			}
		}
	}


function scrape_subcat($url, $cat, $scat) {

	$params = '';

	while (true) {
		$content = http_req($url, $params);
		if (preg_match_all('#<a id="[^"]*?_lnkProduction"[^>]*?href="([^"]*?)"#', $content, $m)) {
			foreach ($m[1] as $href) {
				scrape_item('http://w2trading.net/listing/'.html_entity_decode($href), $cat, $scat);
			}
		}

		if (!preg_match('#<a[^>]*?href=[^>]*?>Next</a>#', $content)) break;

		$vst = (preg_match('#<input[^>]*?id="__VIEWSTATE" value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';
		$vstg = (preg_match('#<input[^>]*?id="__VIEWSTATEGENERATOR" value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';
		$id = (preg_match('#<input[^>]*?id="ctl00_contentMain_hfSubID" value="([^"]*?)"#si', $content, $m)) ? $m[1] : '';
		$params = array(
			'__EVENTTARGET' => 'ctl00$contentMain$lbtnNext',
			'__VIEWSTATE' => $vst,
			'__VIEWSTATEGENERATOR' => $vstg,
			'__VIEWSTATEENCRYPTED' => '',
			'ctl00$contentMain$hfSubID' => $id
		);
	}
}
function scrape_item($url, $cat, $scat) {
global $insert_product, $up_dir;

	$content = http_req($url);

	$num = rx('#<span id="ctl00_contentMain_lblItemNumber">\s*(.*?)\s*</span>#s', $content);
	echo "\t\t$num\n";

	$name = rx('#<span id="ctl00_contentMain_lblItemName">\s*(.*?)\s*</span>#s', $content);
	$name = html_entity_decode($name);

	$price = rx('#<span id="ctl00_contentMain_lblPrice"[^>]*?>\s*(.*?)\s*</span>#s', $content);
	$sale_price = rx('#<span id="ctl00_contentMain_lblSalePrice">\s*(.*?)\s*</span>#s', $content);


	$size = rx('#<span id="ctl00_contentMain_lblSizeWeight">\s*(.*?)\s*</span>#s', $content);

	$desc = rx('#<span id="ctl00_contentMain_lblItemDesc">\s*(.*?)\s*</span></td>#s', $content);
	$desc = preg_replace('#[\r\n]+#', ' ', $desc);
	$desc = html_entity_decode($desc);

	$image = '';
	$img = rx('#<img id="ctl00_contentMain_imgItem" src="([^"]*?)"#s', $content);
	if ($img) {
		$img = 'http://w2trading.net'.$img;
		$image_file = $num.'.jpg';
		$path = $up_dir.DIRECTORY_SEPARATOR.$image_file;
		$res = dl_file($img, $path);
		if ($res) $image = $image_file;
	}

	$row = array($cat, $scat, $num, $name, $price, $sale_price, $size, $desc, $image);
	$insert_product->execute($row);
}



function rx($pat, $content) {
	return (preg_match($pat, $content, $m)) ? $m[1] : '';
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
