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
 * This class glues together the various parts of the extension
 * system.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Extension_System {
  private static $singleton;

  private $cache = NULL;
  private $fullContainer = NULL;
  private $defaultContainer = NULL;
  private $mapper = NULL;
  private $manager = NULL;
  private $browser = NULL;
  private $downloader = NULL;

  /**
   * The URL of the remote extensions repository
   *
   * @var string|FALSE
   */
  private $_repoUrl = NULL;

  /**
   * @return CRM_Extension_System
   */
  public static function singleton($fresh = FALSE, $parameters = array()) {
    if (! self::$singleton || $fresh) {
      self::$singleton = new CRM_Extension_System($parameters);
    }
    return self::$singleton;
  }

  public function __construct($parameters = array()) {
    $config = CRM_Core_Config::singleton();
    if (!array_key_exists('extensionsDir', $parameters)) {
      $parameters['extensionsDir'] = $config->extensionsDir;
    }
    if (!array_key_exists('extensionsURL', $parameters)) {
      $parameters['extensionsURL'] = $config->extensionsURL;
    }
    $this->parameters = $parameters;
  }

  /**
   * Get a container which represents all available extensions
   *
   * @return CRM_Extension_Container_Interface
   */
  public function getFullContainer() {
    if ($this->fullContainer === NULL) {
      $containers = array();

      if ($this->getDefaultContainer()) {
        $containers['default'] = $this->getDefaultContainer();
      }

      $config = CRM_Core_Config::singleton();
      global $civicrm_root;
      $containers['civiroot']  = new CRM_Extension_Container_Basic($civicrm_root, $config->resourceBase, $this->getCache(), 'civiroot');

      // TODO: CRM_Extension_Container_Basic( /sites/all/modules )
      // TODO: CRM_Extension_Container_Basic( /sites/$domain/modules
      // TODO: CRM_Extension_Container_Basic( /modules )
      // TODO: CRM_Extension_Container_Basic( /vendors )

      $this->fullContainer = new CRM_Extension_Container_Collection($containers, $this->getCache(), 'full');
    }
    return $this->fullContainer;
  }

  /**
   * Get the container to which new extensions are installed
   *
   * This container should be a particular, writeable directory.
   *
   * @return CRM_Extension_Container_Basic|FALSE (false if not configured)
   */
  public function getDefaultContainer() {
    if ($this->defaultContainer === NULL) {
      $config = CRM_Core_Config::singleton();
      if ($config->extensionsDir) {
        $this->defaultContainer = new CRM_Extension_Container_Basic($config->extensionsDir, $config->extensionsURL, $this->getCache(), 'default');
      } else {
        $this->defaultContainer = FALSE;
      }
    }
    return $this->defaultContainer;
  }

  /**
   * Get the service which provides runtime information about extensions
   *
   * @return CRM_Extension_Mapper
   */
  public function getMapper() {
    if ($this->mapper === NULL) {
      $this->mapper = new CRM_Extension_Mapper($this->getFullContainer(), $this->getCache(), 'mapper');
    }
    return $this->mapper;
  }

  /**
   * Get the service for enabling and disabling extensions
   *
   * @return CRM_Extension_Manager
   */
  public function getManager() {
    if ($this->manager === NULL) {
      $typeManagers = array(
        'payment' => new CRM_Extension_Manager_Payment($this->getMapper()),
        'report' => new CRM_Extension_Manager_Report(),
        'search' => new CRM_Extension_Manager_Search(),
        'module' => new CRM_Extension_Manager_Module($this->getMapper()),
      );
      $this->manager = new CRM_Extension_Manager($this->getFullContainer(), $this->getDefaultContainer(), $this->getMapper(), $typeManagers);
    }
    return $this->manager;
  }

  /**
   * Get the service for finding remotely-available extensions
   *
   * @return CRM_Extension_Browser
   */
  public function getBrowser() {
    if ($this->browser === NULL) {
      $cacheDir = NULL;
      if ($this->getDefaultContainer()) {
        $cacheDir = $this->getDefaultContainer()->getBaseDir() . DIRECTORY_SEPARATOR . 'cache';
      }
      $this->browser = new CRM_Extension_Browser($this->getRepositoryUrl(), '', $cacheDir);
    }
    return $this->browser;
  }

  /**
   * Get the service for loading code from remotely-available extensions
   *
   * @return CRM_Extension_Downloader
   */
  public function getDownloader() {
    if ($this->downloader === NULL) {
      $basedir = ($this->getDefaultContainer() ? $this->getDefaultContainer()->getBaseDir() : NULL);
      $this->downloader = new CRM_Extension_Downloader(
        $this->getManager(),
        $basedir,
        CRM_Utils_File::tempdir() // WAS: $config->extensionsDir . DIRECTORY_SEPARATOR . 'tmp';
      );
    }
    return $this->downloader;
  }

  /**
   * @return CRM_Utils_Cache_Interface
   */
  public function getCache() {
    if ($this->cache === NULL) {
      $this->cache = new CRM_Utils_Cache_SqlGroup(array(
        'group' => 'ext',
        'prefetch' => TRUE,
      ));
    }
    return $this->cache;
  }

  /**
   * Determine the URL which provides a feed of available extensions
   *
   * @return string|FALSE
   */
  public function getRepositoryUrl() {
    if (empty($this->_repoUrl) && $this->_repoUrl !== FALSE) {
      $config = CRM_Core_Config::singleton();
      $url = CRM_Core_BAO_Setting::getItem('Extension Preferences', 'ext_repo_url', NULL, CRM_Extension_Browser::DEFAULT_EXTENSIONS_REPOSITORY);

      // boolean false means don't try to check extensions
      // http://issues.civicrm.org/jira/browse/CRM-10575
      if($url === false) {
        $this->_repoUrl = false;
      }
      else {
        $vars = array(
          '{ver}' => CRM_Utils_System::version(),
          '{uf}' => $config->userFramework,
          '{php}' => phpversion(),
        );
        $this->_repoUrl = strtr($url, $vars);
      }
    }
    return $this->_repoUrl;
  }
}
