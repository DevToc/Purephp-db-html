<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');




$user = 'mdrtrading';
$password = 'Mdr856268*';



	$pdo_product->exec('TRUNCATE TABLE `prd_ss_lk_options`;');

	$insert_item = $pdo_product->prepare("INSERT INTO `prd_ss_lk_options` (`year`, `make`, `model`) VALUES (?, ?, ?);");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '');
	curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

	echo "logging in.\n";

	$content = http_req('https://portal.lkqcorp.com/login');

	$params = array(
		'isInternalEmployee' => false,
		'username' => $user,
		'password' => $password,
	);
	$content = http_req('https://portal.lkqcorp.com/api/Users/login', json_encode($params), array('Accept: application/json, text/plain, */*', 'Content-Type: application/json', 'Origin: https://portal.lkqcorp.com'));

	$data = json_decode($content, true);
	if (!$data || !isset($data['isAuthenticated']) || $data['isAuthenticated'] != 1) die("auth failed!\n");
	if (!isset($data['token'])) die("no token\n");

	$auth = 'Authorization: Bearer '.$data['token'];

	$content = http_req('https://portal.lkqcorp.com/api/Users/OKC/get-transport-key', false, array('Accept: application/json, text/plain, */*', $auth));
	$data = json_decode($content, true);
	if (!$data || !isset($data['transportKey'])) die("getting transport key failed\n");
	$tkey = $data['transportKey'];

	$content = http_req('https://preview.orderkeystone.com/web-transport-landing?key='.urlencode($tkey), false, array('Referer: https://portal.lkqcorp.com/'));

	$api_head = array('Accept: application/json, text/plain, */*', $auth, 'Referer: https://preview.orderkeystone.com/');

	$content = http_req('https://preview.orderkeystone.com/api/Products/getYears/002/crash/years', false, $api_head);
	$years = json_decode($content, true);
	foreach ($years as $year) {
		echo "$year\r\n";
		$pdo_product->beginTransaction();
		$content = http_req('https://preview.orderkeystone.com/api/Products/getMakes/002/crash/'.$year.'/makes', false, $api_head);
		$makes = json_decode($content, true);
		foreach ($makes as $make) {
			echo "\t$make\r\n";
			$content = http_req('https://preview.orderkeystone.com/api/Products/getModels/002/crash/'.$year.'/'.$make.'/models', false, $api_head);
			$models = json_decode($content, true);
			foreach ($models as $model) {
				$insert_item->execute(array(intval($year), $make, $model));
			}
		}
		$pdo_product->commit();
	}
	$pdo_product->exec('DELETE FROM `prd_ss_lk_options` where year < 2000;');

	
//begin part 2//
	
	$stmt = $pdo_product->prepare('SELECT id,year,make,model FROM prd_ss_lk_options;');
	$stmt->execute(); 
	$models = $stmt->fetchAll(PDO::FETCH_NUM);
	if (count($models) == 0) die("nothing to do\r\n");

	$pdo_product->exec('TRUNCATE TABLE `prd_ss_lk_products`;');
	$insert_item = $pdo_product->prepare("INSERT INTO `prd_ss_lk_products` (`model_id`, `sku`, `qty`, `title`, `list_price`, `price`) VALUES (?, ?, ?, ?, ?, ?);");


	$content = http_req('https://portal.lkqcorp.com/login');

	$params = array(
		'isInternalEmployee' => false,
		'username' => $user,
		'password' => $password,
	);
	$content = http_req('https://portal.lkqcorp.com/api/Users/login', json_encode($params), array('Accept: application/json, text/plain, */*', 'Content-Type: application/json', 'Origin: https://portal.lkqcorp.com'));

	$data = json_decode($content, true);
	if (!$data || !isset($data['isAuthenticated']) || $data['isAuthenticated'] != 1) die("auth failed!\n");
	if (!isset($data['token'])) die("no token\n");
	$user_id = $data['currentCustomer']['customerNumber'];

	$auth = 'Authorization: Bearer '.$data['token'];

	$content = http_req('https://portal.lkqcorp.com/api/Users/OKC/get-transport-key', false, array('Accept: application/json, text/plain, */*', $auth));
	$data = json_decode($content, true);
	if (!$data || !isset($data['transportKey'])) die("getting transport key failed\n");
	$tkey = $data['transportKey'];

	$content = http_req('https://preview.orderkeystone.com/web-transport-landing?key='.urlencode($tkey), false, array('Referer: https://portal.lkqcorp.com/'));

	$api_head = array('Accept: application/json, text/plain, */*', $auth, 'Referer: https://preview.orderkeystone.com/crash');

	foreach ($models as $m) {
		list($id, $year, $make, $model) = $m;
		echo "$year - $make - $model\r\n";
		$off = 0;
		$results = '';
		while (1) {
			$api_url = 'https://preview.orderkeystone.com/api/Products/getPartsByYmmCFilters/002/crash/filter?make='.$make.'&model='.$model.'&customerNumber='.$user_id.'&category=All%20Parts&year='.$year.'&size=&designation=&sortSequence=F&skip='.$off.'&take=100';
			$content = http_req($api_url, false, $api_head);
			$data = json_decode($content, true);
			if (!$data) break;
			$count = $data['totalCount'];
			echo "\t$count results\r\n\r\n";
			if ($count == 0) break;

			foreach ($data['products'] as $p) {
				$prod_num = $p['productNumber'];
				echo "\t$prod_num\r\n";
				$avail = $p['displayAvailabilityDescription'];
				$first_eda = $p['firstEda'];
				if ($first_eda) $avail.=" $first_eda";
				$title = $p['displayTitle'];

				$extra = '';
				if (preg_match_all('#&\#8226;\s*(.*?)\s*<br/>#s', $p['displayMobileDescription'], $m)) {
					$extra = implode("\r\n", $m[1]);
				}

				$list_price = $p['currentUnitOfMeasure']['listPrice'];
				$your_price = $p['currentUnitOfMeasure']['customerPrice'];


				$insert_item->execute(array($id, $prod_num, $avail, $title, $list_price, $your_price));
				
			}
			$off += 100;
			if ($off > $count) break;
			
		}
	}






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




