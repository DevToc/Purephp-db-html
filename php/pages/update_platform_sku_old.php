<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
  ?>
<!-- 2. Uploads of skus per platform -->
<Br>



  	<form method="post"  action="/product/php/functions/export_amazon_today_com.php">
  	  <h1>Select Your Free Option</h1>

  	  <select   name="option">
  	    <option value="" disabled selected>Choose your option</option>
  	    <option value="1">Option 1</option>
  	    <option value="2">Option 2</option>
  	    <option value="3">Option 3</option>
  	  </select>

  	  <p><button >Register</button></p>
  	</form>
  <?php
  include("/var/www/html/product/php/functions/prd_functions.php");

  if(isset($_POST['option'])) {
  	echo "This is Button1 that is selected";
  }

  		if(isset($_POST['button1'])) {
  			echo "This is Button1 that is selected";
  		}
  		if(isset($_POST['button2'])) {
  			echo "This is Button2 that is selected";
  		}
  	?>
  	<form method="post">
  		<input type="submit" name="button1"
  				value="Button1"/>
  		<input type="submit" name="button2"
  				value="Button2"/>
  	</form>
  </head>
  </html>

<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
