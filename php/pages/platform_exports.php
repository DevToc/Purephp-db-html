<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
  ?>

<h1>Download Daily Price and Quantity.</h1>
<?php include_once('/var/www/html/global/php/gbl_connect.php'); ?>
<form method="get" action="http://159.203.110.60/product/php/functions/prd_functions.php">

<select id="plat" name="platform" placeholder="Select A Platform">
  <option value="" disabled selected>Select your Platform</option>
<?php
$querya = "SELECT * FROM `gbl_platforms` ORDER BY `platform` ASC";
$stma = $pdo_global->prepare($querya);
$stma->execute();
$datab= $stma->fetchALL(PDO::FETCH_ASSOC);
foreach($datab as $data)
{
    echo"  <option data-id=".$data['platform']." value=".$data['platform'].">". $data['platform'] ."</option>";
}
?>
  </select>
  <select id="response" name="platb" required></select>
  <br>
  <button type="submit" name="btn-plat_option"> submit</button>
</form>
<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>



<script>
 $('#plat').on('change', function () {

   trans_type =  $(this).find('option:selected').attr('value');
   transactions = $("#response");
   $.ajax({
            method: 'POST',
            url: '../functions/platform_options.php',
dataType:'JSON',
            data: {trans_type:trans_type},
            success: function(response){
                 transactions.html(response);
console.log(response);
            }
       });
});</script>





<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
<?php
//any on click statements
if(isset($_GET['btn-plat_option'])) {
platform_export_choice();
}
 ?>
