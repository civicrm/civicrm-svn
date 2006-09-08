<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
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
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright CiviCRM LLC (c) 2004-2006
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Profile/Form.php';

/**
 * This class generates form components generic to all the contact types.
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Profile_Form_Search extends CRM_Profile_Form
{
    /** 
     * pre processing work done here. 
     * 
     * @param  
     * @return void 
     * 
     * @access public 
     * 
     */ 
    function preProcess() 
    { 
        $this->_mode = CRM_Profile_Form::MODE_SEARCH; 
         
        parent::preProcess( ); 
    } 

    /** 
     * Set the default form values 
     * 
     * @access protected 
     * @return array the default array reference 
     */ 
    function &setDefaultValues() {
        $defaults = array(); 

        // note we intentionally overwrite value since we use it as defaults
        // and its all pass by value
        // we need to figure out the type, so we can either set an array element
        // or a scalar -- FIX ME sometime please
        foreach ( $_GET as $key => $value ) {
            if ( substr( $key, 0, 7 ) == 'custom_' ) {
                if ( strpos( $value, CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) !== false ) {
                    $v = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $value );
                    $value = array( );
                    foreach ( $v as $item ) {
                        $value[$item] = 1;
                    }
                }
            } else if ( $key == 'group' || $key == 'tag' ) {
                $v = explode( ',', $value );
                $value = array( ); 
                foreach ( $v as $item ) { 
                    $value[$item] = 1; 
                } 
            }
            $defaults[$key] = $value;
        }

        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons(array( 
                                array ('type'      => 'refresh', 
                                       'name'      => ts('Search'), 
                                       'isDefault' => true ), 
                                ) ); 

        parent::buildQuickForm( );
     }

       
    /**
     *
     *
     * @access public
     * @return void
     */
    public function postProcess() 
    {
    }
}

?>
