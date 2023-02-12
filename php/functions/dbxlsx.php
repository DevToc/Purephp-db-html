<?php
include_once('/var/www/html/global/php/gbl_connect.php');
require 'SimpleXLSX.php';

	$pdo_product->exec('TRUNCATE TABLE `cars`;');


// xlsx heading => table column
$map = [
'yr' => 'year',
'make' => 'make',
'mdl' => 'model'
];

// table name
$table = 'cars';

	process_xlsx('cars.xlsx', $table, $map);


function process_xlsx($fname, $table, $mapping, $tx = false) {
global $pdo_product;

	$xlsx = SimpleXLSX::parse($fname);

	if (!$xlsx) {
		die('error parsing xlsx - '.SimpleXLSX::parseError());
	}

	$cols = implode(', ', array_map(function ($a) { return "`$a`"; }, array_values($mapping)));
	$vals = implode(', ', array_pad([], count($mapping), '?'));
	$keys = array_map('strtolower', array_keys($mapping));


	if ($tx) $pdo_product->beginTransaction();
	$insert = $pdo_product->prepare("INSERT INTO `$table` ($cols) VALUES ($vals);");

	$heading = [];
	foreach ($xlsx->rows() as $n => $row) {
		if ($n == 0) {
			$heading = array_map('strtolower', $row);
			continue;
		}

		$vals = array_combine($heading, $row);
		$match = array_intersect_key($vals, $mapping);
		$insert->execute(array_values($match));
	}
	if ($tx) $pdo_product->commit();

}
