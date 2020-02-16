<?php
/*
  XML connection method with Canada Post. 
  Before using this module, you should open a Canada Post SellOnline Eparcel Account,
  and set your Canada Post CPCID in this module's admin settings to the SellOnline ID given to you.

  Released under the GNU General Public License

  Adapted from OSC 2.2 MS2 version released under GPL by Copyright (c) 2002,2003 Kelvin Zhang (kelvin@syngear.com), Kenneth Wang (kenneth@cqww.net) 2002.11.12, 3.6 for OSC 2.2 MS2 with LXWXH added by Tom St.Croix (management@betterthannature.com)
*/

define('MODULE_SHIPPING_CANADAPOST_TEXT_TITLE', 'Canada Post');
define('MODULE_SHIPPING_CANADAPOST_TEXT_DESCRIPTION', 'Canada Post Parcel Service<p><strong>CPC Profile Information </strong>can be obtained at https://sellonline.canadapost.ca<br /><a href=https://sellonline.canadapost.ca/servlet/LogonServlet?Language=0 target="_blank">> Modify my profile <</a>');

define('MODULE_SHIPPING_PACKAGING_RESULTS', ' box(es) to be shipped');
//define('MODULE_SHIPPING_PACKAGING_RESULTS', ' box(es), total weight ');

define('MODULE_SHIPPING_CANADAPOST_CALC_ERROR','An unknown error occured with the Canada Post shipping calculations.');
define('MODULE_SHIPPING_CANADAPOST_ERROR_INFO','<br>If you prefer to use Canada Post as your shipping method, please contact the '.STORE_NAME.' via <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'"><u>Email</U></a>.');
define('MODULE_SHIPPING_CANADAPOST_COMM_ERROR','Cannot reach Canada Post Server. You may refresh this page (Press F5 on your keyboard) to try again.');
