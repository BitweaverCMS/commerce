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
// $Id: media_manager.php,v 1.4 2006/12/19 00:11:33 spiderr Exp $
//
   global $gBitDb;
   $zv_collection_query = "select * from " . TABLE_MEDIA_TO_PRODUCTS . " 
                           where product_id = '" . (int)$_GET['products_id'] . "'";
   $zq_collections = $gBitDb->Execute($zv_collection_query);
   $zv_product_has_media = false;
   if ($zq_collections->RecordCount() > 0) {
     $zv_product_has_media = true;
     while (!$zq_collections->EOF) {
       $zf_media_manager_query = "select * from " . TABLE_MEDIA_MANAGER . " 
                                  where media_id = '" . $zq_collections->fields['media_id'] . "'";
 
       $zq_media_manager = $gBitDb->Execute($zf_media_manager_query);
       $za_media_manager[$zq_media_manager->fields['media_id']] = array(
                                   'text' => $zq_media_manager->fields['media_name']);
       if ($zq_collections->RecordCount() < 1) {
         $zv_product_has_media = false;
       } else {
         $zv_clips_query = "select * from " . TABLE_MEDIA_CLIPS . " 
                            where media_id = '" . $zq_media_manager->fields['media_id'] . "'";

         $zq_clips = $gBitDb->Execute($zv_clips_query);
         while (!$zq_clips->EOF) {
           
           $zf_clip_type_query = "select * from " . TABLE_MEDIA_TYPES . " 
                                  where `type_id` = '" . $zq_clips->fields['clip_type'] . "'";

           $zq_clip_type = $gBitDb->Execute($zf_clip_type_query);

           $za_media_manager[$zq_media_manager->fields['media_id']]['clips'][$zq_clips->fields['clip_id']] = 
                              array('clip_filename' => $zq_clips->fields['clip_filename'], 'clip_type' => $zq_clip_type->fields['type_name']);
           $zq_clips->MoveNext();
         }
       }
       $zq_collections->MoveNext();
     }
   }
?>
