<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
  ?>
<tr>
<th>Wholesaler</th>
<th>Did the Screens Scrape Run</th>
<th>Did We Receive A File </th>
</tr>
<?php include_once("/var/www/html/global/php/gbl_connect.php");
$sql = "SELECT wholesaler_name,ss_successful,file_received FROM `gbl_ss_info` ORDER BY `wholesaler_name` ASC";
$result = $mysqli_global->query($sql);
if($result-> num_rows > 0){
while($row = $result-> fetch_assoc()){
  echo "<table><tr><td>". $row['wholesaler_name'] .
   "</td><td>". $row['ss_successful'] .
    "</td><td>". $row['file_received'] . "</td></tr>";
}
echo "</table>";
  }
 ?>


 <?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
