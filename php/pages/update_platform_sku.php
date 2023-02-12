<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
  ?>
<!-- 2. Uploads of skus per platform -->
<?php include_once('/var/www/html/global/php/gbl_connect.php'); ?>

<form method="get" action="http://159.203.110.60/product/php/functions/prd_functions.php">
<select id="plat" name="platform" >
<?php
//look up in database  for all current platforms
      $querya = "select * from gbl_platforms";
      $stma = $pdo_global->prepare($querya);
 $stma->execute();

        $datab= $stma->fetchALL(PDO::FETCH_ASSOC);

 foreach($datab as $data)
 {
  echo"
  <option data-id=".$data['id']." value=".$data['platform'].">". $data['platform'] ."</option>
";
}
?>



<label for="myfile">Select a file:</label><br>
<input type="file" id="myfile" name="myfile">
<button type="submit" name="import_btn"> submit</button>

  </form>



<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
<!-- <script>
 $('#plat').on('change', function () {

   trans_type =  $(this).find('option:selected').attr('data-id');
   transactions = $('#response');
   $.ajax({
            method: 'POST',
            url: '../test_run.php',

            data: {trans_type:trans_type},
            success: function(response){
              console.log("a");
                 transactions.html(response);
            }
       });
});</script> -->
