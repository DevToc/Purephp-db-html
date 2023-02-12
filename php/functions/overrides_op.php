<?php
include_once('/var/www/html/global/php/gbl_connect.php');


$type = $_POST['type'];

if ($type == "search") {
    $keyword = $_POST['keyword'];
    $query = "select * from prd_mpl_active_overides  where INSTR(wholesaler_sku,'$keyword') or INSTR(wholesaler_option,'$keyword')or INSTR(wholesaler,'$keyword')";
    $result = $mysqli_product->query($query);
    $res = "";
    if ($result->num_rows == 0) {
        echo "";
        return;
    }
    $arr  = array();
    while ($data = $result->fetch_assoc()) {
        array_push($arr, $data);
    }
    $res['data'] = $arr;
    echo json_encode($res);
}

if ($type == "add") {

    parse_str($_POST['addData'], $searcharray);
    $mdr_sku = $searcharray["wholesaler1"] . "-" . $searcharray["wholesaler_sku1"];
    $wholesaler = $searcharray['wholesaler1'];
    $wholesaler_sku = $searcharray['wholesaler_sku1'];
    $wholesaler_option = $searcharray['wholesaler_option1'];
    $BO_Date = $searcharray['BO_Date1'];
    $bo = explode("-", $BO_Date);
    $BO_Date = $bo[1] . "/" . $bo[2] . "/" . $bo[0];
    $disconinued = $searcharray['discontinued1'];
    $country = $searcharray['country1'];

    $query = "INSERT INTO `prd_mpl_active_overides` (`mdr_sku`, `wholesaler`, `wholesaler_sku`, `wholesaler_option`, `bo_date`, `discontinued`, `country`) VALUES ('$mdr_sku', '$wholesaler', '$wholesaler_sku', '$wholesaler_option', '$BO_Date', '$disconinued', '$country')";
    $result = $mysqli_product->query($query);
    print_r($result);
}

if ($type == "edit") {
    parse_str($_POST['editData'], $searcharray);
    $id = $searcharray['editId'];
    $BO_Date = $searcharray['BO_Date'];
    $disconinued = $searcharray['discontinued'];
    $country = $searcharray['country'];
    $query = "update `prd_mpl_active_overides` set `bo_date`='$BO_Date', `discontinued`='$disconinued', `country`='$country' where `id`= $id";

    $result = $mysqli_product->query($query);
    echo $result;
}


if ($type == "delete") {
    $id = $_POST['id'];
    $query = "delete from prd_mpl_active_overides  where id = $id";
    $result = $mysqli_product->query($query);
    echo $result;
}
