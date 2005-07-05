<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: sniffer.php,v 1.2 2005/07/05 16:44:05 spiderr Exp $
//
/**
 * Sniffer Class.
 * This class is used to collect information on the system that Zen Cart is running on
 * and to return error reports
 * @package ZenCart_Classes
*/
  class sniffer {

    function sniffer() {
      $this->browser = Array();
      $this->php = Array();
      $this->server = Array();
      $this->database = Array();
      $this->phpBB = Array();
      $this->get_phpBB_info();
    }

    function get_phpBB_info() {
      $this->phpBB['db_installed']=false;
      $this->phpBB['files_installed']=false;
      $this->phpBB['phpbb_path']='';
      $this->phpBB['phpbb_url']='';
      $this->phpBB['installed']=false;

      if (PHPBB_LINKS_ENABLED !='true') {  // if disabled in Zen Cart admin, don't do any checks for phpBB
        if ( !empty( $debug1) ) echo "phpBB connection disabled in Admin<br>";
        return false;
      }

      $debug1=false;
      //calculate the path from root of server for absolute path info
      $script_filename = $_SERVER['PATH_TRANSLATED'];
      if (empty($script_filename)) $script_filename = $_SERVER['SCRIPT_FILENAME'];
      $script_filename = str_replace(array('\\', '//'), '/', $script_filename);  //convert slashes
      $dir_phpbb = str_replace(array('\\', '//'), '/', DIR_WS_PHPBB ); // convert slashes

      if (substr($dir_phpbb,-1)!='/') $dir_phpbb .= '/'; // ensure has a trailing slash
      if ($debug1==true) echo 'dir='.$dir_phpbb.'<br>';

      //check if file exists
      if (@file_exists($dir_phpbb . 'config.php')) {
        $this->phpBB['files_installed'] = true;
        if ($debug1==true) echo "files_installed<br>";
        // if exists, also store it for future use
        $this->phpBB['phpbb_path'] = $dir_phpbb;
        if ($debug1==true) echo 'phpbb_path='. $dir_phpbb . '<br><br>';

       // find phpbb table prefix without including file:
        $lines = array();
        $lines = @file($this->phpBB['phpbb_path']. 'config.php');
        foreach($lines as $line) { // read the configure.php file for specific variables
				if ($debug1==true && strlen($line)>3 && substr($line,0,2)!='//') echo 'CONFIG.PHP-->'.$line.'<br>';
          if (substr($line,0,1)!='$') continue;
          if (substr_count($line,'"')>1) $delim='"';
          if (substr_count($line,"'")>1) $delim="'"; // determine whether single or double quotes used in this line.
          $def_string=array();
          $def_string=explode($delim,$line);
          if (substr($line,0,7)=='$dbhost') $this->phpBB['dbhost'] = $def_string[1];
          if (substr($line,0,7)=='$dbname') $this->phpBB['dbname'] = $def_string[1];
          if (substr($line,0,7)=='$dbuser') $this->phpBB['dbuser'] = $def_string[1];
          if (substr($line,0,9)=='$dbpasswd') $this->phpBB['dbpasswd'] = $def_string[1];
          if (substr($line,0,13)=='$table_prefix') $this->phpBB['table_prefix'] = $def_string[1];
        }//end foreach $line
       // find phpbb table-names without INCLUDEing file:
        if (!@file_exists($dir_phpbb . 'includes/constants.php')) $this->phpBB['files_installed'] = false;
        $lines = array();
        $lines = @file($this->phpBB['phpbb_path']. 'includes/constants.php');
        foreach($lines as $line) { // read the configure.php file for specific variables
          if (substr_count($line,'define(')<1) continue;
          if ($debug1==true && strlen($line)>3 && substr($line,0,1)!='/') echo 'CONSTANTS.PHP-->'.$line.'<br>';
          if (substr_count($line,'"')>1) $delim='"';
          if (substr_count($line,"'")>1) $delim="'"; // determine whether single or double quotes used in this line.
          $def_string=array();
          $def_string=explode($delim,$line);
          if ($def_string[1]=='USERS_TABLE')      $this->phpBB['users_table'] = $this->phpBB['table_prefix'] . $def_string[3];
          if ($def_string[1]=='USER_GROUP_TABLE') $this->phpBB['user_group_table'] = $this->phpBB['table_prefix'] . $def_string[3];
          if ($def_string[1]=='GROUPS_TABLE')     $this->phpBB['groups_table'] = $this->phpBB['table_prefix'] . $def_string[3];
          if ($def_string[1]=='CONFIG_TABLE')     $this->phpBB['config_table'] = $this->phpBB['table_prefix'] . $def_string[3];
        }//end foreach of $line
        if ($debug1==true) {
          echo 'prefix='.$this->phpBB['table_prefix'].'<br>';
          echo 'dbname='.$this->phpBB['dbname'].'<br>';
          echo 'dbuser='.$this->phpBB['dbuser'].'<br>';
          echo 'dbhost='.$this->phpBB['dbhost'].'<br>';
          echo 'dbpasswd='.$this->phpBB['dbpasswd'].'<br>';
          echo 'users_table='.$this->phpBB['users_table'].'<br>';
          echo 'user_group_table='.$this->phpBB['user_group_table'].'<br>';
          echo 'groups_table='.$this->phpBB['groups_table'].'<br>';
          echo 'config_table='.$this->phpBB['config_table'].'<br>';
        }
        // check if tables exist in database
        if ($this->phpBB['dbname']!='' && $this->phpBB['dbuser'] !='' && $this->phpBB['dbhost'] !='' && $this->phpBB['config_table']!='' && $this->phpBB['users_table'] !='' && $this->phpBB['user_group_table'] !='' && $this->phpBB['groups_table']!='') {
          if ($this->dbname == DB_DATABASE) {
            $this->phpBB['db_installed'] = $this->table_exists($this->phpBB['users_table']);
            $this->phpBB['db_installed_config'] = $this->table_exists($this->phpBB['config_table']);
            if ($debug1==true) echo "db_installed -- in ZC Database = ".$this->phpBB['db_installed']."<br>";
            } else {
            $this->phpBB['db_installed'] = $this->table_exists_phpbb($this->phpBB['users_table']);
            $this->phpBB['db_installed_config'] = $this->table_exists_phpbb($this->phpBB['config_table']);
            if ($debug1==true) echo "db_installed -- in separate database = ".$this->phpBB['db_installed']."<br>";
          }
        }

      }//endif @file_exists

      if ($debug1==true) echo "link_enabled_admin_status=".PHPBB_LINKS_ENABLED.'<br>';

      if ( ($this->phpBB['db_installed']) && ($this->phpBB['files_installed'])  && (PHPBB_LINKS_ENABLED=='true')) {
       //good so far. now let's check for relative path access so we can successfully "include" the config.php file when needed.
        if ($debug1==true) echo "ok, now let's check relative paths<br>";
        if ($debug1==true) echo 'docroot='.$_SERVER['DOCUMENT_ROOT'].'<br>';
        $this->phpBB['phpbb_url'] = str_replace(array($_SERVER['DOCUMENT_ROOT'],substr($script_filename,0,strpos($script_filename,$_SERVER['PHP_SELF']))),'',$dir_phpbb);
        $this->phpBB['installed'] = true;
        if ($debug1==true) echo 'URL='.$this->phpBB['phpbb_url'].'<br>';
        //if neither of the relative paths validate, the function still returns false for 'installed'.
      }
      if ($debug1==true && $this->phpBB['installed']==false) echo "FAILURE: phpBB NOT activated<br><br>";
     // will use $sniffer->phpBB['installed'] to check for suitability of calling phpBB in the future.
    }

    function table_exists($table_name) {
      global $db;

    // Check to see if the requested Zen Cart table exists
      $sql = "SHOW TABLES like '".$table_name."'";
      $tables = $db->Execute($sql);
//echo 'tables_found = '. $tables->RecordCount() .'<br>';
      if ($tables->RecordCount() > 0) {
        $found_table = true;
      }

//      $sql = "SHOW TABLES";
////      $tables = $db->execute($sql);
//      $found_table = false;
////      while (!$tables->EOF) {
////        list(,$table) = each($tables->fields);
////         if ($table == $table_name) {
////           $found_table = true;
////         }
////         $tables->MoveNext();
////      }
//

      return $found_table;
    }
    function table_exists_phpbb($table_name) {
      global $db;
    // Check to see if the requested PHPBB table exists, regardless of which database it's set to use
      $sql = "SHOW TABLES like '".$table_name."'";
      $db_phpbb = new queryFactory();
      $db_phpbb->connect($this->phpBB['dbhost'], $this->phpBB['dbuser'], $this->phpBB['dbpasswd'], $this->phpBB['dbname'], USE_PCONNECT, false);
      $tables = $db_phpbb->Execute($sql);
//echo 'tables_found = '. $tables->RecordCount() .'<br>';
      if ($tables->RecordCount() > 0) {
        $found_table = true;
      }
      $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);
      return $found_table;
    }

  }
?>