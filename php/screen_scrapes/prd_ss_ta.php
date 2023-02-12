<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');


//tiger auto 1//

$user = '193962';
$pass = '10e13p8';


	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ta_options`;');
	$insert_item = $pdo_product->prepare("INSERT INTO `prd_ss_ta_options` (`year`, `make`, `make_id`, `model`, `model_id`) VALUES (?, ?, ?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , 'localhost:8080');

	echo "logging in.\n";

	$params = array(
		'location' => 'Brampton', 
		'username' => $user, 
		'password' => $pass,
		'submit' => '1'

	);
	$content = http_req('https://brampton.tigeronlineorder.com/admin/dologin.php', $params);
	if (!preg_match("#window\.location='(?:mr|partslist2|dashboard)\.php';#", $content)) die('auth failed');

	$content = http_req('https://brampton.tigeronlineorder.com/admin/partslist2.php');
		
	if (!preg_match('#<select id="makes"(.*?)</select>#s', $content, $m)) die('makes not found');
	if (!preg_match_all('#<option value="([^"]*?)">\s*(.*?)\s*</option>#', $m[1], $m)) die('makes not found');
	$makes = array_combine($m[2], $m[1]);

	foreach ($makes as $make => $make_id) {
		if ($make == 'Make' || $make_id == 'x') continue;
		echo "$make - $make_id\r\n";

		$content = http_req('https://brampton.tigeronlineorder.com/admin/get_parts_models.php?r='.rands(), "id=$make_id");
		if (!preg_match('#<select id="models"(.*?)</select>#s', $content, $m)) continue;
		if (!preg_match_all('#<option value=\'([^"]*?)\'>\s*(.*?)\s*</option>#', $m[1], $m)) continue;
		$models = array_combine($m[2], $m[1]);
		foreach ($models as $model => $model_id) {
			if ($model == 'Model' || $model_id == 'x') continue;
			echo "\t$model - $model_id\r\n";

			$content = http_req('https://brampton.tigeronlineorder.com/admin/get_model_years.php?r='.rands(), "model=$model_id");
			if (!preg_match('#<select id="years"(.*?)</select>#s', $content, $m)) continue;
			if (!preg_match_all('#<option value=\'([^"]*?)\'>\s*(.*?)\s*</option>#', $m[1], $m)) continue;
			$years = array_combine($m[2], $m[1]);

			foreach ($years as $year => $year_id) {
				if ($year == 'Year' || $year_id == 'x') continue;
				$insert_item->execute(array(intval($year), $make, $make_id, $model, $model_id));
			}
		}
	}

	echo "\nfinished.\n";

	//starting of tiger auto 2//
	
	$stmt = $pdo_product->prepare('SELECT `id`,`year`,`make`,`make_id`,`model`,`model_id` FROM prd_ss_ta_options ORDER by id ASC;');
	$stmt->execute(); 
	$models = $stmt->fetchAll(PDO::FETCH_NUM);
	if (count($models) == 0) die("nothing to do\r\n");

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_ta_products`;');
	$insert_item = $pdo_product->prepare("INSERT INTO `prd_ss_ta_products` (`model_row`, `sku`, `description`, `list_price`, `price`, `qty`) VALUES (?, ?, ?, ?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
//	curl_setopt($ch, CURLOPT_PROXY , 'localhost:8080');

	echo "logging in.\n";

	$params = array(
		'location' => 'Brampton', 
		'username' => $user, 
		'password' => $pass,
		'submit' => '1'

	);
	$content = http_req('https://brampton.tigeronlineorder.com/admin/dologin.php', $params);
	if (!preg_match("#window\.location='(?:mr|partslist2|dashboard)\.php';#", $content)) die('auth failed');
	$content = http_req('https://brampton.tigeronlineorder.com/admin/partslist2.php');

	$bo = preg_match('#<input type="hidden" id="bo" value="([^"]*?)"#s', $content, $m) ? $m[1] : '';
	$branch = preg_match("#var branch = '([^']*?)';#", $content, $m) ? $m[1] : '';
	$cust = preg_match("#var cust = '([^']*?)';#", $content, $m) ? $m[1] : '';
	$staffid = preg_match("#var staffid = '([^']*?)';#", $content, $m) ? $m[1] : '';
	if (!$branch || !$cust || !$staffid) die('no params');


	foreach ($models as $mrow) {
		list($row_id, $year, $make, $make_id, $model, $model_id) = $mrow;
		echo "$make $model - $year\r\n";

		$params = array(
			'make' => $make_id, 
			'model' => $model_id, 
			'year' => $year, 
			'customer' => 'x', 
		);
		$content = http_req('https://brampton.tigeronlineorder.com/admin/parts_products2.php?hide_title=1&r='.rands(), $params);

		if (!preg_match('#<table[^>]*?class="[^"]*?product_list_desktop[^"]*?">(.*?)</table>#s', $content, $m)) {
			echo "no results table\r\n";
			continue;
		}
		if (!preg_match_all('#(<tr class="alt[^>]*?>.*?</tr>)#si', $m[1], $m)) continue;

		$rows = $m[1];
		foreach ($rows as $row) {
			if (!preg_match('#<span class="new-sku">\s*(.*?)\s*</span>#s', $row, $m)) continue;
			$sku = $m[1];
			echo "\t$sku ";
			$desc = '';
			if (preg_match('#<td[^>]*?headers="category"[^>]*?>\s*(.*?)\s*</td>#s', $row, $m)) {
				$line = preg_replace('#\s*?<div[^>]*?display:none[^>]*?>.*?</div>#s', '', $m[1]);
				$line = preg_replace('#<span class="tg-tooltip">.*?</span>#s', '', $line);
				$line = preg_replace('#\s*</?span>\s*#', '', $line);
				$desc = trim($line);
			} else {
				$desc = preg_match('#<div style="width: 90%[^"]*?">(?:<br>\s*)*(.*?)(?:<br>\s*)*</div>#s', $row, $m) ? $m[1] : '';
			}

			$list_price = $your_price = '';
			if (preg_match('#<td axis="sstring">(?:\s*<span[^>]*?>)?\s*(.*?)\s*(?:</span>\s*)?</td>\s*<td axis="sstring">(?:\s*<span[^>]*?>)?\s*(.*?)\s*(?:</span>\s*)?</td>#s', $row, $m)) {
				$list_price = $m[1];
				$your_price = $m[2];
			}

			$stock = '';	
			if (preg_match('#<input name="cart"#', $row)) {
				$stock = 'Yes';
			} else {
				echo '.';
				$check_url = 'https://brampton.tigeronlineorder.com/admin/other_stock2.php?bo='.$bo.'&sku='.urlencode($sku).'&branch='.$branch.'&custid='.$staffid.'&cust='.$cust.'&staffid='.$staffid.'&make='.$make_id.'&model='.$model_id.'&query=undefined';
				$content = http_req($check_url);
				if (preg_match('#^(.*?)(?:<br /><br /><strong>List Price:|<|$)#si', $content, $m)) {
					$stock = trim(strip_tags($m[1]));
				}
			}
			$insert_item->execute(array($row_id, $sku, $desc, $list_price, $your_price, $stock));	
			echo "\r\n";
#			sleep(1);
		}
	}
	echo "\nfinished.\n";
	

function http_req($url, $postfields = false, $head = false) {
global $ch;

	curl_setopt($ch, CURLOPT_URL, $url);
	$post = false;
	if ($postfields) {
		$post = true;
		if (is_array($postfields)) $postfields = http_build_query($postfields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	}
	if ($head) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
	} else {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array());
	}

	curl_setopt($ch, CURLOPT_POST, $post);
	$content = curl_exec($ch);

	return $content;
}

function rands($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}





