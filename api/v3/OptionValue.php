<?
require_once 'api/v3/utils.php';

function civicrm_api3_option_value_get( $params ) {
    _civicrm_api3_initialize(true);
    try{
     civicrm_api3_verify_mandatory($params);
     //do we need anything mandatory eg. name or group ?

      require_once 'CRM/Core/BAO/OptionValue.php';
      $bao = new CRM_Core_BAO_OptionValue( );
//      $bao = civicrm_api3_get_DAO ('OptionValue');

      _civicrm_api3_dao_set_filter (&$bao,$params );

      return civicrm_api3_create_success(_civicrm_api3_dao_to_array ($bao,$params));
    } catch (PEAR_Exception $e) {
      return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
      return civicrm_api3_create_error( $e->getMessage() );
    }
}

?>
