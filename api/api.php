<?php

/**
 * File for the CiviCRM APIv3 API wrapper
 *
 * @package CiviCRM_APIv3
 * @subpackage API
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: api.php 30486 2010-11-02 16:12:09Z shot $
 */

/*
 * 
usage
$result = civicrm_api_legacy('civicrm_contact_get', 'Contact', $params);
@TODO the class is generated by our code. TO be verified
 * @param string $function name of API function
 * @param string $class name of file
 * @param array $params array to be passed to function
 */
function civicrm_api_legacy($function, $class, $params) {
    $version = civicrm_get_api_version($params);
    require_once 'CRM/Utils/String.php';
    // clean up. they should be alphanumeric and _ only
    $class = CRM_Utils_String::munge( $class );
    $function = CRM_Utils_String::munge( $function );
    if ($version ==3){
        $function = str_replace( 'civicrm', 'civicrm_api3',$function);
    }
    require_once 'api/v' . $version . '/' . $class .'.php';
    $result = $function($params);
    return $result;
}


/*
 * @param string $entity
 *   type of entities to deal with
 * @param string $action
 *   create, get, delete or some special action name.
 * @param array $params
 *   array to be passed to function
 */
function civicrm_api($entity, $action, $params, $extra = NULL) {
  try {
    require_once ('api/v3/utils.php');
    require_once 'CRM/Utils/String.php';
    _civicrm_api3_initialize(true );
    $entity = CRM_Utils_String::munge($entity);
    $action = CRM_Utils_String::munge($action);
    $version = civicrm_get_api_version($params);
    $errorFnName = ( $version == 2 ) ? 'civicrm_create_error' : 'civicrm_api3_create_error';
    if ($version > 2) civicrm_api3_api_check_permission($entity, $action, $params);
    $function = civicrm_api_get_function_name($entity, $action,$version);
    civicrm_api_include($entity,null,$version);
    if ( !function_exists ($function ) ) {
      switch (strtolower($action)){
        case "getfields":
            $version = 3;
            $dao = _civicrm_api3_get_DAO ($entity);
            if (empty($dao)) {
                return $errorFnName("API for $entity does not exist (join the API team and implement $function" );
            }
            $file = str_replace ('_','/',$dao).".php";
            require_once ($file); 
            $d = new $dao();
            return civicrm_api3_create_success($d->fields());
            break;
        case "getcount":
          $result=civicrm_api ($entity,'get',$params);
          $result = $result['count'];
          break;
        case "getsingle":
          $params['sequential'] =1;//so the first entity is always result['values'][0]
          $result = civicrm_api ($entity,'get',$params);
          if ($result['is_error'] !== 0) 
            break;
          if ($result['count'] === 1) {
            $result=$result['values'][0];
            break;
          }
          if ($result['count'] !== 1) {
            $result = civicrm_api3_create_error("Expected one $entity but found " .$result['count'], array ('count'=>$result['count']));
            break;
          }
          break;
        case "getvalue":
          $params['sequential'] =1;
          $result=civicrm_api ($entity,'get',$params);
          if ($result['is_error'] !== 0) 
            break;
          if ($result['count'] !== 1) {
            $result = civicrm_api3_create_error("Expected one $entity but found " .$result['count'], array ('count'=>$result['count']));
            break;
          }

          // we only take "return=" as valid options
          if (CRM_Utils_Array::value('return',$params) ){
            if (!isset ($result['values'][0][$params['return']])) {
              $result = civicrm_api3_create_error("field ".$params['return']. " unset or not existing", array ('invalid_field'=>$params['return']));
              break;
            }

            $result = $result['values'][0][$params['return']];
            break;
          }

          $result = civicrm_api3_create_error("missing param return=field you want to read the value of",array('error_type'=>'mandatory_missing','missing_param'=>'return'));
          break;
       
        case "update":
            //$key_id = strtolower ($entity)."_id";
            $key_id = "id";
            if (!array_key_exists ($key_id,$params)) {
                return $errorFnName( "Mandatory parameter missing $key_id" );
            }
            $seek = array ($key_id => $params[$key_id], 'version' => $version);
            $existing = civicrm_api ($entity, 'get',$seek);
            if ($existing['is_error'])
                return $existing;
            if ($existing['count'] > 1)
                return $errorFnName( "More than one $entity with id ".$params[$key_id] );
            if ($existing['count'] == 0)
                return $errorFnName( "No $entity with id ".$params[$key_id] );
       
            $existing= array_pop($existing['values'] ); 
            $p = array_merge ( $existing,$params );
            return civicrm_api ($entity, 'create',$p);
            break;
        
        default:
            return $errorFnName( "API ($entity,$action) does not exist (join the API team and implement $function" );
        }
     }else{
       _civicrm_api3_validate_fields($entity,$action,$params);
       $result = isset($extra) ? $function($params, $extra) : $function($params);
     }
     if(CRM_Utils_Array::value('format.is_success', $params) == 1){
       if($result['is_error'] === 0){
         return 1;
       }else {
         return 0;
       }
     }
     if(CRM_Utils_Array::value('format.only_id', $params) && isset($result['id'])){
      return $result['id'];
    }
    if (CRM_Utils_Array::value( 'is_error', $result, 0 ) == 0) {
        _civicrm_api_call_nested_api($params, $result, $action,$entity,$version);
    }

    return $result;
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage(),null,$params );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage(),null,$params );
  }
}

function civicrm_api_get_function_name($entity, $action,$version = NULL) {
    static $_map;
    if (!isset($_map)) {
        $_map = array();
        if(empty($version)){
            $version = civicrm_get_api_version();
        }

        if ($version === 2) {
            $_map['event']['get'] = 'civicrm_event_search';
            $_map['group_roles']['create'] = 'civicrm_group_roles_add_role';
            $_map['group_contact']['create'] = 'civicrm_group_contact_add';
            $_map['group_contact']['delete'] = 'civicrm_group_contact_remove';
            $_map['entity_tag']['create'] = 'civicrm_entity_tag_add';
            $_map['entity_tag']['delete'] = 'civicrm_entity_tag_remove';
            $_map['group']['create'] = 'civicrm_group_add';
            $_map['contact']['create'] = 'civicrm_contact_add';
            $_map['relationship_type']['get'] = 'civicrm_relationship_types_get';
            $_map['uf_join']['create'] = 'civicrm_uf_join_add';
            if (isset($_map[$entity][$action])) {
                return $_map[$entity][$action];
            }
        }
    }
    if ($entity == strtolower ($entity) ) {
      $function = '_'.$entity;
    } else {
      $function = strtolower(str_replace('U_F',
                                       'uf', 
                                       // That's CamelCase, beside an odd UFCamel that is expected as uf_camel
                                       preg_replace('/(?=[A-Z])/','_$0', $entity)));
    }
    if ( $version === 2 ) {
        return 'civicrm'. $function .'_'. $action;
    } else {
        return 'civicrm_api3'. $function .'_'. $action;
    }
}


/**
 * We must be sure that every request uses only one version of the API.
 *
 * @param $desired_version : array or integer
 *   One chance to set the version number.
 *   After that, this version number will be used for the remaining request.
 *   This can either be a number, or an array(.., 'version' => $version, ..).
 *   This allows to directly pass the $params array.
 */
function civicrm_get_api_version($desired_version = NULL) {

    if (is_array($desired_version)) {
        // someone gave the full $params array.
        $params = $desired_version;
        $desired_version = empty($params['version']) ? NULL : (int) $params['version'];
    }
    if (isset($desired_version) && is_integer ($desired_version)) {
        $_version = $desired_version;
        // echo "\n".'version: '. $_version ." (parameter)\n";
    }
    else {
        // we will set the default to version 3 as soon as we find that it works.
        $_version = 3;
        // echo "\n".'version: '. $_version ." (default)\n";
    }
    return $_version;
}


/**
 * @param $entity
 * @param $rest_interface : boolean
 *   In case of TRUE, we need to set the base path explicitly.
 */
function civicrm_api_include($entity, $rest_interface = FALSE,$version = NULL) {

    $version = civicrm_get_api_version($version);
    $camel_name = civicrm_api_get_camel_name($entity,$version);
    $file = 'api/v'. $version .'/'. $camel_name .'.php';
    if ( $rest_interface ) {
        $apiPath = substr( $_SERVER['SCRIPT_FILENAME'], 0, -15 );
        // check to ensure file exists, else die
        if ( ! file_exists( $apiPath . $apiFile ) ) {
            return self::error( 'Unknown function invocation.' );
        }
        $file = $apiPath . $file;
    }
    require_once $file;
}


function civicrm_api_get_camel_name($entity,$version = NULL) {
    static $_map = NULL;
    if (!isset($_map)) {
        $_map = array();
        $_map['utils'] = 'utils';
        if(empty($version)){
            $version = civicrm_get_api_version();
        }
        if ($version === 2) {
            // TODO: Check if $_map needs to contain anything.
            $_map['contribution'] = 'Contribute';
            $_map['custom_field'] = 'CustomGroup';
        }
        else {
            // assume $version == 3.
        }
    }
    if (isset($_map[strtolower($entity)])) {
        return $_map[strtolower($entity)];
    }
    $fragments = explode('_', $entity);
    foreach ($fragments as &$fragment) {
        $fragment = ucfirst($fragment);
    }
    // Special case: UFGroup, UFJoin, UFMatch, UFField
    if ($fragments[0] === 'Uf') {
        $fragments[0] = 'UF';
    }
    return implode('', $fragments);
}

/*
 * Call any nested api calls
 */
function _civicrm_api_call_nested_api(&$params, &$result, $action,$entity,$version){
        foreach($params as $field => $newparams){          
            if (substr($field,0, 3) == 'api' && (is_array($newparams) || $newparams === 1) ){
              
                $idIndex = _civicrm_api_get_results_id_index($params,$result);
                              
                if ($newparams === 1){
                  $newparams = array('version' => $version);
                }
                $separator = $field[3]; // can be api_ or api.
                if (!($separator == '.' || $separator == '_')) {
                    continue;
                }
                $subAPI = explode($separator,$field);

                $action = empty($subAPI[2])?$action:$subAPI[2];
                $subParams  = array();
                
                            
                if(strtolower($subAPI[1]) != 'contact'){
                  //contact spits the dummy at activity_id so what else won't it like?
                  $subParams["entity_id"] = $result['id'];
                  $subParams[strtolower($entity) . "_id"] = $result['id'];
                  $subParams['entity_table'] = $entity;
                }
               
                if(CRM_Utils_Array::value('entity_table',$result['values'][$idIndex ]) == $subAPI[1] ) {
                  $subParams['id'] = $result['values'][$idIndex ]['entity_id'];
              
                }
                if (strtolower(CRM_Utils_Array::value(2,$subAPI)) == 'delete'){
                  $subParams["id"] = $result['id'];
                }
                
                $subParams['version'] = $version;
                $subParams['sequential'] = 1;
                if(array_key_exists(0, $newparams)){
                    // it is a numerically indexed array - ie. multiple creates
                    foreach ($newparams as $entity => $entityparams){
                        $subParams = array_merge($subParams,$entityparams);
                        _civicrm_api_replace_variables($subAPI[1],$action,$subParams,$result['values'][$idIndex],$separator);
                         $result['values'][$result['id']][$field][] = civicrm_api($subAPI[1],$action,$subParams);
                        
                    }
                }else{

                    $subParams = array_merge($subParams,$newparams);
                    _civicrm_api_replace_variables($subAPI[1],$action,$subParams,$result['values'][$idIndex],$separator);
                    $result['values'][$idIndex ][$field] = civicrm_api($subAPI[1],$action,$subParams);
                        
                }
            }
        }
}
/*
 * Figure out the entity ID in the result array 
 * $result['id'] or 
 * $result['values'][1] or 
 * $result['values'][0]['id']
 * or
 * $result['values']['0']['entity_id']
 */

function _civicrm_api_get_entity_ID_from_results(&$result,$entity){
  if(CRM_Utils_Array::value('id',$result)){
    return CRM_Utils_Array::value('id',$result);
  }
  if (CRM_Utils_Array::value('values',$result)){
    
  }else{
    //hasn't been through create_success yet
  }
  
}
/*
 * Swap out any $values vars - ie. the value after $value is swapped for the parent $result
 * 'activity_type_id' => '$value.testfield',
   'tag_id'  => '$value.api.tag.create.id',  
    'tag1_id' => '$value.api.entity.create.0.id'
 */
function _civicrm_api_replace_variables($entity,$action,&$params, &$parentResult,$separator = '.' ){
 

  foreach ($params as $field => $value) {

    if(substr($value, 0,6) == '$value') {
      $valuesubstitute =  substr($value, 7); 
     
      if (!empty($parentResult[$valuesubstitute])){ 
        $params[$field] = $parentResult[$valuesubstitute];
      }else{
        
        $stringParts = explode($separator,$value);
        unset($stringParts[0]);
        
        $fieldname = array_shift($stringParts);

        //when our string is an array we will treat it as an array from that . onwards
        $count = count($stringParts);
        while($count > 0){
          $fieldname .= "." . array_shift($stringParts);
          if(is_array($parentResult[$fieldname])){
            $arrayLocation = $parentResult[$fieldname];
            foreach($stringParts as $key => $value){
             $arrayLocation = $arrayLocation[$value] ;              
             }
             $params[$field] = $arrayLocation ;
          }
          $count = count($stringParts);
          
        }

   
        }
        
      }

    }   
  }

/*
 * Get the field the results are indexed by (0 for sequential, $reuslt['id'] otherwise
 */
function _civicrm_api_get_results_id_index(&$params,&$result){
      if(CRM_Utils_Array::value('sequential',$params) == 1){
        return  0;
      }else{
        return $result['id'];
       }
}
