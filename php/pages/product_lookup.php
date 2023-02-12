<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
  ?>

  <table>
<tr>
  <th>Sku</th>
<th>Wholesaler</th>
<th>Did the Screens Scrape Run</th>
<th>Did We Receive A File </th>
</tr>
if($result-> num_rows > 0){
while($row = $result-> fetch_assoc()){
  echo "<tr><td>". $row['wholesaler_sku'] .
   "<tr><td>". $row['wholesaler_name'] .
   "</td><td>". $row['ss_successful'] .
    "</td><td>". $row['file_received'] . "</td></tr>";
}
echo "</table>";
  }
 ?>


 <?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
