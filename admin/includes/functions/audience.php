<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
//  $Id: audience.php,v 1.5 2005/10/06 21:01:45 spiderr Exp $
//
// This should be turned into a class...

  function get_audiences_list($query_category='email', $display_count="") {
  // used to display drop-down list of available audiences in emailing modules:
  // ie: mail, gv_main, coupon_admin... and eventually newsletters too.
  // gets info from query_builder table

  global $db, $gBitCustomer;
  include_once(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/' . 'audience.php');  //$current_page
  $count_array = array();
  $count=0;
  if ($display_count=="") $display_count=AUDIENCE_SELECT_DISPLAY_COUNTS;

  // get list of queries in database table, based on category supplied
  $queries_list = $db->Execute("select query_name, query_string from " . TABLE_QUERY_BUILDER . " " .
                  "where query_category like '%" . $query_category . "%'");

    $audience_list = array();
    if ($queries_list->RecordCount() > 1) {  // if more than one query record found
      $audience_list[] = array('id' => '', 'text' => TEXT_SELECT_AN_OPTION); //provide a "not-selected" value
  }

  reset($queries_list);
  while (!$queries_list->EOF) {
    // if requested, show recordcounts at end of descriptions of each entry
    // This could slow things down considerably, so use sparingly !!!!
    if ($display_count=='true' || $display_count ==true ) {  // if it's literal 'true' or logical true
    $count_array = $db->Execute(parsed_query_string($queries_list->fields['query_string']) );
    $count = $count_array->RecordCount();
    }

    // generate an array consisting of 2 columns which are identical. Key and Text are same.
    // Thus, when the array is used in a Select Box, the key is the same as the displayed description
    // The key can then be used to get the actual select SQL statement using the get...addresses_query function, below.
      $audience_list[] = array('id' => $queries_list->fields['query_name'], 'text' => $queries_list->fields['query_name'] . ' (' . $count . ')');
    $queries_list->MoveNext();
  }

  //if this is called by an emailing module which offers individual customers as an option, add all customers email addresses as well.
  if ($query_category=='email') {
    $customers_values = $db->Execute("select customers_email_address, customers_firstname, customers_lastname " .
                  "from " . TABLE_CUSTOMERS . " WHERE customers_email_format != 'NONE' " .
                  "order by customers_lastname, customers_firstname, customers_email_address");
    while(!$customers_values->EOF) {
      $audience_list[] = array('id' => $customers_values->fields['customers_email_address'],
                 'text' => $customers_values->fields['customers_lastname'] . ', ' . $customers_values->fields['customers_firstname'] . ' (' . $customers_values->fields['customers_email_address'] . ')');
      $customers_values->MoveNext();
    }
  }
  // send back the array for display in the SELECT drop-down menu
  return $audience_list;
  }

  function get_audience_sql_query($selected_entry, $query_category='email') {
    // This is used to take the query_name selected in the drop-down menu or singular customer email address and
  // generate the SQL Select query to be used to build the list of email addresses to be sent to
  // it only returns a query name and query string (SQL SELECT statement)
  // the query string is then used in a $db->Execute() command for later parsing and emailing.
  global $db;
  $query_name='';
  $queries_list = $db->Execute("select query_name, query_string from " . TABLE_QUERY_BUILDER . " " .
                 "where query_category like '%" . $query_category . "%'");
//                 "where query_category = '" . $query_category . "'");

  while (!$queries_list->EOF) {
      if ($selected_entry == $queries_list->fields['query_name']) {
      $query_name   = $queries_list->fields['query_name'];
        $query_string = parsed_query_string($queries_list->fields['query_string']);
//echo 'GET_AUD_EM_ADDR_QRY:<br />query_name='.$query_name.'<br />query_string='.$query_string;
      }
    $queries_list->MoveNext();
  }
  //if no match found against queries listed in database, then $selected_entry must be an email address
  if ($query_name=='' && $query_category=='email') {
        $cust_email_address = zen_db_prepare_input($selected_entry);
        $query_name   = $cust_email_address;
        $query_string = "select customers_firstname, customers_lastname, customers_email_address
                              from " . TABLE_CUSTOMERS . "
                              where customers_email_address = '" . zen_db_input($cust_email_address) . "'";
    }
  //send back a 1-row array containing the query_name and the SQL query_string
  return array('query_name'=>$query_name, 'query_string'=>$query_string);
}

function parsed_query_string($read_string) {
   // extract table names from sql strings, so that prefixes are supported.
   // this will also in the future be used to reconstruct queries from query_keys_list field in query_builder table.

  $allwords = explode( " ", $read_string );
  reset( $allwords );
  while( list( $key, $val ) = each( $allwords ) ) {
    // find "{TABLE_" and extract that tablename
    if( substr( $val, 0, 7) == "{TABLE_"  && substr( $val, -1) == "}" ) { //check for leading and trailing {} braces
    $val = substr( $val, 2, strlen($val)-2);  // strip off braces.  Could also use str_replace(array('{','}'),'',$val);
    //now return the value of the CONSTANT with the name that $val has.  ie: TABLE_CUSTOMERS = zen_customers
    $val = constant($val);
    } elseif ( substr( $val, 0, 6) == "TABLE_" ) {
    //return the value of the CONSTANT with the name that $val has.  ie: TABLE_CUSTOMERS = zen_customers
    $val = constant($val);
    }
    $good_string .= $val.' ';
   }
   $days = array();

   if( preg_match( '/zen_db_offset_date\(\s*(\d*)\s*\)*/', $good_string, $days ) ) {
     global $db;
     $offsetSql = $db->OffsetDate( $days[1] );
   }
   $good_string = preg_replace( '/zen_db_offset_date\(\s*\d*\s*\)*/', $offsetSql, $good_string );
   return $good_string;
}

  function legacy_get_audiences_list($customers_email_address, $query_category='email') {
  // used to display drop-down list of available audiences in emailing modules: mail, gv_main, coupon_admin... and eventually newsletters too.
    include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/' . 'audience.php');  //$current_page

    $customers = array();
    $customers[] = array('id' => '', 'text' => TEXT_SELECT_AN_OPTION);
    $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
    $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
    $customers_values = $db->Execute("select customers_email_address, customers_firstname, customers_lastname
                                from " . TABLE_CUSTOMERS . "
                                order by customers_lastname, customers_firstname, customers_email_address");

    while(!$customers_values->EOF) {
      $customers[] = array('id' => $customers_values->fields['customers_email_address'],
                           'text' => $customers_values->fields['customers_lastname'] . ', ' . $customers_values->fields['customers_firstname'] . ' (' . $customers_values->fields['customers_email_address'] . ')');
      $customers_values->MoveNext();
    }
  return $customers;
  }

  function legacy_get_audience_sql_query($customers_email_address, $query_category='email') {

    switch ($customers_email_address) {
      case '***':  //All Customers
        $query_name   = TEXT_ALL_CUSTOMERS;
        $query_string = "select customers_firstname, customers_lastname, customers_email_address
                              from " . TABLE_CUSTOMERS;
        break;

      case '**D': //All newsletter subscribers
        $query_name = TEXT_NEWSLETTER_CUSTOMERS;
        $query_string = "select customers_firstname, customers_lastname, customers_email_address
                              from " . TABLE_CUSTOMERS . "
                              where customers_newsletter = '1'";
        break;

      default: // singular email address supplied
        $cust_email_address = zen_db_prepare_input($customers_email_address);
        $query_name   = $cust_email_address;
        $query_string = "select customers_firstname, customers_lastname, customers_email_address
                              from " . TABLE_CUSTOMERS . "
                              where customers_email_address = '" . zen_db_input($cust_email_address) . "'";
        break;
    }
  return array('query_name'=>$query_name, 'query_string'=>$query_string);
}

?>
