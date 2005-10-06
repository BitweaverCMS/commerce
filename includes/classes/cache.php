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
// $Id: cache.php,v 1.3 2005/10/06 21:01:47 spiderr Exp $
//

class cache {

  function sql_cache_exists($zf_query) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
        // where using a single directory at the moment. Need to look at splitting into subdirectories
	// like adodb
	if (file_exists(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql')) {
	  return true;
	} else {
          return false;
	}
      break;
      case 'database':
        $sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
	$zp_cache_exists = $db->Execute($sql);
	if ($zp_cache_exists->RecordCount() > 0) {
	  return true;
	} else {
          return false;
	}
      break;
      case 'memory':
        return false;
      break;
      case 'none':
        return false;
      break;
    }
  }

  function sql_cache_is_expired($zf_query, $zf_cachetime) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
        if (filemtime(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql') > (time() - $zf_cachetime)) {
	  return false;
	} else {
          return true;
	}
      break;
      case 'database':
        $sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name ."'";
	$cache_result = $db->Execute($sql);
	if ($cache_result->RecordCount() > 0) {
	  $start_time = $cache_result->fields['cache_entry_created'];
	  if (time() - $start_time > $zf_cachetime) return true;
	  return false;
	} else {
          return true;
	}
      break;
      case 'memory':
        return true;
      break;
      case 'none':
        return true;
      break;
    }
  }

  function sql_cache_expire_now($zf_query) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
        @unlink(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql');
        return true;
      break;
      case 'database':
        $sql = "delete from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
	$db->Execute($sql);
        return true;
      break;
      case 'memory':
        unset($this->cache_array[$zp_cache_name]);
        return true;
      break;
      case 'none':
        return true;
      break;
    }
  }

  function sql_cache_store($zf_query, $zf_result_array) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
        $OUTPUT = serialize($zf_result_array);
        $fp = fopen(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql',"w");
        fputs($fp, $OUTPUT);
        fclose($fp);
        return true;
      break;
      case 'database':
        $result_serialize = $db->prepare_input(serialize($zf_result_array));
	$sql = "insert into " . TABLE_DB_CACHE . " set cache_entry_name = '" . $zp_cache_name . "',
	                                               cache_data = '" . $result_serialize . "',
						       cache_entry_created = '" . time() . "'";
	$db->Execute($sql);
        return true;
      break;
      case 'memory':
        return true;
      break;
      case 'none':
        return true;
      break;
    }
  }

  function sql_cache_read($zf_query) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
        $zp_fa = file(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql');
	$zp_result_array = unserialize(implode('', $zp_fa));
        return $zp_result_array;
      break;
      case 'database':
	$sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
	$zp_cache_result = $db->Execute($sql);
	$zp_result_array = unserialize($zp_cache_result->fields['cache_data']);
        return $zp_result_array;
      break;
      case 'memory':
        return true;
      break;
      case 'none':
        return true;
      break;
    }
  }

  function sql_cache_flush_cache() {
    global $db;
    switch (SQL_CACHE_METHOD) {
      case 'file':
        if ($za_dir = @dir(DIR_FS_SQL_CACHE)) {
          while ($zv_file = $za_dir->read()) {
            if (strstr($zv_file, '.sql') && strstr($zv_file, 'zc_')) {
              @unlink(DIR_FS_SQL_CACHE . '/' . $zv_file);
            }
          }
        }
        return true;
      break;
      case 'database':
        $sql = "delete from " . TABLE_DB_CACHE;
	$db->Execute($sql);
        return true;
      break;
      case 'memory':
        return true;
      break;
      case 'none':
        return true;
      break;
    }
  }

  function cache_generate_cache_name($zf_query) {
    switch (SQL_CACHE_METHOD) {
      case 'file':
        return 'zc_' . md5($zf_query);
      break;
      case 'database':
        return 'zc_' . md5($zf_query);
      break;
      case 'memory':
        return 'zc_' . md5($zf_query);
      break;
      case 'none':
        return true;
      break;
    }
  }
}
?>