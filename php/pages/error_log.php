<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
 include_once('/var/www/html/global/php/gbl_connect.php');


?>

	6. Display Log file?

	<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
