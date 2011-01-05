<?php

/**
 * File for the CiviCRM APIv3 API wrapper
 *
 * @package CiviCRM_APIv3
 * @subpackage API
 *
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Contribution.php 30486 2010-11-02 16:12:09Z shot $
 * @todo write me properly - I'm just a pretend function @ the moment
 * @todo I could do the throw & catch thing here while I'm at it because I'm just that kind of a function
 * @todo send a case of warm beer to the API team
 * /*
 * 
usage
$result = civicrm_api('civicrm_contact_get', 'Contact', $params);
From Dave
from a security standpoint you might want to have some checks. 
At the least don't allow ".." or path separators in them. And function_exists($function).
*/



function civicm_api($function, $class, $params){
if (empty($params['version'])){
 $params['version'] = 2;
}

require_once 'civicrm/api/v' . $params['version'] . '/' . $class .'.php';
$function($params);
}



