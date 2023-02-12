<?php
include_once('/var/www/html/global/php/gbl_connect.php');
include_once('/var/www/html/global/php/gbl_general.php');
$platform = $_POST['trans_type'];


      $queryb = "select * from gbl_platforms_exports where platform='$platform' ORDER BY `platform` DESC";
      $stmb = $pdo_global->prepare($queryb);

 $stmb->execute();
$datac= $stmb->fetchALL(PDO::FETCH_ASSOC);

 $return = '<select name="trans_no"  class="form-control select211" style="width: 100%;">';

 foreach($datac as $datab)
                        {
 $return .= '<option value='.$datab["download_type"].'>'.$datab["download_type"].'';



 }
echo json_encode($return);









//echo "Test was successful";

?>
