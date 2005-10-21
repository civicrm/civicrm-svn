<?php
/**
 * @package Services_PayPal
 */

/**
 * Include parent and package classes.
 */
require_once 'Services/PayPal.php';
require_once 'Services/PayPal/Profile/Handler.php';

/**
 * File handler class for storing PayPal profiles
 *
 * @package Services_PayPal
 */
class ProfileHandler_File extends ProfileHandler
{
    function ProfileHandler_File($parameters)
    {
        parent::ProfileHandler($parameters);
    }

    function _getFilename($id)
    {
        return "{$this->_params['path']}/tmp/$id.ppd";

    }

    function listProfiles()
    {
        $validate = $this->validateParams();

        if (Services_PayPal::isError($validate)) {
            return $retval;
        }

        $filemask = $this->_getFilename("*");

        $profile_files = glob($filemask);
        
        $retval = array();
        
        foreach ($profile_files as $pf) {
            $filename = basename($pf);
            $retval[] = substr($filename, 0, strpos($filename, '.'));
        }

        return $retval;
    }

    function loadProfile($id)
    {
        $retval = $this->validateParams();

        if (Services_PayPal::isError($retval)) {
            return $retval;
        }

        $open_file = $this->_getFileName($id);

        if (!file_exists($open_file)) {
            return Services_PayPal::raiseError("Profile '$id' cannot be loaded, does not exist.");
        }

        $data = file_get_contents($open_file, false);

        $retval = @unserialize($data);

        if (!is_array($retval)) {
            return Services_PayPal::raiseError("Unserialization of data failed.");
        }

        return $retval;
    }

    function saveProfile($data, $id = null)
    {
        $retval = $this->validateParams();
        if (Services_PayPal::isError($retval)) {
            return $retval;
        }

        $id = (is_null($id)) ? $this->generateID() : $id;

        $write_file = $this->_getFileName($id);

        $fr = @fopen($write_file, 'w', false);

        if (!$fr) {
            return Services_PayPal::raiseError("Could not open file '$write_file' for writing.");
        }

        $serialized = serialize($data);
        fputs($fr, $serialized);
        fclose($fr);

        return $id;
    }

    function deleteProfile($id)
    {
        $retval = $this->validateParams();

        if (Services_PayPal::isError($retval)) {
            return $retval;
        }

        $delete_file = $this->_getFileName($id);

        if (!@unlink($delete_file)) {
            return Services_PayPal::raiseError("Could not delete the Profile file '$delete_file'");
        }

        return true;
    }

    function getParamInfo()
    {
        return array('path' => array('desc' => 'Profile Save Path',
                                     'type' => 'string'));
    }

    function validateParams()
    {
        if (!isset($this->_params['path'])) {
            return Services_PayPal::raiseError("You must provide the 'path' parameter for this handler");
        }

        if (file_exists($this->_params['path']) &&
            is_dir($this->_params['path'])) {
            return true;
        }

        return false;
    }

    function &getInstance($params)
    {
        $classname = __CLASS__;
        return $inst =& new $classname($params);
    }

}
