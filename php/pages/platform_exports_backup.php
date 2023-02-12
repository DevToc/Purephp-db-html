<?php
session_start();
include("/var/www/html/global/php/user/access.php");
access("USERPROD");
 include("/var/www/html/product/php/pages/prd_header.php");
  ?>
<h1>Download Daily Price and Quantity.</h1>
<div class="dropdown">
  <button class="dropbtn">Walmart</button>
  <div class="dropdown-content">
<u>    <h3>Canada</h3></u>
    <h4>Today</h4>
<a href="../../php/functions/export_walmart_today_ca_qty.php" target="_self">Todays Quantity</a>
<a href="../../php/functions/export_walmart_today_ca_price.php" target="_self">Todays Price</a>
<u><h4>All Days</h4></u>
<a href="../../php/functions/export_walmart_all_ca_qty.php" target="_self">All Time Canada</a>
<a href="../../php/functions/export_walmart_all_ca_price.php" target="_self">All Time USA</a>
<hr>
<u><h3>USA</h3></u>
<u><h4>Today</h4></u>
<a href="../../php/functions/export_walmart_today_com_qty.php" target="_self">Todays Quantity</a>
<a href="../../php/functions/export_walmart_today_com_price.php" target="_self">Todays Price</a>
<u><h4>All Days</h4></u>
<a href="../../php/functions/export_walmart_all_com_qty.php" target="_self">All Time Canada</a>
<a href="../../php/functions/export_walmart_all_com_price.php" target="_self">All Time USA</a>

  </div>
</div>
<div class="dropdown">

  <button class="dropbtn">Amazon</button>
  <div class="dropdown-content">
    <u><h4>Today</h4></u>
<a href="../../php/functions/export_amazon_today_ca.php" target="_self">Quantity And price Canada</a>
<a href="../../php/functions/export_amazon_today_com.php" target="_self">Quantity And price USA</a>
<a href="../../php/functions/export_amazon_today_au.php" target="_self">Quantity And price AU</a>
<a href="../../php/functions/export_amazon_today_mx.php" target="_self">Quantity And price MX</a>
<hr>
<u><h4>All Days</h4></u>
<a href="../../php/functions/export_amazon_all_ca.php" target="_self">Quantity And price Canada</a>
<a href="../../php/functions/export_amazon_all_com.php" target="_self">Quantity And price USA</a>
<a href="../../php/functions/export_amazon_all_au.php" target="_self">Quantity And price AU</a>
<a href="../../php/functions/export_amazon_all_mx.php" target="_self">Quantity And price MX</a>  </div>
</div>

<div class="dropdown">
  <button class="dropbtn">Wayfair</button>
  <div class="dropdown-content">
<a href="../../php/functions/export_wayfair_ca.php" target="_self">Quantity And price Canada</a>
<a href="../../php/functions/export_wayfair_com.php" target="_self">Quantity And price USA</a>
<hr>
<a href="../../php/functions/export_wayfair_ca_audit.php" target="_self">Audit Canada</a>
<a href="../../php/functions/export_wayfair_com_audit.php" target="_self">Audit USA</a>
  </div>
</div>

<div class="dropdown">
  <button class="dropbtn">Ebay</button>
  <div class="dropdown-content">
<a href="http://159.203.110.60/product/php/functions/export_ebaymrmj_ca.php" target="_self">Quantity And price Canada MRMJ</a>
<a href="../../php/functions/export_Ebay_com.php" target="_self">Quantity And price USA MRMJ</a>
<a href="../../php/functions/export_Ebay_ca.php" target="_self">Quantity And price Canada MDR</a>
<a href="../../php/functions/export_Ebay_com.php" target="_self">Quantity And price USA MDR</a>
<hr>
<a href="../../php/functions/export_Ebay_ca_audit.php" target="_self">Audit Canada MRMJ</a>
<a href="../../php/functions/export_Ebay_com_audit.php" target="_self">Audit USA MRMJ</a>
<a href="../../php/functions/export_Ebay_ca_audit.php" target="_self">Audit Canada MDR</a>
<a href="../../php/functions/export_Ebay_com_audit.php" target="_self">Audit USA MDR</a>
  </div>
</div>

<div class="dropdown">
  <button class="dropbtn">Unbeatable</button>
  <div class="dropdown-content">
<a href="../../php/functions/export_unbeatable_ca.php" target="_self">Quantity And price Canada</a>
<a href="../../php/functions/export_unbeatable_com.php" target="_self">Quantity And price USA</a>
<hr>
<a href="../../php/functions/export_unbeatable_ca_audit.php" target="_self">Audit Canada</a>
<a href="../../php/functions/export_unbeatable_com_audit.php" target="_self">Audit USA</a>
  </div>
</div>


<div class="dropdown">
  <button class="dropbtn">Etsy</button>
  <div class="dropdown-content">
<a href="../../php/functions/export_etsy_ca.php" target="_self">Quantity And price Canada</a>
<a href="../../php/functions/export_etsy_com.php" target="_self">Quantity And price USA</a>
<hr>
<a href="../../php/functions/export_etsy_ca_audit.php" target="_self">Audit Canada</a>
<a href="../../php/functions/export_etsy_com_audit.php" target="_self">Audit USA</a>
  </div>
</div>

<!-- <div class="dropdown">
  <button class="dropbtn">BCVB</button>
  <div class="dropdown-content">
<a href="../../php/functions/export_bcvb.php" target="_self">Quantity And price Canada</a>
<hr>
<a href="../../php/functions/export_bcvb_audit.php" target="_self">Audit USA</a>
  </div>
</div> -->

<div class="dropdown">
  <button class="dropbtn">OverStock</button>
  <div class="dropdown-content">
<a href="../../php/functions/export_overstock_ca.php" target="_self">Quantity And price Canada</a>
<a href="../../php/functions/export_overstock_com.php" target="_self">Quantity And price USA</a>
<hr>
<a href="../../php/functions/export_overstock_ca_audit.php" target="_self">Audit Canada</a>
<a href="../../php/functions/export_overstock_com_audit.php" target="_self">Audit USA</a>
  		</div>
	</div>

<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>
