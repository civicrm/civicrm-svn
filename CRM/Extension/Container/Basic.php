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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 */

/**
 * An extension container is a locally-accessible source tree which can be
 * scanned for extensions.
 */
class CRM_Extension_Container_Basic implements CRM_Extension_Container_Interface {

  /**
   * @var string
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $baseDir;

  /**
   * @var string
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $baseUrl;

  /**
   * @var CRM_Utils_Cache_Interface|NULL
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $cache;

  /**
   * @var string the cache key used for any data stored by this container
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $cacheKey;

  /**
   * @var array($key => $relPath)
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $relPaths = FALSE;

  /**
   * @param string $baseDir local path to the container
   * @param string $baseUrl public URL of the container
   * @param CRM_Utils_Cache_Interface $cache
   * @param string $cacheKey unique name for this container
   */
  public function __construct($baseDir, $baseUrl, CRM_Utils_Cache_Interface $cache = NULL, $cacheKey = NULL) {
    $this->cache = $cache;
    $this->cacheKey = $cacheKey;
    $this->baseDir = $baseDir;
    $this->baseUrl = $baseUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeys() {
    return array_keys($this->getRelPaths());
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($key) {
    return $this->baseDir . $this->getRelPath($key);
  }

  /**
   * {@inheritdoc}
   */
  public function getResUrl($key) {
    return $this->baseUrl . $this->getRelPath($key);
  }

  /**
   * {@inheritdoc}
   */
  public function refresh() {
    $this->relPaths = NULL;
    if ($this->cache) {
      $this->cache->delete($this->cacheKey);
    }
  }

  /**
   * @return string
   */
  public function getBaseDir() {
    return $this->baseDir;
  }

  /**
   * Determine the relative path of an extension directory
   *
   * @return string
   * @throws CRM_Extension_Exception
   */
  protected function getRelPath($key) {
    $keypaths = $this->getRelPaths();
    if (! isset($keypaths[$key])) {
      throw new CRM_Extension_Exception("Failed to find extension: $key");
    }
    return $keypaths[$key];
  }

  /**
   * Scan $basedir for a list of extension-keys
   *
   * @return array($key => $relPath)
   */
  protected function getRelPaths() {
    if (!is_array($this->relPaths)) {
      if ($this->cache) {
        $this->relPaths = $this->cache->get($this->cacheKey);
      }
      if (!is_array($this->relPaths)) {
        $this->relPaths = array();
        $infoPaths = CRM_Utils_File::findFiles($this->baseDir, 'info.xml');
        foreach ($infoPaths as $infoPath) {
          $relPath = CRM_Utils_File::relativize(dirname($infoPath), $this->baseDir);
          $info = CRM_Extension_Info::loadFromFile($infoPath);
          $this->relPaths[$info->key] = $relPath;
        }
        if ($this->cache) {
          $this->cache->set($this->cacheKey, $this->relPaths);
        }
      }
    }
    return $this->relPaths;
  }
}
