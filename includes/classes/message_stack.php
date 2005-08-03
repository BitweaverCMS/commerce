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
// $Id: message_stack.php,v 1.3 2005/08/03 13:04:38 spiderr Exp $
//

  class messageStack extends tableBox {

// class constructor
    function messageStack() {

      $this->messages = array();

      if( !empty( $_SESSION['messageToStack'] ) ) {
        $messageToStack = $_SESSION['messageToStack'];
        for ($i=0, $n=sizeof($messageToStack); $i<$n; $i++) {
          $this->add($messageToStack[$i]['class'], $messageToStack[$i]['text'], $messageToStack[$i]['type']);
        }
        $_SESSION['messageToStack']= '';
      }
    }

// class methods
    function add($class, $message, $type = 'error') {
      global $template, $current_page_base;
      if ($type == 'error') {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => zen_image($template->get_template_dir('error.gif', DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . 'error.gif', ICON_ERROR) . '&nbsp;' . $message);
      } elseif ($type == 'warning') {
        $this->messages[] = array('params' => 'class="messageStackWarning"', 'class' => $class, 'text' => zen_image($template->get_template_dir('warning.gif', DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . 'warning.gif', ICON_WARNING) . '&nbsp;' . $message);
      } elseif ($type == 'success') {
        $this->messages[] = array('params' => 'class="messageStackSuccess"', 'class' => $class, 'text' => zen_image($template->get_template_dir('success.gif', DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . 'success.gif', ICON_SUCCESS) . '&nbsp;' . $message);
      } elseif ($type == 'caution') {
        $this->messages[] = array('params' => 'class="messageStackCaution"', 'class' => $class, 'text' => zen_image($template->get_template_dir('warning.gif', DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . 'warning.gif', ICON_WARNING) . '&nbsp;' . $message);
      } else {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => $message);
      }
    }

    function add_session($class, $message, $type = 'error') {

      if( empty( $_SESSION['messageToStack'] ) ) {
        $messageToStack = array();
      } else {
        $messageToStack = $_SESSION['messageToStack'];
      }

      $messageToStack[] = array('class' => $class, 'text' => $message, 'type' => $type);
      $_SESSION['messageToStack'] = $messageToStack;
      $this->add($class, $message, $type);
    }

    function reset() {
      $this->messages = array();
    }

    function output($class) {
      $this->table_data_parameters = 'class="messageBox"';

      $output = array();
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $output[] = $this->messages[$i];
        }
      }

      return $this->tableBox($output);
    }

    function size($class) {
      $count = 0;

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $count++;
        }
      }

      return $count;
    }
  }
?>