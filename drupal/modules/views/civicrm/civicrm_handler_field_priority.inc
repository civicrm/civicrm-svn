<?php

/**
 * Field handler to provide acess control for the state field (which is a lookup)
 *
 * @ingroup civicrm_field_handlers
 */
class civicrm_handler_field_priority extends views_handler_field {
    static $_priority;

    function construct( ) {
        if ( ! self::$_priority ) {
            if ( ! civicrm_initialize( ) ) {
                return;
            }
            require_once 'CRM/Core/PseudoConstant.php';
            self::$_priority = CRM_Core_PseudoConstant::priority( );
        }
    }

    function render( $values ) {
        $sid = $values->{$this->field_alias};
        if ( empty( $sid ) ||
             (int ) $sid <= 0 ) {
            return null;
        }

        return self::$_priority[$values->{$this->field_alias}];
    }
}
?>
