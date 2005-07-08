<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: tpl_main_page.php,v 1.4 2005/07/08 06:13:05 spiderr Exp $
//

  $header_template = 'tpl_header.php';
  $footer_template = 'tpl_footer.php';
?>


<div id="header">
<?php require(DIR_WS_MODULES . 'header_nav.php'); ?><br class="clear" />
</div>
<?php require(DIR_WS_MODULES . 'header.php'); ?>

<div id="content">

	<div id="floatwrap">
		<div id="col-main">
	  		<div id="main">
				<div class="breadCrumb"><?php echo $breadcrumb->trail(' &raquo; '); ?></div>
<?php require($body_code); ?>
			</div> <!-- end main -->
		</div> <!-- end mainwrap -->
		<br class="clear" />
	</div> <!-- end floatwrap -->

<br class="clear" />

</div> <!-- end pagewrap -->


<div id="footer">
<?php require(DIR_WS_MODULES . 'footer.php'); ?>
</div> <!-- end footer -->

