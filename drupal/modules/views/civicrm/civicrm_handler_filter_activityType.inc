<?php

class civicrm_handler_filter_activityType extends civicrm_handler_filter_option {
  
    function initialize( ) {
        parent::construct();
        if ( ! self::$_options ) {
            if ( ! civicrm_initialize( ) ) {
                return;
            }
            require_once 'CRM/Core/PseudoConstant.php';
            self::$_states = CRM_Core_PseudoConstant::activityType( );
        }
    }

    function title( ) {
        return t('State/Province');
    }
}
?>
