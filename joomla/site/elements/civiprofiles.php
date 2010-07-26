<?php
  /*
   +--------------------------------------------------------------------+
   | CiviCRM version 3.2                                                |
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

  // Retrieve list of CiviCRM profiles
  // Active
  // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementCiviprofiles extends JElement {
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'CiviProfiles';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		// Initiate CiviCRM
		require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
		require_once 'CRM/Core/Config.php';
		$config =& CRM_Core_Config::singleton( );
		
		//require_once 'api/v2/UFGroup.php';
		// Would like to eventually retrieve profiles using API
		// Currently does not provide sufficient control to retrieve list
        
		// Get list of all profiles and assign to options array
		$options = array();
		
    	$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT civicrm_uf_group.id AS value, civicrm_uf_group.title AS text '
            .' FROM civicrm_uf_join'
            .' INNER JOIN civicrm_uf_group ON (civicrm_uf_join.uf_group_id = civicrm_uf_group.id)'
            .' WHERE (civicrm_uf_group.is_active = 1)'
            .' ORDER BY civicrm_uf_group.title ASC';
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		
		return JHTML::_( 'select.genericlist', $options, 'params[gid]', null, 'value', 'text', $value );
	}
}
?>