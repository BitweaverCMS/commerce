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
// $Id: sessions.php,v 1.3 2005/08/24 15:06:37 lsces Exp $
//
/**
 * @package ZenCart_Functions
*/

  if (STORE_SESSIONS == 'db') {
    if (defined('DIR_WS_ADMIN')) {
      if (!$SESS_LIFE = (SESSION_TIMEOUT_ADMIN + 900)) {
        $SESS_LIFE = (SESSION_TIMEOUT_ADMIN + 900);
      }
    } else {
      if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
        $SESS_LIFE = 1440;
      }
    }

    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      global $db;
      $qid = "select value
              from " . TABLE_SESSIONS . "
              where sesskey = '" . zen_db_input($key) . "'
              and expiry > '" . time() . "'";

      $value = $db->Execute($qid);

      if ($value->fields['value']) {
        return $value->fields['value'];
      }

      return ("");
    }

    function _sess_write($key, $val) {
      global $db;
      global $SESS_LIFE;

      $expiry = time() + $SESS_LIFE;
      $value = $val;

      $qid = "select count(*) as `total`
              from " . TABLE_SESSIONS . "
              where sesskey = '" . zen_db_input($key) . "'";

      $total = $db->Execute($qid);

      if ($total->fields['total'] > 0) {
        $sql = "update " . TABLE_SESSIONS . "
                set expiry = '" . zen_db_input($expiry) . "', value = '" . zen_db_input($value) . "'
                where sesskey = '" . zen_db_input($key) . "'";

        return $db->Execute($sql);

      } else {
        $sql = "insert into " . TABLE_SESSIONS . "
                values ('" . zen_db_input($key) . "', '" . zen_db_input($expiry) . "', '" .
                         zen_db_input($value) . "')";

        return $db->Execute($sql);

      }
    }

    function _sess_destroy($key) {
      global $db;
      $sql = "delete from " . TABLE_SESSIONS . " where sesskey = '" . zen_db_input($key) . "'";
      return $db->Execute($sql);
    }

    function _sess_gc($maxlifetime) {
      global $db;
      $sql = "delete from " . TABLE_SESSIONS . " where expiry < '" . time() . "'";
      $db->Execute($sql);
      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function zen_session_start() {
    if (defined('DIR_WS_ADMIN')) {
      @ini_set('session.gc_maxlifetime', (SESSION_TIMEOUT_ADMIN < 900 ? (SESSION_TIMEOUT_ADMIN + 900) : SESSION_TIMEOUT_ADMIN));
    }
    return TRUE;
  }

  function zen_session_register($variable) {
    die('This function has been deprecated. Please use Register Globals Off compatible code');
  }

  function zen_session_is_registered($variable) {
    die('This function has been deprecated. Please use Register Globals Off compatible code');
  }

  function zen_session_unregister($variable) {
    die('This function has been deprecated. Please use Register Globals Off compatible code');
  }

  function zen_session_id($sessid = '') {
    if (!empty($sessid)) {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function zen_session_name($name = '') {
    if (!empty($name)) {
      return session_name($name);
    } else {
      return session_name();
    }
  }

  function zen_session_close() {
    if (function_exists('session_close')) {
      return session_close();
    }
  }

  function zen_session_destroy() {
    return session_destroy();
  }

  function zen_session_save_path($path = '') {
    if (!empty($path)) {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }

  function zen_session_recreate() {
    if (PHP_VERSION >= 4.1) {
      $session_backup = $_SESSION;

      unset($_COOKIE[zen_session_name()]);

      zen_session_destroy();

      if (STORE_SESSIONS == 'db') {
        session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
      }

      zen_session_start();

      $_SESSION = $session_backup;
      unset($session_backup);
    }
  }
?>