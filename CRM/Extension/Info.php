<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

class CRM_Extension_Info {

  /**
   * Extension info file name
   */
  const FILENAME = 'info.xml';

  /**
   * @var string local file system path containing extension
   */
  public $path = NULL;

  public $key = NULL;
  public $type = NULL;
  public $name = NULL;
  public $label = NULL;
  public $file = NULL;

  /**
   * Load extension info an XML file
   *
   * @param string $string XML content
   * @return CRM_Extension_Info
   * @throws Exception
   */
  public static function loadFromFile($file) {
    list ($xml, $error) = CRM_Utils_XML::parseFile($file);
    if ($xml === FALSE) {
      throw new Exception("Failed to parse info XML: $error");
    }

    $instance = new CRM_Extension_Info(NULL, NULL, NULL, NULL, NULL);
    $instance->parse($xml);
    return $instance;
  }

  /**
   * Load extension info a string
   *
   * @param string $string XML content
   * @return CRM_Extension_Info
   *
  public static function loadFromString($string) {
    $xml = simplexml_load_string($string, 'SimpleXMLElement');
    if ($xml == FALSE) {
      throw new \Exception("Failed to parse info XML: $string");
    }

    $instance = new CRM_Extension_Info(NULL, NULL, NULL, NULL, NULL);
    $instance->parse($xml);
    return $instance;
  } // */

  function __construct($path, $key, $type = NULL, $name = NULL, $label = NULL, $file = NULL) {
    $this->path      = $path;
    $this->key       = $key;
    $this->type      = $type;
    $this->name      = $name;
    $this->label     = $label;
    $this->file      = $file;
  }

  /**
   * Copy attributes from an XML document to $this
   *
   * @param SimpleXMLElement $info
   * @return void
   */
  public function parse($info) {
    $this->key   = (string) $info->attributes()->key;
    $this->type  = (string) $info->attributes()->type;
    $this->file  = (string) $info->file;
    $this->label = (string) $info->name;

    // Convert first level variables to CRM_Core_Extension properties
    // and deeper into arrays. An exception for URLS section, since
    // we want them in special format.
    foreach ($info as $attr => $val) {
      if (count($val->children()) == 0) {
        $this->$attr = (string) $val;
      }
      elseif ($attr === 'urls') {
        $this->urls = array();
        foreach ($val->url as $url) {
          $urlAttr = (string) $url->attributes()->desc;
          $this->urls[$urlAttr] = (string) $url;
        }
        ksort($this->urls);
      }
      else {
        $this->$attr = CRM_Utils_XML::xmlObjToArray($val);
      }
    }
  }

}
