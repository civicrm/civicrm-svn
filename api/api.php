<?php
//TODO write me properly - I'm just a pretend function @ the moment
//TODO I could do the throw & catch thing here while I'm at it because I'm just that kind of a function
//TODO send a case of warm beer to the API team
function civicm_api($function, $class, $params){
if (empty($params['version'])){
 $params['version'] = 2;
}

require_once 'civicrm/api/v' . $params['version'] . '/' . $class .'.php';
$function($params);
}

/*
usage
$result = civicrm_api('civicrm_contact_get', 'Contact', $params);
From Dave
from a security standpoint you might want to have some checks. 
At the least don't allow ".." or path separators in them. And function_exists($function).
*/

