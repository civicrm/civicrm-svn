<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Profile/Selector/Listings.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Core/Page.php';

/**
 * This implements the profile page for all contacts. It uses a selector
 * object to do the actual dispay. The fields displayd are controlled by
 * the admin
 */
class CRM_Profile_Page_Listings extends CRM_Core_Page {

    /**
     * all the fields that are listings related
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /** 
     * the custom fields for this domain
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_customFields;

    /**
     * The input params from the request
     *
     * @var array 
     * @access protected 
     */ 
    protected $_params;

    /** 
     * The group id that we are editing
     * 
     * @var int 
     */ 
    protected $_gid; 

    /** 
     * state wether to display serch form or not
     * 
     * @var int 
     */ 
    protected $_search; 
    
    /**
     * Should we display a map
     *
     * @var int
     */
    protected $_map;

    /**
     * extracts the parameters from the request and constructs information for
     * the selector object to do a query
     *
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) {
        
        $this->_search = true;
        
        $search = CRM_Utils_Request::retrieve( 'search', 'Boolean',
                                               $this, false, 0, 'GET' );
        if( isset( $search ) && $search == 0) {
            $this->_search = false;
        }

        $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive',
                                                  $this, false, 0, 'GET' );

        $this->_map = CRM_Utils_Request::retrieve( 'map', 'Boolean',
                                                   $this, false, 0, 'GET' );
        // map only one specific profile
        $this->_map = $this->_gid ? $this->_map : 0;
        
        require_once 'CRM/Core/BAO/UFGroup.php';
        $this->_fields =
            CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::UPDATE,
                                                    CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                    false, $this->_gid, true, 'Profile' );

        $this->_customFields = CRM_Core_BAO_CustomField::getFieldsForImport( null );
        $this->_params   = array( );
        
        foreach ( $this->_fields as $name => $field ) {
            if ( (substr($name, 0, 6) == 'custom') && $field['is_search_range']) {
                $from = CRM_Utils_Request::retrieve( $name.'_from', 'String',
                                                     $this, false, null, 'REQUEST' );
                $to = CRM_Utils_Request::retrieve( $name.'_to', 'String',
                                                   $this, false, null, 'REQUEST' );
                $value = array();
                if ( $from && $to ) {
                    $value['from'] = $from;
                    $value['to']   = $to;
                } else if ( $from ) {
                    $value['from'] = $from;
                } else if ( $to ) {
                    $value['to'] = $to;
                }
            } else {
                $value = CRM_Utils_Request::retrieve( $name, 'String',
                                                      $this, false, null, 'REQUEST' );
            }
            
            if ( ( $name == 'group' || $name == 'tag' ) && ! empty( $value ) && ! is_array( $value ) ) {
                $v = explode( ',', $value );
                $value = array( );
                foreach ( $v as $item ) {
                    $value[$item] = 1;
                }
            }

            $customField = CRM_Utils_Array::value( $name, $this->_customFields );
            if ( ! empty( $_POST ) && ! CRM_Utils_Array::value( $name, $_POST ) ) {
                if ( $customField ) {
                    // reset checkbox because a form does not send null checkbox values
                    if ( $customField['html_type'] == 'CheckBox' ) {
                        // only reset on a POST submission if we dont see any value
                        $value = null;
                        $this->set( $name, $value );
                    }
                } else if ( $name == 'group' || $name == 'tag' || $name == 'preferred_communication_method' || 
                            $name == 'do_not_phone' || $name == 'do_not_email' || $name == 'do_not_mail' || $name == 'do_not_trade' ) {
                    $value = null;  
                    $this->set( $name, $value );  
                }
            }

            if ( isset( $value ) && $value != null ) {
                $this->_fields[$name]['value'] = $value;
                $this->_params[$name] = $value;
            } 
        }
   }

    /** 
     * run this page (figure out the action needed and perform it). 
     * 
     * @return void 
     */ 
    function run( ) {
        $this->preProcess( );

        
        $this->assign( 'recentlyViewed', false );
        if ( $this->_gid ) {
            // set the title of the page
            $title = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'title' );
            if ( $title ) {
                CRM_Utils_System::setTitle( $title );
            }
        }

        // do not do any work if we are in reset mode
        if ( ! CRM_Utils_Array::value( 'reset', $_GET ) || CRM_Utils_Array::value( 'force', $_GET ) ) {
            $this->assign( 'isReset', false );
            if ( $this->_map ) {
                $this->map( );
                return;
            } else {
                $map      = 0;
                $linkToUF = 0;
                if ( $this->_gid ) {
                    $map      = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'is_map'     );
                    $linkToUF = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'is_uf_link' );
                }
                if ( $map ) {
                    $this->assign( 'mapURL',
                                   CRM_Utils_System::url( 'civicrm/profile',
                                                          '_qf_Search_display=true&map=1' ) );
                }
                
                $editLink = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'is_edit_link' );
                if ( ! CRM_Core_Permission::check( 'access CiviCRM' ) ) {
                    $editLink = false;
                }

                $selector =& new CRM_Profile_Selector_Listings( $this->_params, $this->_customFields, $this->_gid,
                                                                $map, $editLink, $linkToUF );

                $controller =& new CRM_Core_Selector_Controller($selector ,
                                                                $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                                $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                                CRM_Core_Action::VIEW, $this, CRM_Core_Selector_Controller::TEMPLATE );
                $controller->setEmbedded( true );
                $controller->run( );
            }
        } else {
            $this->assign( 'isReset', true );
        }
   
        if ( $this->_search ) {
            $formController =& new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Search', ts('Search Profile'), CRM_Core_Action::ADD );
            $formController->setEmbedded( true );
            $formController->process( ); 
            $formController->run( ); 
        }

        $this->assign( 'search', $this->_search );

        return parent::run( );
    }

    function map( ) {
        $details = array( );
        $ufGroupParam   = array('id' => $this->_gid );
        CRM_Core_BAO_UFGroup::retrieve($ufGroupParam, $details);

        // make sure this group can be mapped
        if ( ! $details['is_map'] ) {
            CRM_Core_Error::statusBounce( 'This profile does not have the map feature turned on' );
        }

        $groupId = CRM_Utils_Array::value('limit_listings_group_id', $details);
        $groupId = CRM_Utils_Array::value('limit_listings_group_id', $details);
        
        // add group id to params if a uf group belong to a any group
        if ($groupId) {
            if ( CRM_Utils_Array::value('group', $this->_params ) ) {
                $this->_params['group'][$groupId] = 1;
            } else {
                $this->_params['group'] = array($groupId => 1);
            }
        }
        
        $this->_fields = CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::VIEW,
                                                                 CRM_Core_BAO_UFGroup::PUBLIC_VISIBILITY |
                                                                 CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                                 false, $this->_gid );

        $returnProperties =& CRM_Contact_BAO_Contact::makeHierReturnProperties( $this->_fields );
        $returnProperties['contact_type'] = 1;
        $returnProperties['sort_name'   ] = 1;

        $queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_params, 1 );
        $this->_query   =& new CRM_Contact_BAO_Query( $queryParams, $returnProperties, $this->_fields );
        
        $ids = $this->_query->searchQuery( 0, 0, null, 
                                           false, false, false, 
                                           true, false );                            
        $contactIds = explode( ',', $ids );

        require_once 'CRM/Contact/Form/Task/Map.php';
        CRM_Contact_Form_Task_Map::createMapXML( $contactIds, null, $this, false );

        $template =& CRM_Core_Smarty::singleton( );
        $template->assign( 'isProfile', 1 );
        $content = $template->fetch( 'CRM/Contact/Form/Task/Map.tpl' );
        echo CRM_Utils_System::theme( 'page', $content, true, false );
        return;
    }

    function getTemplateFileName() {
        if ( $this->_gid ) {
            $templateFile = "CRM/Profile/Page/{$this->_gid}/Listings.tpl";
            $template     =& CRM_Core_Page::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }
        }
        return parent::getTemplateFileName( );
    }

}

?>
