<?php

/*
 * Heavily based on anonymous code contribution. No claim.
 * CiviCRM Views Integration
 *
 * civicrm_handler_filter_event_type.inc
 * Filters Event Types, and is configured using the labels from the database
 *
 */

class civicrm_handler_filter_encounter_medium extends views_handler_filter_in_operator {
    static $_encounter_medium;

    function construct( ) {
        parent::construct();
        if ( ! self::$_encounter_medium ) {
            if ( ! civicrm_initialize( ) ) {
                return;
            }
            require_once 'CRM/Core/OptionGroup.php';
            self::$_encounter_medium = CRM_Core_OptionGroup::values( 'encounter_medium', false );;
        }
    }

    function get_value_options() {
        if (!isset($this->value_options)) {
            $this->value_title = t('Encounter Medium');
            $options = array( );
            foreach (self::$_encounter_medium as $id => $name ) {
                $options[$id] = $name;
            }
            $this->value_options = $options;
        }
    }

}
