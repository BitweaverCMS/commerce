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
// $Id: html_header.php,v 1.5 2005/09/27 22:33:53 spiderr Exp $
//
// TODO
// cvs block
// stylesheets

require(DIR_FS_MODULES . 'meta_tags.php');

  $directory_array = $template->get_template_part($template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'common') . '/common/', '/^jscript_/', '.js');

  while(list ($key, $value) = each($directory_array)) {
    require($template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $value);
  }

  $directory_array = $template->get_template_part($page_directory, '/^jscript_/');

  while(list ($key, $value) = each($directory_array)) {
    require($page_directory . '/' . $value);
  }
?>
<script type="text/javascript">
function clearText(thefield){
if (thefield.defaultValue==thefield.value)
thefield.value = ""
}
</script>

</head>
