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
// $Id: page_4.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

define('NAVBAR_TITLE', 'Tutorial-Text &amp; Titles');
define('HEADING_TITLE', 'Text &amp; Titles');

define('NAVBAR_TITLE', 'Tutorial-Text &amp; Titles');
define('HEADING_TITLE', 'Text &amp; Titles');

define('TEXT_BACK', '&laquo; Sideboxes &amp; Buttons');

define('TEXT_INFORMATION', '<h4> G. Changing the Text &amp; Titles </h4>
<p>All of the text on each page is dynamically generated and can be easily changed. 
  Begin by locating the language file that has the same name as the page or module 
  you want to change. For example, this is the \'page 4\' page and the text is 
  in: <strong>/includes/languages/YOUR LANGUAGE/page_4.php</strong></p>
<p> You can edit the file with your favorite text editor and then upload it or 
  you can use the Admin for the main languages files. (Some language files are 
  not available through the Admin)</p>
<p> Each page\'s language file has one or more "defines". Each define contains 
  a constant that is used on the page. The constants may be a single word or a 
  section of text. Each define is set-up like: <strong>define(\'I_AM_A_CONSTANT\', 
  \'<span class="messageStackSuccess">I output this in your browser.</span>\');</strong> 
</p>
<p>The text between the 2 single quotes (the area in the green box) can be modified 
  or removed. You may use HTML tags or CSS classes to modify and format the text, 
  you can insert URL links or add images (be sure to use relative paths).</p>
<p><strong>Caution: If you use a contraction (can\'t, I\'d, Sara\'s), you must 
  put an "escape" in front of the apostrophe. The escape is a backslash "\".</strong> 
</p>
<p>If you do not want to show something that is in a define, simply delete the 
  text from between the 2 single quotes. For example: <strong>define(\'I_AM_A_CONSTANT\', 
  \'\');</strong> </p>
<p><span class="productSpecialPrice"><strong>NOTE: Do not remove the define from 
  the language file or you will break the code.</strong></span></p>
<p> For more information on the PHP define() function, visit<a href="http://www.php.net/define" target="_blank"> 
  PHP Net</a>. </p>
<p>To use the Admin to edit the main language files, open the Admin, point your 
  cursor at Localization, in the drop down click Languages. On the page that comes 
  up choose your language and click the \'details\' button. From this page choose 
  the file you want to edit. </p>');
?>