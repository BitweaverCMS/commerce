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
//  $Id: newsletter.php,v 1.3 2005/10/06 21:01:47 spiderr Exp $
//

  class newsletter {
    var $show_choose_audience, $title, $content, $content_html, $queryname;

    function newsletter($title, $content, $content_html, $queryname='') {
      $this->show_choose_audience = true;
//      $this->show_choose_audience = (count(get_audiences_list('newsletters')) > 1 );    //if only 1 list of newsletters, don't offer selection
      $this->title = $title;
      $this->content = $content;
      $this->content_html = $content_html;
    $this->query_name = $queryname;
    }

    function choose_audience() {
      global $_GET;

      $choose_audience_string = '<form name="audience" action="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm') .'" method="post" onsubmit="return check_form(audience);">' .
                 ' <table border="0" cellspacing="0" cellpadding="2">' . "\n" .
                                 '  <tr>' . "\n" .
                 '<td class="main">' . TEXT_PLEASE_SELECT_AUDIENCE .'<br />' .
                         '' . zen_draw_pull_down_menu('audience_selected', get_audiences_list('newsletters'), $this->query_name) . '</td>' .
                                 '  </tr>' . "\n" .
                                 '  <tr>' . "\n" .
                                 '   <td colspan="2" align="right">' . zen_image_submit('button_select.gif', IMAGE_SELECT) . '</td>' .
                                 '  </tr>' . "\n" .
                                 '</table></form>';

      return $choose_audience_string;
    }


    function confirm() {
      global $_GET, $_POST, $db;

    if ($_POST['audience_selected']) {
          $this->query_name=$_POST['audience_selected'];
        if (is_array($_POST['audience_selected']))  $this->query_name=$_POST['audience_selected']['text'];
      }

      $query_array = get_audience_sql_query($this->query_name, 'newsletters');
      $mail = $db->Execute($query_array['query_string'] );
      $confirm_string = '<table border="0" cellspacing="0" cellpadding="2">' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><font color="#ff0000"><b>' . sprintf(TEXT_COUNT_CUSTOMERS, $mail->RecordCount() ) . '</b></font></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><b>' . $this->title . '</b></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '<hr /></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . nl2br($this->content_html) . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td><hr>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><tt>' . nl2br($this->content) . '</tt><hr /></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
            '<form name="ready_to_send" action="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm_send') .'" method="post" >' .
                        '    <td align="right"> ' . zen_draw_hidden_field('audience_selected',$this->query_name).
            zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL) .
            '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a></td>' . "\n" .
                        '</form>' .
                        '  </tr>' . "\n" .
                        '</table>';

      return $confirm_string;
    }

    function send($newsletter_id) {
      global $db;
      $audience_select = get_audience_sql_query($this->query_name, 'newsletters');
      $audience = $db->Execute($audience_select['query_string']);
      $records = $audience->RecordCount();
      if ($records==0) return 0;
    $i=0;

      while (!$audience->EOF) {
    $i++;
      $html_msg['EMAIL_FIRST_NAME'] = $audience->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $audience->fields['customers_lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = $this->content_html;
      zen_mail($audience->fields['customers_firstname'] . ' ' . $audience->fields['customers_lastname'], $audience->fields['customers_email_address'], $this->title, $this->content, STORE_NAME, EMAIL_FROM, $html_msg, 'newsletters');
      echo zen_image(DIR_WS_ICONS . 'tick.gif', $audience->fields['customers_email_address']);

      //force output to the screen to show status indicator each time a message is sent...
      ob_flush();
      flush();

      $audience->MoveNext();
      }

      $newsletter_id = zen_db_prepare_input($newsletter_id);
      $db->Execute("update " . TABLE_NEWSLETTERS . "
                    set date_sent = now(), status = '1'
                    where newsletters_id = '" . zen_db_input($newsletter_id) . "'");
     return $records;  //return number of records processed whether successful or not
  }
  }
?>