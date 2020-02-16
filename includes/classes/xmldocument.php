<?php
/*
	$Id: xmldocument.php,v 1.5 2003/06/27 01:03:03 torinwalker Exp $
	Written by Torin Walker
	torinwalker@rogers.com

	Original copyright (c) 2003 Torin Walker
	Copyright(c) 2003 by Torin Walker, All rights reserved.

	Released under the GNU General Public License
	This program is free software; you can redistribute it and/or modify it 
	under the terms of the GNU General Public License as published by the Free 
	Software Foundation; either version 2 of the License, or (at your option) 
	any later version. This program is distributed in the hope that it will be 
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
	Public License for more details. You should have received a copy of the 
	GNU General Public License along with this program; If not, you may obtain 
	one by writing to and requesting one from:
	The Free Software Foundation, Inc.,
	59 Temple Place, Suite 330,
	Boston, MA 02111-1307 USA
*/

define("ELEMENT", 0);
define("TEXTELEMENT", 1);

//*****************
class XMLDocument {
	protected $root,
			  $children;

	public function __construct() {
	}

	public function createElement($name) {
		$node = new Node();
		$node->setName($name);
		$node->setType(ELEMENT);
		return $node;
	}

	public function createTextElement($text) {
		$node = new Node();
		$node->setType(TEXTELEMENT);
		$node->setValue($text);
		return $node;
	}

	public function getRoot() {
		return (!empty($this->root)) ? $this->root : false;
	}

	public function setRoot(&$node) {
		$this->root = $node;
	}

	public function toString() {
		return (!empty($this->root)) ? $this->root->toString() : 'not set';
	}

	public function getValueByPath($path) {
		$pathArray = explode('/', $path);
		if ($pathArray[0] != $this->root->getName()) {
			return false;
		} else {
			array_shift($pathArray);
			$newPath = implode('/', $pathArray);
			return $this->root->getValueByPath($newPath);
		}
	}
}

//**********
class Node 
{
	protected $name,
			  $type,
			  $text,
			  $parent,
			  $children,
			  $attributes;

	public function __construct() {
		$this->children = array();
		$this->attributes = array();
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setParent(&$node) {
		$this->parent = &$node;
	}

	public function &getParent() {
		return $this->parent;
	}

	public function &getChildren() {
		return $this->children;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getElementByName($name) {
		for ($i = 0, $c = count($this->children); $i < $c; $i++) {
			if ($this->children[$i]->getType() == ELEMENT) {
				if ($this->children[$i]->getName() == $name) {
					return $this->children[$i];
				}
			}
		}
		return null;
	}

	public function getElementsByName($name) {
		$elements = array();
		for ($i = 0, $c = count($this->children); $i < $c; $i++) {
			if ($this->children[$i]->getType() == ELEMENT) {
				if ($this->children[$i]->getName() == $name) {
					$elements[] = $this->children[$i];
				}
			}
		}
		return $elements;
	}

	public function getValueByPath($path) {
		$pathArray = explode('/', $path);
		$node = $this;
		for ($i = 0, $matches = 0, $num_segments = count($pathArray); $i < $num_segments; $i++) {
			if ($i == 0 && $pathArray[$i] == '') {
				$matches++;
				continue;
			}
			if ($node->getChildren()) {
				for ($j = 0; $j < count($node->getChildren()); $j++) {
					if ($node->children[$j]->getType() == ELEMENT) {
						if ($node->children[$j]->getName() == $pathArray[$i]) {
							$node = $node->children[$j];
							$matches++;
						}
					}
				}
			}
		}
		return ($matches == $num_segments) ? $node->getValue() : false;
	} 

	public function getText() {
		return $this->text();
	}

	public function setValue($text) {
		$this->text = $text;
	}

	public function getValue() {
		if ($this->getType() == ELEMENT) {
			for ($i = 0, $value = '', $c = count($this->children); $i < $c; $i++) {
				$value .= $this->children[$i]->getValue();
			}
		} elseif ($this->getType() == TEXTELEMENT) {
			$value = $this->text;
		}
		return $value;
	}

	public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
	}

	public function getAttribute($name) {
		return (isset($this->attributes[$name])) ? $this->attributes[$name] : '';
	}

	public function addNode(&$node) {
		$this->children[] = &$node;
		$node->parent = &$this;
	}

	public function parentToString($node) {
		while ($node->parent) {
			$node = $node->parent;
		}
	}

	public function toString() {
		if ($this->type == ELEMENT) {
			$string = '{' . $this->name . '}';
			for ($i = 0; $i < count($this->children); $i++) {
				$string .= $this->children[$i]->toString();
			}
			$string .= '{/' . $this->name . '}';
		} else {
			$string = $this->getValue();
		}
		return $string;
	}
}

//**************
class XMLParser 
{
	protected $xp,
			  $document,
			  $current,
			  $error;

	public function __construct() {
		$this->document = new XMLDocument();
		$this->error = array();
	}

	public function setDocument($document) {
		$this->document = $document;
	}

	public function getDocument() {
		return $this->document;
	}

	public function destruct() {
		if (!empty($this->xp)) {
			xml_parser_free($this->xp);
		}
	}

	// return 1 for an error, 0 for no error
	public function hasErrors() {
		return (!empty($this->error)) ? 1 : 0;
	}

	// return array of error messages
	public function getError() {
		return $this->error;
	}

	// process xml start tag
	public function startElement($xp, $name, $attrs) {
		$node = $this->document->createElement($name);
		if ($this->document->getRoot()) {
			$this->current->addNode($node);
		} else {
			$this->document->setRoot($node);
		}
		$this->current = &$node;
	}

	// process xml end tag
	public function endElement($xp, $name) {
		if ($this->current->getParent()) {
			$this->current = &$this->current->getParent();
		}
	}

	// process data between xml tags
	public function dataHandler($xp, $text) {
		$node = $this->document->createTextElement($text);
		$this->current->addNode($node);
	}

	// parse xml document from string
	public function parse($xmlString) {
		$this->xp = xml_parser_create();
		if (empty($this->xp)) {
			$this->error['description'] = 'Could not create xml parser';
			
		} elseif (!xml_set_object($this->xp, $this)) {
			$this->error['description'] = 'Could not set xml parser for object';
			
		} elseif (!xml_set_element_handler($this->xp, 'startElement', 'endElement')) {
			$this->error['description'] = 'Could not set xml element handler';
			
		} elseif (!xml_set_character_data_handler($this->xp, 'dataHandler')) {
			$this->error['description'] = 'Could not set xml character handler';
			
		} else {
			xml_parser_set_option($this->xp, XML_OPTION_CASE_FOLDING, false);
			if (!xml_parse($this->xp, $xmlString)) {
				$this->error['description'] = xml_error_string(xml_get_error_code($this->xp));
				$this->error['line'] = xml_get_current_line_number($this->xp);
			}
		}
	}
}
