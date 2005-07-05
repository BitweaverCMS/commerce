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
// $Id: html_header.php,v 1.2 2005/07/05 21:08:15 spiderr Exp $
//
// TODO 
// cvs block
// stylesheets 

require(DIR_WS_MODULES . 'meta_tags.php');
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo META_TAG_TITLE; ?></title>
<meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>" />
<meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>" />
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" />
<?php
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^style/', '.css');

  while(list ($key, $value) = each($directory_array)) {
    echo '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />';
  }
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
