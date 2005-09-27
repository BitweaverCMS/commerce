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
// $Id: tpl_header_test_info.php,v 1.2 2005/09/27 22:33:53 spiderr Exp $
//
?>
    <table class="centershop" border="0" cellspacing="0" cellpadding="0">
      <tr><td>
        <table border="0" cellspacing="0" cellpadding="0" class="headerNavigation" align="center">
          <tr class="headerNavigation">
            <td align="left" valign="top" width="33%" class="headerNavigation">
              <a href="<?php echo zen_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>"><?php echo HEADER_TITLE_CATALOG; ?></a>&nbsp;|&nbsp;
<?php if ($_SESSION['customer_id']) { ?>
              <a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGOFF; ?></a>&nbsp;|&nbsp;
              <a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a>
<?php } else { ?>
              <a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a>
<?php } ?>
            </td >
            <td align="center" width="25%"><?php require(DIR_FS_MODULES . 'sideboxes/' . 'search_header.php'); ?>
            </td>
            <td class="headerNavigation" align="right" valign="top" width="33%">
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
              <a href="<?php echo zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'); ?>"><?php echo HEADER_TITLE_CART_CONTENTS; ?></a>&nbsp;|&nbsp;<a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo HEADER_TITLE_CHECKOUT; ?>&raquo;</a>
<?php }?></td>
          </tr>
        </table>
        <table border="0" width="100%" cellspacing="0" cellpadding="0" class="header">
          <tr><!-- All HEADER_ definitions in the columns below are defined in includes/languages/english.php //-->
            <td align="center" valign="middle" height="<?php echo HEADER_LOGO_HEIGHT; ?>" width="<?php echo HEADER_LOGO_WIDTH; ?>">
            <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) . '</a>'; ?>
            </td>
            <td align="center" valign="middle"><?php echo HEADER_SALES_TEXT; ?></td>
          </tr>
        </table>
<?php
  if (isset($_GET['error_message']) && zen_not_null($_GET['error_message'])) {
?>
        <table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="headerError">
            <td class="headerError"><?php echo htmlspecialchars(urldecode($_GET['error_message'])); ?></td>
          </tr>
        </table>
<?php
  }
  if (isset($_GET['info_message']) && zen_not_null($_GET['info_message'])) {
?>
        <table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="headerInfo">
            <td class="headerInfo"><?php echo htmlspecialchars($_GET['info_message']); ?></td>
          </tr>
        </table>
<?php
  }
?>

<?php
if (isset($_SESSION['SSL_SESSION_ID'])) {
  $show_session_expire = $db->Execute("select * from " . TABLE_SESSIONS . " where sessions_id= '" . $_SESSION['SSL_SESSION_ID'] . "'");
}
echo '<br /><b>TESTING INFO:</b> Time page: <strong>' . $_GET['main_page'] . '</strong> was loaded is: <strong>' . date('H:i:s', time()) . '</strong><br><br>';
echo 'Session ID: ' . zen_session_id() . '<br / >';
echo 'REGISTERED GLOBALS is: <b>' . (ini_get('register_globals')=='1' ? 'ON' : 'OFF') . '</b>' . ' Session Timeout: <b>' . ini_get('session.gc_maxlifetime') . 's</b><br><br>';
echo "GLOBALS[$main_page] and HTTP_GET_VARS['main_page'] and _GET['main_page'] = " . $GLOBALS['main_page'] . ' - ' . $_GET['main_page'] . ' - ' . $_GET['main_page']  . '<br><br>';
echo "_SERVER['PHP_SELF'] and _GET['PHP_SELF'] and PHP_SELF and _SESSION['PHP_SELF'] = " . $_SERVER['PHP_SELF'] . ' - ' . $_GET['PHP_SELF'] . ' - ' . $PHP_SELF  . ' - ' . $_SESSION['PHP_SELF'] . '<br><br>';
echo "getenv('REQUEST_URI') = " . getenv('REQUEST_URI') . '<br><br>';
echo 'SERVER_NAME = ' . $_SERVER['SERVER_NAME'] . '<br><br>';
echo 'SCRIPT_FILENAME = ' . $_SERVER['SCRIPT_FILENAME'] . '<br><br>';
echo 'HTTP_REFERER = ' . $_SERVER['HTTP_REFERER'] . '<br><br>';
echo 'template_dir = ' . $template_dir . '<br><br>';
echo '<strong>TEST LANGUAGE ' . TEST_LANGUAGE . '</strong><br><br>';
if (strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME'])) {
  echo 'SERVER_NAME within HTTP_REFERER - Yes' . '<br>';
} else {
  echo 'SERVER_NAME within HTTP_REFERER - No' . '<br>';
}

/*
echo '<b>AIN\'T THIS COOL TO KNOW:</b> ' . '<br><br>';
if (isset($_GET['width']) AND isset($_GET['height'])) {
  // output the geometry variables
  echo "Screen width is: ". $_GET['width'] ."<br />\n";
  echo "Screen height is: ". $_GET['height'] ."<br />\n";
} else {
  // pass the geometry variables
  // (preserve the original query string
  //   -- post variables will need to handled differently)

  echo "<script language='javascript'>\n";
  echo "  location.href=\"${_SERVER['SCRIPT_NAME']}?${_SERVER['QUERY_STRING']}"
            . "&width=\" + screen.width + \"&height=\" + screen.height;\n";
  echo "</script>\n";
  exit();
}
*/
echo '<br><br>'
?>
<?php
  $offset = 0;
  while ($offset < 12) {
   $back = sizeof($_SESSION['navigation']->path)-$offset;
   if (isset($_SESSION['navigation']->path[$back]['page'])) {
     $test_link= zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']);
?>
            <table>
              <tr>
                <td class="main"><?php echo '<a href="' . zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . zen_image_button('button_back.gif', 'TEST BACK NONE') . '</a>-' . $offset . '<br>Go to: ' . $test_link; ?></td>
              </tr>
            </table>
<?php
   }
   $offset++;
 }
?>
