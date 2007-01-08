<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                  |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Mailing/PseudoConstant.php';
/**
 * Meta information about the mailing
 *
 */
class CRM_Mailing_Form_Name extends CRM_Core_Form {

    /**
     * This function sets the default values for the form.
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {

        $mailingID = $this->get("mid");
        
        $defaults = array( );
        if ( $mailingID ) {
            $defaults["name"] = CRM_Core_DAO::getFieldValue("CRM_Mailing_DAO_Mailing",$mailingID,"name","id");
        }
        
        return $defaults;
    }
    
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->add( 'text', 'name', ts('Name Your Mailing'),
                    CRM_Core_DAO::getAttribute( 'CRM_Mailing_DAO_Mailing', 'name' ),
                    true );
        $this->addRule('name', ts('Name already exists in Database.'),
            'objectExists', array('CRM_Mailing_DAO_Component', $this->_id));

        /**
        $template =& CRM_Mailing_PseudoConstant::template( );
        if ( ! empty( $template ) ) {
            $template = array( '' => '-select-' ) + $template;
            $this->add('select'  , 'template'    , ts('Mailing Template'), $template );
        }

        $this->add('checkbox', 'is_template' , ts('Mailing Template?'));
        **/

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Next >>'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

    }

    public function postProcess() 
    {
        $mailingName = $this->controller->exportValue($this->_name, 'name');
        $isTemplate  = $this->controller->exportValue($this->_name, 'template');
        $this->set('mailing_name', $mailingName);
        $this->set('template', $isTemplate);
    }

    /**
     * Display Name of the form
     *
     * @access public
     * @return string
     */
    public function getTitle( ) 
    {
        return ts( 'Name' );
    }

}

?>
