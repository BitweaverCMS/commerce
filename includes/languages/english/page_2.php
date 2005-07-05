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
// $Id: page_2.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

define('NAVBAR_TITLE', 'Tutorial-Colors, Text &amp; the Header');
define('HEADING_TITLE', 'Colors, Text &amp; the Header');

define('TEXT_BACK', '&laquo; Index');
define('TEXT_NEXT', 'Sideboxes &amp; Buttons &raquo;');


define('TEXT_INFORMATION', '
<h4> B. Changing the Colors &amp; Fonts </h4>
<p>Start by opening the style sheet in your favorite text editor: <strong>/includes/themes/template_custom/css/stylesheet.css</strong> 
  All of the pages are broken into smaller pieces called "classes" and each class 
  has a style. The class styles are used to control the look of your fonts, colors, 
  text size, borders, background images, etc. Change the colors by substituting 
  standard HTML color numbers for the text and background colors. Change the fonts 
  by increasing or decreasing the size, change the font by using a different font 
  name.</p>
<p>To remove a CSS element, such as a border, simply comment out the line you 
  don\'t want to use with a "/*" (slash, asterisk) at the beginning of the line 
  and "*/" (asterisk, slash) at the end of the line. After making your changes 
  upload the stylesheet to the directory, refresh your browser and admire your 
  handywork.</p>
<p>To keep the code clean, you may want to remove commented lines after you are done modifying the stylesheet.</p>
<p> For more information on Cascading Style Sheets visit the Zen Cart&trade; <a href="http://www.zen-cart.com/modules/mylinks/">Online 
  Resources</a> for links to tutorials and references.</p>
<h4> C. Changing the Navigation Bar &amp; Sidebox Header Background Images</h4>
<p> To change the images, upload your navigation bar and sidebox header images 
  to the image directory: <strong>includes/themes/template_custom/images/</strong> Either name your images 
  to match the .css file or rename the images in the stylesheet. You can use GIF, 
  JPEG or PNG images.</p>
<p><strong>NOTE: Do not change the relative paths to the images to the full URL 
  or you will create security warnings when in SSL mode.</strong></p>
<h4> D. Changing the Header </h4>
<p>To change the images, upload your logo and header background images to the 
  image directory: <strong>includes/themes/template_custom/images/</strong> Either 
  name your images to match the .css file or rename the images in the stylesheet. 
  You can use GIF, JPEG or PNG images. See "Changing the Text and Titles" for 
  instructions on changing headline in the header.</p>
');
?>