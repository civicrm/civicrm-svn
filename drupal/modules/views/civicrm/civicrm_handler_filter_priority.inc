<?php
/*
 * Heavily based on anonymous code contribution. No claim.
 * CiviCRM Views Integration
 *
 * civicrm_handler_filter_state.inc
 * Filters States, and is configured using the labels from the database
 *
 */


class civicrm_handler_filter_priority extends views_handler_filter_in_operator {
    static $_priority;

    function construct( ) {
        parent::construct();
        if ( ! self::$_priority ) {
            if ( ! civicrm_initialize( ) ) {
                return;
            }
            require_once 'CRM/Core/PseudoConstant.php';
            self::$_priority = CRM_Core_PseudoConstant::priority( );
        }
    }

    function get_value_options() {
        if (!isset($this->value_options)) {
            $this->value_title = t('Priority');
            $options = array( );
            foreach (self::$_priority as $id => $name ) {
                $options[$id] = $name;
            }
            $this->value_options = $options;
        }
    }

}
?>
