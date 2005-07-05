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
// $Id: jscript_main.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//
?>
<script language="javascript" type="text/javascript"><!--
var i=0;
function resize() {
  if (navigator.appName == 'Netscape') i=10;
  if (document.documentElement && document.documentElement.clientWidth) {
    frameWidth = document.documentElement.clientWidth;
    frameHeight = document.documentElement.clientHeight;
    window.resizeTo(frameWidth,frameHeight-i);
	}
  else if (document.body) {
     window.resizeTo(document.body.clientWidth, document.body.clientHeight-i);
     }
  self.focus();
}
//--></script>