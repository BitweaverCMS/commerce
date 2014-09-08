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

/*
  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error');
  $messageStack->add('Error: Error 2', 'warning');
  if ($messageStack->size > 0) echo $messageStack->output();
*/

  class messageStack extends tableBlock {
    var $size = 0;

    function messageStack() {

      $this->errors = array();

      if( isset( $_SESSION['messageToStack'] ) and $_SESSION['messageToStack'] != '' ) {
        for ($i = 0, $n = sizeof($_SESSION['messageToStack']); $i < $n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        $_SESSION['messageToStack'] = '';
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $this->errors[] = array('params' => 'class="alert alert-danger"', 'text' => '<i class=" icon-minus-sign"></i> ' . $message);
      } elseif ($type == 'warning') {
        $this->errors[] = array('params' => 'class="alert alert-warning"', 'text' => '<i class=" icon-exclamation-sign"></i> ' . $message);
      } elseif ($type == 'success') {
        $this->errors[] = array('params' => 'class="alert alert-success"', 'text' => '<i class="icon-ok-sign"></i> ' . $message);
      } elseif ($type == 'caution') {
        $this->errors[] = array('params' => 'class="alert alert-info"', 'text' => '<i class="icon-info-sign"></i> ' . $message);
      } else {
        $this->errors[] = array('params' => 'class="alert alert-danger"', 'text' => $message);
      }


      $this->size++;
    }

    function add_session($message, $type = 'error') {

      if( empty( $_SESSION['messageToStack'] ) ) {
        $_SESSION['messageToStack'] = array();
      }

      $_SESSION['messageToStack'][] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
		$ret = '';
		foreach( $this->errors as $error ) {
			$ret .= '<div '.$error['params'].'>'.$error['text'].'</div>';
		}
		return $ret;
    }
  }
?>
