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
    var $root;
    var $children;

    function __construct() {
    }

    function createElement($name) {
        $node = new Node();
        $node->setName($name);
        $node->setType(ELEMENT);
        return $node;
    }

    function createTextElement($text) {
        $node = new Node();
        $node->setType(TEXTELEMENT);
        $node->setValue($text);
        return $node;
    }

    function getRoot() {
        return $this->root;
    }

    function setRoot($node) {
        $this->root = $node;
    }

    function toString() {
        if ($this->root) {
            return $this->root->toString();
        } else {
            return "DOCUMENT ROOT NOT SET";
        }
    }

    function getValueByPath($path) {
        $pathArray = explode("/", $path);
        if ($pathArray[0] == $this->root->getName()) {
            //print_r("Looking for " . $pathArray[0] . "<br>");
            array_shift($pathArray);
            $newPath = implode("/", $pathArray);
            return $this->root->getValueByPath($newPath);
            
//-bof-20170925-lat9-Return boolean false if the root path was not found.
        } else {
            return false;
        }
//-eof-20170925-lat9

    }
}

//**********
class Node {
    var $name;
    var $type;
    var $text;
    var $parent;
    var $children;
    var $attributes;

    function __construct() {
        $this->children = array();
        $this->attributes = array();
    }

    function getName() {
        return $this->name;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setParent(&$node) {
        $this->parent =& $node;
    }

    function &getParent() {
        return $this->parent;
    }

    function &getChildren() {
        return $this->children;
    }

    function getType() {
        return $this->type;
    }

    function setType($type) {
        $this->type = $type;
    }

    function getElementByName($name) {
        for ($i = 0; $i < count($this->children); $i++) {
            if ($this->children[$i]->getType() == ELEMENT) {
                if ($this->children[$i]->getName() == $name) {
                    return $this->children[$i];
                }
            }
        }
        return null;
    }

    function getElementsByName($name) {
        $elements = array();
        for ($i = 0; $i < count($this->children); $i++) {
            if ($this->children[$i]->getType() == ELEMENT) {
                if ($this->children[$i]->getName() == $name) {
                    $elements[] = $this->children[$i];
                }
            }
        }
        return $elements;
    }

    function getValueByPath($path) {
        $pathArray = explode('/', $path);
        $node = $this;
        for ($i = 0, $matches = 0, $num_segments = count($pathArray); $i < $num_segments; $i++) {
            if ($i == 0 && $pathArray[$i] == '') {
                $matches++;
                continue;
            }
            //print_r("Looking for " . $pathArray[$i] ."<br>");
            if ($node->getChildren()) {
                for ($j = 0; $j < count($node->getChildren()); $j++) {
                    if ($node->children[$j]->getType() == ELEMENT) {
                        if ($node->children[$j]->getName() == $pathArray[$i]) {
                            //print_r("Found " . $pathArray[$i] ."<br>");
                            $node = $node->children[$j];
                            $matches++;
                        }
                    }
                }
            }
        }
        return ($matches == $num_segments) ? $node->getValue() : false;
    } 

    function getText() {
        return $this->text();
    }

    function setValue($text) {
        $this->text = $text;
    }

    function getValue() {
        $value = NULL;
        if ($this->getType() == ELEMENT) {
            for ($i = 0; $i < count($this->children); $i++) {
                $value .= $this->children[$i]->getValue();
            }
        } elseif ($this->getType() == TEXTELEMENT) {
            $value .= $this->text;
        }
        return $value;
    }

    function setAttribute($name, $value) {
        $attributes[$name] = $value;
    }

    function getAttribute($name) {
        return $attributes[$name];
    }

    function addNode(&$node) {
        $this->children[] =& $node;
        $node->parent =& $this;
    }

    function parentToString($node) {
        while($node->parent) {
            //print_r("Node " . $node->name . " has parent<br>");
            $node = $node->parent;
        }
        //print_r("Node contents from root: " . $node->toString() . "<br>");
    }

    function toString() {
        $string = NULL;    
        //print_r("toString child count " . $this->name . " contains " . count($this->children) . "<br>");    
        if ($this->type == ELEMENT) {
            $string .= '{' . $this->name . '}';
            for ($i = 0; $i < count($this->children); $i++) {
                $string .= $this->children[$i]->toString();
            }
            $string .= '{/' . $this->name . '}';
        } else {
            $string .= $this->getValue();
        }
        return $string;
    }
}

//**************
class XMLParser {
    var $xp;
    var $document;
    var $current;
    var $error;

    function __construct() {
        $this->document = new XMLDocument();
        $this->error = array();
    }

    function setDocument($document) {
        $this->document = $document;
    }

    function getDocument() {
        return $this->document;
    }

    function destruct(){
        xml_parser_free($this->xp);
    }

    // return 1 for an error, 0 for no error
    function hasErrors() {
        if (sizeof($this->error) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    // return array of error messages
    function getError() {
        return $this->error;
    }

    // process xml start tag
    function startElement($xp, $name, $attrs) {
        //print_r("Found Start Tag: " . $name . "<br>");
        $node =& $this->document->createElement($name);
        if ($this->document->getRoot()) {
            $this->current->addNode($node);
        } else {
            $this->document->root =& $node;
        }
        $this->current =& $node;
    }

    // process xml end tag
    function endElement($xp, $name){
        //print_r("Found End Tag: " . $name . "<br>");
        if ($this->current->getParent()) {
            $this->current =& $this->current->getParent();
        }
    }

    // process data between xml tags
    function dataHandler($xp, $text) {
        //print_r("Adding Data: \"" . $text . "\"<br>");
        $node =& $this->document->createTextElement($text);
        $this->current->addNode($node);
    }

    // parse xml document from string
    function parse($xmlString) {
        if(!($this->xp = @xml_parser_create())) {
            $this->error['description'] = 'Could not create xml parser';
        }
        if(!$this->hasErrors()) {
            if(!@xml_set_object($this->xp, $this)) {
                $this->error['description'] = 'Could not set xml parser for object';
            }
        }
        if(!$this->hasErrors()) {
            if(!@xml_set_element_handler($this->xp, 'startElement', 'endElement')) {
                $this->error['description'] = 'Could not set xml element handler';
            }
        }
        if(!$this->hasErrors()) {
            if(!@xml_set_character_data_handler($this->xp, 'dataHandler')) {
                $this->error['description'] = 'Could not set xml character handler';
            }
        } 
        xml_parser_set_option($this->xp, XML_OPTION_CASE_FOLDING, false);
        if (!$this->hasErrors()) {
            if(!@xml_parse($this->xp, $xmlString)) {
                $this->error['description'] = xml_error_string(xml_get_error_code($this->xp));
                $this->error['line'] = xml_get_current_line_number($this->xp);
            }
        }
    }
}
?>