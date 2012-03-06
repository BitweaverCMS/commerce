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
// $Id$
//

define('NAVBAR_TITLE', tra( 'Reviews' ) );

define('SUB_TITLE_FROM', tra( 'From:' ) );
define('SUB_TITLE_REVIEW', tra( 'Your Review:' ) );
define('SUB_TITLE_RATING', tra( 'Rating:' ) );

define('TEXT_NO_HTML', tra( '<span class="coming"><strong>NOTE:</strong></span>&nbsp;HTML tags are not allowed.' ) );
define('TEXT_BAD', tra( '<span class="coming"><strong>BAD</strong></span>' ) );
define('TEXT_GOOD', tra( '<span class="coming"><strong>GOOD</strong></span>' ) );

define('TEXT_PRODUCT_INFO', tra( '<strong>Product Information</strong>' ) );

define('TEXT_APPROVAL_REQUIRED', tra( '<span class="coming"><strong>NOTE:</strong></span>&nbsp;Reviews require prior approval before they will be displayed' ) );

define('EMAIL_REVIEW_PENDING_SUBJECT', tra( 'Product Review Pending Approval: %s' ) );
define('EMAIL_PRODUCT_REVIEW_CONTENT_INTRO','A Product Review for %s has been submitted and requires your approval.'."\n\n");
define('EMAIL_PRODUCT_REVIEW_CONTENT_DETAILS', tra( 'Review Details: %s' ) );

?>
