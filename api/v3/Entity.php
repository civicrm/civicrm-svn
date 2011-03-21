<?php

require_once 'api/v3/utils.php';

/**
 *  returns the list of all the entities that you can manipulate via the api. The entity of this API call is the entity, that isn't a real civicrm entity as in something stored in the DB, but an abstract meta object. My head is going to explode. In a meta way.
 */
function civicrm_api3_entity_get ($params) {
  _civicrm_api3_initialize( true );
   try {
     civicrm_api3_verify_mandatory ($params);
     $entities = array ();
     $iterator = new DirectoryIterator(dirname(__FILE__));
     foreach ($iterator as $fileinfo) {
       $file = $fileinfo->getFilename();
       $parts = explode(".", $file);  
       if (end($parts) == "php" &&  $file != "utils.php" ) {
         $entities [] = substr ($file, 0, -4); // without the ".php"
       }
    }
    sort($entities);
    return civicrm_api3_create_success ($entities);
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}
