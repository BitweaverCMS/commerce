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
// $Id: message_stack.php,v 1.6 2007/10/31 11:24:24 spiderr Exp $
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
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'type'=>$type, 'text' => $message);
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

      $this->table_data_parameters = 'class="messageBox"';
	  $ret = '<div class="clear formfeedback"><ul>';
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
	  	$ret .= '<li class="'.$this->messages[$i]['type'].'">'.$this->messages[$i]['text'].'</li>';
      }
	  $ret .= '</ul></div>';

		return $ret;
    }

	function size($class=NULL) {
		$n = sizeof( $this->messages );
		if( $class ) {
			for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
				if( $this->messages[$i]['class'] == $class) {
					$count++;
				}
			}
		} else {
			$count = $n;
		}
		return $count;
	}
  }
?>
