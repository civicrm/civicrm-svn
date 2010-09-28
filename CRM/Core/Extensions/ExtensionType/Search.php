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

class CRM_Core_Extensions_ExtensionType_Search extends CRM_Core_Extensions_ExtensionType
{


    /**
     * 
     */
    const OPTION_GROUP_NAME = 'system_extensions';
    const CUSTOM_SEARCH_GROUP_NAME = 'custom_search';

    private $allowedExtTypes = array( 'payment', 'search', 'report' );
    
    public function install( $id, $key ) {
        parent::install( $id, $key );

        $groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', 
                                                  self::CUSTOM_SEARCH_GROUP_NAME, 'id', 'name' );

        $customSearches = CRM_Core_OptionGroup::values(self::CUSTOM_SEARCH_GROUP_NAME, true, false, false, null, 'name', false );

//        CRM_Core_Error::debug( $customSearches );

        if( array_key_exists( $key, $customSearches ) ) {
            CRM_Core_Error::fatal( 'This custom search is already registered.' );
        }
        
        $e = parent::$_extensions;

        $ids = array();
            
        $params = array( 'option_group_id' => $groupId,
                         'description' => $e['per_id'][$id]['label'] . ' (' . $key . ')',
                         'name'  => $key,
                         'value' => max( $customSearches ) + 1,
                         'label'  => $key,
                         'is_active' => 0
                      );
                      
        $optionValue = CRM_Core_BAO_OptionValue::add($params, $ids);
                
    }

    public function deinstall( $id, $key ) {
        parent::deinstall( $id, $key );       
    }
}