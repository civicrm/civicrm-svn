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

/**
 * This class facilitates the loading of secondary, per-page resources
 * such as JavaScript files and CSS files.
 *
 * Any URLs generated for resources may include a 'cache-code'. By resetting the
 * cache-code, one may force clients to re-download resource files (regardless of
 * any HTTP caching rules).
 *
 * TODO: This is currently a thin wrapper over CRM_Core_Region. We
 * should incorporte services for aggregation, minimization, etc.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Core_Resources {
  const DEFAULT_WEIGHT = 0;
  const DEFAULT_REGION = 'page-footer';

  /**
   * We don't have a container or dependency-injection, so use singleton instead
   *
   * @var object
   * @static
   */
  private static $_singleton = NULL;

  /**
   * @var callable(string => string) Map extension names to their base URLs. Note:
   *  - URLs should end with a trailing '/'
   */
  private $extMapper = NULL;

  /**
   * 
   */
  protected $settings = array();
  protected $addedSettings = FALSE;

  /**
   * @var string a value to append to JS/CSS URLs to coerce cache resets
   */
  protected $cacheCode = NULL;

  /**
   * @var string the name of a setting which persistently stores the cacheCode
   */
  protected $cacheCodeKey = NULL;

  /**
   * Get or set the single instance of CRM_Core_Resources
   *
   * @param $instance CRM_Core_Resources, new copy of the manager
   * @return CRM_Core_Resources
   */
  static public function singleton(CRM_Core_Resources $instance = NULL) {
    if ($instance !== NULL) {
      self::$_singleton = $instance;
    }
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Core_Resources(
        array(CRM_Extension_System::singleton()->getMapper(), 'keyToUrl'),
        'resCacheCode'
      );
    }
    return self::$_singleton;
  }

  /**
   * Construct a resource manager
   *
   * @var $extMapper array(extensionName => url) Map extension names to their base URLs. Note:
   *  - The $extMapper['*'] is a grandparent-URL for unknown extension dirs
   *  - URLs should end with a trailing '/'
   */
  public function __construct($extMapper, $cacheCodeKey = NULL) {
    $this->extMapper = $extMapper;
    $this->cacheCodeKey = $cacheCodeKey;
    if ($cacheCodeKey !== NULL) {
      $this->cacheCode = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, $cacheCodeKey);
    }
    if (! $this->cacheCode) {
      $this->resetCacheCode();
    }
  }

  /**
   * Add a JavaScript file to the current page using <SCRIPT SRC>.
   *
   * @param $ext string, extension name; use 'civicrm' for core
   * @param $file string, file path -- relative to the extension base dir
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addScriptFile($ext, $file, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    return $this->addScriptUrl($this->getUrl($ext, $file, TRUE), $weight, $region);
  }

  /**
   * Add a JavaScript file to the current page using <SCRIPT SRC>.
   *
   * @param $url string
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addScriptUrl($url, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    $config = CRM_Core_Config::singleton();
    if ($region == 'html-header' && is_callable(array($config->userSystem, 'addHtmlHeadScriptUrl'))) {
      $config->userSystem->addHtmlHeadScriptUrl($url, $weight);
    } else {
      CRM_Core_Region::instance($region)->add(array(
        'name' => $url,
        'type' => 'scriptUrl',
        'scriptUrl' => $url,
        'weight' => $weight,
        'region' => $region,
      ));
    }
    return $this;
  }

  /**
   * Add a JavaScript file to the current page using <SCRIPT SRC>.
   *
   * @param $code string, JavaScript source code
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addScript($code, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    $config = CRM_Core_Config::singleton();
    if ($region == 'html-header' && is_callable(array($config->userSystem, 'addHtmlHeadScriptCode'))) {
      $config->userSystem->addHtmlHeadScriptCode($code, $weight);
    } else {
      CRM_Core_Region::instance($region)->add(array(
        // 'name' => automatic
        'type' => 'script',
        'script' => $code,
        'weight' => $weight,
        'region' => $region,
      ));
    }
    return $this;
  }

  /**
   * Add JavaScript variables to the global CRM object.
   *
   * @param $settings array
   * @return CRM_Core_Resources
   */
  public function addSetting($settings) {
    foreach ($settings as $k => $v) {
      if (isset($this->settings[$k]) && is_array($this->settings[$k]) && is_array($v)) {
        $v += $this->settings[$k];
      }
      $this->settings[$k] = $v;
    }
    if (!$this->addedSettings) {
      $resources = $this;
      CRM_Core_Region::instance('settings')->add(array(
        'callback' => function(&$snippet, &$html) use ($resources) {
          $html .= "\n" . $resources->renderSetting();
        }
      ));
      $this->addedSettings = TRUE;
    }
    return $this;
  }

  /**
   * Helper fn for addSetting
   * Render JavaScript variables for the global CRM object.
   *
   * Example:
   * From the server:
   * CRM_Core_Resources::singleton()->addSetting(array('myNamespace' => array('foo' => 'bar')));
   * From javascript:
   * CRM.myNamespace.foo // "bar"
   *
   * @return string
   */
  public function renderSetting() {
    return 'CRM = cj.extend(true, ' . json_encode($this->settings) . ', CRM);';
  }

  /**
   * Add translated string to the js CRM object.
   * It can then be retrived from the client-side ts() function
   * Variable substitutions can happen from client-side
   *
   * Simple example:
   * // From php:
   * CRM_Core_Resources::singleton()->addString('Hello');
   * // The string is now available to javascript code i.e.
   * ts('Hello');
   *
   * Example with client-side substitutions:
   * // From php:
   * CRM_Core_Resources::singleton()->addString('Your %1 has been %2');
   * // ts() in javascript works the same as in php, for example:
   * ts('Your %1 has been %2', {1: objectName, 2: actionTaken});
   *
   * NOTE: This function does not work with server-side substitutions
   * (as this might result in collisions and unwanted variable injections)
   * Instead, use code like:
   * CRM_Core_Resources::singleton()->addSetting(array('myNamespace' => array('myString' => ts('Your %1 has been %2', array(subs)))));
   * And from javascript access it at CRM.myNamespace.myString
   *
   * @param $text string
   * @return CRM_Core_Resources
   */
  public function addString($text) {
    $translated = ts($text);
    // We only need to push this string to client if the translation
    // is actually different from the original
    if ($translated != $text) {
      $this->addSetting(array('strings' => array($text => $translated)));
    }
    return $this;
  }

  /**
   * Add a CSS file to the current page using <LINK HREF>.
   *
   * @param $ext string, extension name; use 'civicrm' for core
   * @param $file string, file path -- relative to the extension base dir
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addStyleFile($ext, $file, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    return $this->addStyleUrl($this->getUrl($ext, $file, TRUE), $weight, $region);
  }

  /**
   * Add a CSS file to the current page using <LINK HREF>.
   *
   * @param $url string
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addStyleUrl($url, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    CRM_Core_Region::instance($region)->add(array(
      'name' => $url,
      'type' => 'styleUrl',
      'styleUrl' => $url,
      'weight' => $weight,
      'region' => $region,
    ));
    return $this;
  }

  /**
   * Add a CSS content to the current page using <STYLE>.
   *
   * @param $code string, CSS source code
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addStyle($code, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    CRM_Core_Region::instance($region)->add(array(
      // 'name' => automatic
      'type' => 'style',
      'style' => $code,
      'weight' => $weight,
      'region' => $region,
    ));
    return $this;
  }

  /**
   * Determine public URL of a resource provided by an extension
   *
   * @param $ext string, extension name; use 'civicrm' for core
   * @param $file string, file path -- relative to the extension base dir
   * @return string, URL
   */
  public function getUrl($ext, $file = NULL, $addCacheCode = FALSE) {
    if ($file === NULL) {
      $file = '';
    }
    if ($addCacheCode) {
      $file .= '?r=' . $this->getCacheCode();
    }
    // TODO consider caching call_user_func results
    return call_user_func($this->extMapper, $ext) . '/' . $file;
  }

  public function getCacheCode() {
    return $this->cacheCode;
  }

  public function setCacheCode($value) {
    $this->cacheCode = $value;
    if ($this->cacheCodeKey) {
      CRM_Core_BAO_Setting::setItem($value, CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, $this->cacheCodeKey);
    }
  }

  public function resetCacheCode() {
    $this->setCacheCode(CRM_Utils_String::createRandom(5, CRM_Utils_String::ALPHANUMERIC));
  }

  /**
   * Read resource files from a template
   * 
   * @param $tpl (str) template file name
   * @return array filename => filetype
   */
  static function parseTemplate($tpl) {
    $items = array();
    $template = CRM_Core_Smarty::singleton();
    $buffer = $template->fetch($tpl);
    $lines = preg_split('/\s+/', $buffer);
    foreach ($lines as $line) {
      $line = trim($line);
      if ($line) {
        $items[$line] = substr($line, 1 + strrpos($line, '.'));
      }
    }
    return $items;
  }
}
