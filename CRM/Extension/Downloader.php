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
 * This class handles downloads of remotely-provided extensions
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Extension_Downloader {
  /**
   * @var CRM_Extension_Container_Basic the place where downloaded extensions are ultimately stored
   */
  public $container;

  /**
   * @var string local path to a temporary data directory
   */
  public $tmpDir;

  /**
   * @param string $containerDir the place to store downloaded & extracted extensions
   * @param string $tmpDir
   */
  public function __construct(CRM_Extension_Manager $manager, $containerDir, $tmpDir) {
    $this->manager = $manager;
    $this->containerDir = $containerDir;
    $this->tmpDir = $tmpDir;
  }

  /**
   * Determine whether downloading is supported
   *
   * @return array list of error messages; empty if OK
   */
  public function checkRequirements() {
    $errors = array();

    if (!$this->containerDir || !is_dir($this->containerDir) || !is_writeable($this->containerDir)) {
      $civicrmDestination = urlencode(CRM_Utils_System::url('civicrm/admin/extensions', 'reset=1'));
      $url = CRM_Utils_System::url('civicrm/admin/setting/path', "reset=1&civicrmDestination=${civicrmDestination}");
      $errors[] = array(
        'title' => ts('Directory Unwritable'),
        //'message' => ts('Your extensions directory: %1 is not web server writable. Please go to the <a href="%2">path setting page</a> and correct it.<br/>',
        'message' => ts("Your extensions directory is not set or is not writable. Click <a href='%1'>here</a> to set the extensions directory.",
          array(
            //1 => $this->containerDir,
            1 => $url,
          )
        )
      );
    }

    if (!class_exists('ZipArchive')) {
      $errors[] = array(
        'title' => ts('ZIP Support Required'),
        'message' => ts('You will not be able to install extensions at this time because your installation of PHP does not support ZIP archives. Please ask your system administrator to install the standard PHP-ZIP extension.'),
      );
    }

    return $errors;
  }

  /**
   * Install or upgrade an extension from a remote URL
   *
   * @param string $key the name of the extension being installed
   * @param string $downloadUrl URL of a .zip file
   * @return bool TRUE for success
   * @throws CRM_Extension_Exception
   */
  public function download($key, $downloadUrl) {
    $filename = $this->tmpDir . DIRECTORY_SEPARATOR . $key . '.zip';
    $destDir = $this->containerDir . DIRECTORY_SEPARATOR . $key;

    if (!$downloadUrl) {
      CRM_Core_Error::fatal('Cannot install this extension - downloadUrl is not set!');
    }

    if (! $this->fetch($downloadUrl, $filename)) {
      return FALSE;
    }

    $extractedZipPath = $this->extractFiles($key, $filename);
    if (! $extractedZipPath) {
      return FALSE;
    }

    if (! $this->validateFiles($key, $extractedZipPath)) {
      return FALSE;
    }

    $this->manager->replace($extractedZipPath);

    return TRUE;
  }

  /**
   * Download the remote zipfile.
   *
   * @param string $remoteFile URL of a .zip file
   * @param string $localFile path at which to store the .zip file
   * @return boolean Whether the download was successful.
   */
  public function fetch($remoteFile, $localFile) {
    require_once 'CA/Config/Curl.php';
    $caConfig = CA_Config_Curl::probe(array(
      'verify_peer' => (bool)CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'verifySSL', NULL, TRUE)
    ));

    // Download extension zip file ...
    if (!function_exists('curl_init')) {
      CRM_Core_Error::fatal('Cannot install this extension - curl is not installed!');
    }
    if (preg_match('/^https:/', $remoteFile) && !$caConfig->isEnableSSL()) {
      CRM_Core_Error::fatal('Cannot install this extension - does not support SSL');
    }

    //setting the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remoteFile);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    if (preg_match('/^https:/', $remoteFile)) {
      curl_setopt_array($ch, $caConfig->toCurlOptions());
    }

    //follow redirects
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $fp = fopen($localFile, "w");
    if (! $fp) {
        CRM_Core_Session::setStatus(ts('Unable to write to %1.<br />Is the location writable?', array(1 => $localFile)), ts('Write Error'), 'error');
        return FALSE;
      }
    curl_setopt($ch, CURLOPT_FILE, $fp);

    curl_exec($ch);
    if (curl_errno($ch)) {
      CRM_Core_Error::debug(curl_error($ch));
      CRM_Core_Error::debug(curl_errno($ch)); exit( );
      CRM_Core_Session::setStatus(ts('Unable to download extension from %1. Error Message: %2',
          array(1 => $remoteFile, 2 => curl_error($ch))), ts('Download Error'), 'error');
        return FALSE;
      }
      else {
      curl_close($ch);
      }

    fclose($fp);

    return TRUE;
  }

  /**
   * Extract an extension from a zip file
   *
   * @param string $key the name of the extension being installed; this usually matches the basedir in the .zip
   * @param string $zipFile the local path to a .zip file
   * @return string|FALSE zip file path
   */
  public function extractFiles($key, $zipFile) {
    $config = CRM_Core_Config::singleton();

    $zip = new ZipArchive();
    $res = $zip->open($zipFile);
    if ($res === TRUE) {
      $zipSubDir = CRM_Utils_Zip::guessBasedir($zip, $key);
      if ($zipSubDir === FALSE) {
        CRM_Core_Session::setStatus(ts('Unable to extract the extension: bad directory structure'), '', 'error');
        return FALSE;
      }
      $extractedZipPath = $this->tmpDir . DIRECTORY_SEPARATOR . $zipSubDir;
      if (is_dir($extractedZipPath)) {
        if (!CRM_Utils_File::cleanDir($extractedZipPath, TRUE, FALSE)) {
          CRM_Core_Session::setStatus(ts('Unable to extract the extension: %1 cannot be cleared', array(1 => $extractedZipPath)), ts('Installation Error'), 'error');
          return FALSE;
        }
      }
      if (!$zip->extractTo($this->tmpDir)) {
        CRM_Core_Session::setStatus(ts('Unable to extract the extension to %1.', array(1 => $this->tmpDir)), ts('Installation Error'), 'error');
        return FALSE;
      }
      $zip->close();
    }
    else {
      CRM_Core_Session::setStatus(ts('Unable to extract the extension.'), '', 'error');
      return FALSE;
    }

    return $extractedZipPath;
  }

  /**
   * Validate that $extractedZipPath contains valid for extension $key
   *
   * @return bool
   */
  function validateFiles($key, $extractedZipPath) {
    $filename = $extractedZipPath . DIRECTORY_SEPARATOR . CRM_Extension_Info::FILENAME;
    if (!is_readable($filename)) {
      CRM_Core_Session::setStatus(ts('Failed reading data from %1 during installation', array(1 => $filename)), ts('Installation Error'), 'error');
      return FALSE;
    }

    try {
      $newInfo = CRM_Extension_Info::loadFromFile($filename);
    } catch (Exception $e) {
      CRM_Core_Session::setStatus(ts('Failed reading data from %1 during installation', array(1 => $filename)), ts('Installation Error'), 'error');
      return FALSE;
    }

    if ($newInfo->key != $key) {
      CRM_Core_Error::fatal('Cannot install - there are differences between extdir XML file and archive XML file!');
    }

    return TRUE;
  }

}
