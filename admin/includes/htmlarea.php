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
//  $Id: htmlarea.php,v 1.1 2005/07/05 06:00:00 bitweaver Exp $
//

// INSERTS <SCRIPT> TAGS IN <HEAD> FOR HTMLAREA TO BE CALLED
if ($_SESSION['html_editor_preference_status']=="HTMLAREA") {
 define('BR','
 '); /// YES, this is INTENTIONALLY on 2 separate lines... thus inserting a line break into the code generated between echo statements below.

//define URL and LANG parameters
	echo '<script type="text/javascript">' .BR;
	echo '   _editor_url = "'.DIR_WS_CATALOG . 'htmlarea/";' .BR;
    echo '	_editor_lang = "'.strtolower(DEFAULT_LANGUAGE).'";' .BR;
	echo '</script>' .BR;

//<!-- load the main HTMLArea files -->
	echo '<script type="text/javascript" src="' . DIR_WS_CATALOG . 'htmlarea/htmlarea.js"></script>' .BR;
//	echo '<script type="text/javascript" src="' . DIR_WS_CATALOG . 'htmlarea/lang/'.strtolower(DEFAULT_LANGUAGE).'.js"></script>' .BR;
//	echo '<script type="text/javascript" src="' . DIR_WS_CATALOG . 'htmlarea/dialog.js"></script>' .BR;
// 	echo '<script type="text/javascript" src="' . DIR_WS_CATALOG . 'htmlarea/popupdiv.js"></script>' .BR;
//	echo '<script type="text/javascript" src="' . DIR_WS_CATALOG . 'htmlarea/popupwin.js"></script>' .BR;

//<!-- load the plugins -->
//	echo '<script type="text/javascript">' .BR;
      // WARNING: using this interface to load plugin
      // will _NOT_ work if plugins do not have the language
      // loaded by HTMLArea.

      // In other words, this function generates SCRIPT tags
      // that load the plugin and the language file, based on the
      // global variable HTMLArea.I18N.lang (defined in the lang file,
      // in our case "lang/en.js" loaded above).

      // If this lang file is not found the plugin will fail to
      // load correctly and nothing will work.

//	echo '      HTMLArea.loadPlugin("TableOperations");' .BR;
//	echo '      HTMLArea.loadPlugin("SpellChecker");' .BR;
//	echo '</script>' .BR;
;} ?>
