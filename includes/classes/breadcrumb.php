<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce										|
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers						 	|
// |																	|
// | http://www.zen-cart.com/index.php									|
// |																	|
// | Portions Copyright (c) 2003 osCommerce								|
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,		|
// | that is bundled with this package in the file LICENSE, and is		|
// | available through the world-wide-web at the following url:			|
// | http://www.zen-cart.com/license/2_0.txt.							|
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to		|
// | license@zen-cart.com so we can mail you a copy immediately.		|
// +----------------------------------------------------------------------+
// $Id$
//

class breadcrumb {
	var $_trail;

	function breadcrumb() {
		$this->reset();
	}

	function reset() {
		$this->_trail = array();
	}

	function add($title, $link = '') {
		$this->_trail[] = array('title' => $title, 'link' => $link);
	}

	function trail($separator = '&nbsp;&nbsp;') {
		$trail_string = '';

		for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
			if (isset($this->_trail[$i]['link']) && zen_not_null($this->_trail[$i]['link'])) {
				$trail_string .= '<a class="crumb'.$i.'" href="' . $this->_trail[$i]['link'] . '">' . $this->_trail[$i]['title'] . '</a>';
			} else {
				$trail_string .= $this->_trail[$i]['title'];
			}

			if (($i+1) < $n) $trail_string .= $separator;
		}

		return $trail_string;
	}

	function last() {
		$trail_size = sizeof($this->_trail);
		return $this->_trail[$trail_size-1]['title'];
	}
}
