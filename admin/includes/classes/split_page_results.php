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
//  $Id$
//

  class splitPageResults {
    function splitPageResults(&$current_page_number, $max_rows_per_page, &$sql_query, &$query_num_rows) {
      global $gBitDb;
      if (empty($current_page_number)) $current_page_number = 1;

      $pos_to = strlen($sql_query);
      $pos_from = strpos($sql_query, ' from', 0);

      $pos_group_by = strpos($sql_query, ' group by', $pos_from);
      if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

      $pos_having = strpos($sql_query, ' having', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

      $pos_order_by = strpos($sql_query, ' order by', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

      $reviews_count = $gBitDb->Execute("select count(*) as `total` " .
                                            substr($sql_query, $pos_from, ($pos_to - $pos_from)));

      $query_num_rows = $reviews_count->fields['total'];

      $num_pages = ceil($query_num_rows / $max_rows_per_page);
      if ($current_page_number > $num_pages) {
        $current_page_number = $num_pages;
      }
      $offset = ($max_rows_per_page * ($current_page_number - 1));

// fix offset error on some versions
      if ($offset < 0) { $offset = 0; }

	  return $offset;
    }

    function display_links($query_numrows, $max_rows_per_page, $max_page_links, $current_page_number, $parameters = '', $page_name = 'page') {
      

      if ( zen_not_null($parameters) && (substr($parameters, -1) != '&') ) $parameters .= '&';

// calculate number of pages needing links
      $num_pages = ceil($query_numrows / $max_rows_per_page);

      $pages_array = array();
      for ($i=1; $i<=$num_pages; $i++) {
        $pages_array[] = array('id' => $i, 'text' => $i);
      }

      if ($num_pages > 1) {
        $display_links = zen_draw_form_admin('pages', basename($_SERVER['SCRIPT_NAME']), '', 'get');
		$display_links .= '<ul class="pagination">';

        if ($current_page_number > 1) {
          $display_links .= '<li><a href="' . zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']), $parameters . $page_name . '=' . ($current_page_number - 1), 'NONSSL') . '" class="splitPageLink">' . PREVNEXT_BUTTON_PREV . '</a></li>';
        } else {
          $display_links .= '<li><a href="#">&laquo;</a></li>';
        }

        $display_links .= sprintf(TEXT_RESULT_PAGE, zen_draw_pull_down_menu($page_name, $pages_array, $current_page_number, 'onChange="this.form.submit();"'), $num_pages);

        if (($current_page_number < $num_pages) && ($num_pages != 1)) {
          $display_links .= '<li><a href="' . zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']), $parameters . $page_name . '=' . ($current_page_number + 1), 'NONSSL') . '" class="splitPageLink">' . PREVNEXT_BUTTON_NEXT . '</a></li>';
        } else {
          $display_links .= '<li><a href="#">&raquo;</a></li>';
        }

        if ($parameters != '') {
          if (substr($parameters, -1) == '&') $parameters = substr($parameters, 0, -1);
          $pairs = explode('&', $parameters);
          while (list(, $pair) = each($pairs)) {
            list($key,$value) = explode('=', $pair);
            $display_links .= zen_draw_hidden_field(rawurldecode($key), rawurldecode($value));
          }
        }

        if (SID) $display_links .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

		$display_links .= '</ul>';
        $display_links .= '</form>';
      } else {
        $display_links = sprintf(TEXT_RESULT_PAGE, $num_pages, $num_pages);
      }

      return $display_links;
    }

    function display_count($query_numrows, $max_rows_per_page, $current_page_number, $text_output) {
      $to_num = ($max_rows_per_page * $current_page_number);
      if ($to_num > $query_numrows) $to_num = $query_numrows;
      $from_num = ($max_rows_per_page * ($current_page_number - 1));
      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $from_num, $to_num, $query_numrows);
    }
  }
?>
