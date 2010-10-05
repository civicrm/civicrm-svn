<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Config.php';

class CRM_Core_Extensions_ExtensionType_Report extends CRM_Core_Extensions_ExtensionType
{


    /**
     * 
     */
    const REPORT_GROUP_NAME = 'report_template';

    public function __construct( $ext ) {
        $this->ext = $ext;
    }
    
    public function install( ) {
        $groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', 
                                                  self::REPORT_GROUP_NAME, 'id', 'name' );


                
        if( $this->ext->typeInfo['component'] === 'Contact' ) {
            $compId = 'null';
        } else {
            $comp = CRM_Core_Component::get( $this->ext->typeInfo['component'] );
            $compId = $comp->componentID;
        }

        if( empty($compId) ) {
            CRM_Core_Error::fatal( "Component for which you're trying to install the extension (" . $e['per_id'][$id]['type_info']['component'] . ") is currently disabled." );
        }

        $weight = CRM_Utils_Weight::getDefaultWeight( 'CRM_Core_DAO_OptionValue',
                                                                      array( 'option_group_id' => $groupId ) );


        $ids = array();
        $params = array( 'label'        => $this->ext->label . ' (' . $this->ext->key . ')',
                         'value'        => $this->ext->typeInfo['reportUrl'],
                         'name'         => $this->ext->key,
                         'weight'       => $weight,
                         'description'  => $this->ext->label . ' (' . $this->ext->key . ')',
                         'component_id' => $compId,
                         'option_group_id' => $groupId,
                         'is_active' => 1 );

        $optionValue = CRM_Core_BAO_OptionValue::add($params, $ids);
    }

    public function deinstall( $id, $key ) {
        parent::deinstall( $id, $key );
    }
}