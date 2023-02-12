<?php include("/var/www/html/product/php/pages/prd_header.php"); ?>
<?php include_once('/var/www/html/global/php/gbl_connect.php'); ?>
Test Screen
<form method="post" action="http://159.203.110.60/product/php/functions/prd_functions.php" enctype="multipart/form-data">


<select id="plat" name="platform" style="padding: 7px;border-radius: 7px;">
<?php

      $querya = "select * from gbl_platforms";
      $stma = $pdo_global->prepare($querya);
 $stma->execute();



        $datab= $stma->fetchALL(PDO::FETCH_ASSOC);

 foreach($datab as $data)
                        {
  echo"
  <option data-id=".$data['platform']." value=".$data['platform'].">". $data['platform'] ."</option>

";






            }



?>
  </select>


<select id="response" style="padding: 7px;border-radius: 7px;" name="platb" required></select>
<br><br>
<input type="file" name="file">


<br><br>
<button style="padding-left: 30px !important; padding-right: 30px !important; border-radius: 18px !important;
color:white;background-color:black; padding-top:5px; padding-bottom:5px;   border-color: none !important;" type="submit" style="padding: 7px;border-radius: 7px; padding-top: 5px;  padding-bottom: 5px;" name="button2"> submit</button>

</form>







<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>



<script>
 $('#plat').on('change', function () {

   trans_type =  $(this).find('option:selected').attr('value');
   transactions = $("#response");
   $.ajax({
            method: 'POST',
            url: '../test_run.php',
dataType:'JSON',
            data: {trans_type:trans_type},
            success: function(response){
                 transactions.html(response);
console.log(response);
            }
       });
});</script>
