<?php
require_once 'api/v3/utils.php';
require_once 'CRM/Core/BAO/OptionGroup.php';

function civicrm_api3_option_group_get( $params ) {
    _civicrm_api3_initialize(true);
    try{
    civicrm_api3_verify_mandatory($params);



      $bao = new CRM_Core_BAO_OptionGroup( );
      _civicrm_api3_dao_set_filter ( $bao, $params );

      return civicrm_api3_create_success(_civicrm_api3_dao_to_array ($bao,$params));
    } catch (PEAR_Exception $e) {
      return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
      return civicrm_api3_create_error( $e->getMessage() );
    }
}
/**
 * create/update survey
 *
 * This API is used to create new survey or update any of the existing
 * In case of updating existing survey, id of that particular survey must
 * be in $params array. 
 *
 * @param array $params  (referance) Associative array of property
 *                       name/value pairs to insert in new 'survey'
 *
 * @return array   survey array
 *
 * @access public
 */
function civicrm_api3_option_group_create( $params )
{
    civicrm_api3_verify_mandatory($params);

    $ids = array();
    $bao = CRM_Core_BAO_OptionGroup::add($params,$ids);
 
    if ( is_null( $bao) ) {
      
      return civicrm_api3_create_error( 'Entity not created' );
    } else {
      $values = array();
      _civicrm_api3_object_to_array($bao, $values[ $bao->id]);
      return civicrm_api3_create_success($values,$params,$bao );
    }


}

