<?php
/** 
 * params must contain at least id=xx & {one of the fields from getfields}=value
*/
function civicrm_api3_generic_setValue($apiRequest) {
  $entity = $apiRequest['entity'];
  $params = $apiRequest['params'];
//  civicrm_api3_verify_mandatory($params, NULL, array('id'));
  if (!isset ($params['id']))
    return civicrm_api3_create_error("Missing mandandatory param 'id'");
  $id=$params['id'];
  if (!is_numeric ($id))
    return civicrm_api3_create_error("Param 'id' must be a int (the id for $entity)");

  foreach (array ('id','entity','action','debug','sequential','check_permissions','version','IDS_request_uri','IDS_user_agent') as $f) {
    unset ($params[$f]);
  }
  $fields = civicrm_api ($entity,'getFields',array ("version"=>3,"sequential"));
  if ($fields['is_error'])
    return $fields;
  $fields=$fields['values'];
  $field = null;
  foreach ($params as $f=>$value) {
    if (array_key_exists ($f,$fields)) {
      $field = $f; // we found the field
      $def=$fields[$f];
      switch ($def['type']){
      case 1://int
        if (!is_numeric ($value))
          return civicrm_api3_create_error("Param '$field' must be a number");
        break;
      case 2://string
        $value = substr ($value,0,$def['size']);
        break;
      case 16://boolean
        $value = (boolean) $value;
        break;
      case 4://date
      default:
        return civicrm_api3_create_error("Param '$field' is of a type not managed yet. Join the API team and help us implement it");
      }
      continue;
    }
  }

  if (!$field) {
    return civicrm_api3_create_error("missing 'field=value' param",$params);
  }
  if (CRM_Core_DAO::setFieldValue(_civicrm_api3_get_DAO($entity),$id,$field,$value)) {
    $entity=array ('id'=>$id,$field=>$value);
    CRM_Utils_Hook::post( 'edit', $entity, $id, $entity );
    return civicrm_api3_create_success($entity);
  } else {
    return civicrm_api3_create_error("error assigning $field=$value for $entity (id=$id)",array("params"=>$params));
  }

}
