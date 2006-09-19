<?php
//
// $Id: index.php,v 1.4 2006/09/19 07:12:59 spiderr Exp $
//

$gBitSmarty->assign( 'mainDisplayBlocks', $gBitDb->getAll(SQL_SHOW_PRODUCT_INFO_MAIN) );

?>
