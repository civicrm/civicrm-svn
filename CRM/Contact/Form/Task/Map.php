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

require_once 'CRM/Contact/Form/Task.php';

/**
 * This class provides the functionality to map 
 * the address for group of
 * contacts. 
 */
class CRM_Contact_Form_Task_Map  extends CRM_Contact_Form_Task {

    /**
     * Are we operating in "single mode", i.e. mapping address to one
     * specific contact?
     *
     * @var boolean
     */
    protected $_single = false;
   

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                            $this, false );
        $lid = CRM_Utils_Request::retrieve( 'lid', 'Positive',
                                            $this, false );
        $eid = CRM_Utils_Request::retrieve( 'eid', 'Positive',
                                            $this, false );
        $type = 'Contact';
        if ( $cid ) {
            $ids = array( $cid );
            $this->_single     = true;
        } else if ( $eid ) {
            $ids = $eid;
            $type = 'Event';
        } else {
            parent::preProcess( );
            $ids = $this->_contactIds;
        }
        self::createMapXML( $ids, $lid, $this, true ,$type);
        $this->assign( 'single', $this->_single );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    public function buildQuickForm()
    {
        $this->addButtons( array( 
                                 array ( 'type'      => 'done', 
                                         'name'      => ts('Done'), 
                                         'isDefault' => true   ), 
                                 ) 
                           ); 
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
           
    }//end of function


    /**
     * assign smarty variables to the template that will be used by google api to plot the contacts
     *
     * @param array $contactIds list of contact ids that we need to plot
     * @param int   $locationId location_id
     *
     * @return string           the location of the file we have created
     * @access protected
     */
    static function createMapXML( $ids, $locationId, &$page, $addBreadCrumb, $type = 'Contact' ) {

        $config =& CRM_Core_Config::singleton( );

        CRM_Utils_System::setTitle( ts('Map Location(s)'));
        $page->assign( 'query', 'CiviCRM Search Query' );
        $page->assign( 'mapProvider', $config->mapProvider );
        $page->assign( 'mapKey', $config->mapAPIKey );
        if( $type == 'Contact' ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $locations =& CRM_Contact_BAO_Contact::getMapInfo( $ids , $locationId );
        } else {
            require_once 'CRM/Event/BAO/Event.php';
            $locations =& CRM_Event_BAO_Event::getMapInfo( $ids );
        }

        if ( empty( $locations ) ) {
            CRM_Core_Error::statusBounce(ts('This contact\'s primary address does not contain latitude/longitude information and can not be mapped.'));
        }

        if ( $addBreadCrumb ) {
            $session =& CRM_Core_Session::singleton(); 
            $redirect = $session->readUserContext(); 
            if ( $type == 'Contact') {
                $bcTitle = ts('Contact');
            } else {
                $bcTitle = ts('Event Info');
                $session->pushUserContext( CRM_Utils_System::url('civicrm/event/info', 'reset=1&action=preview&id='. $ids ) );
            }
            CRM_Utils_System::appendBreadCrumb( $bcTitle, $redirect );
        }

        $page->assign_by_ref( 'locations', $locations );

        // only issue a javascript warning if we know we will not
        // mess the poor user with too many warnings
        if ( count( $locations ) <= 3 ) {
            $page->assign( 'geoCodeWarn', true );
        } else {
            $page->assign( 'geoCodeWarn', false );
        }

        $sumLat = $sumLng = 0;
        $maxLat = $maxLng = -400;
        $minLat = $minLng = +400;
        foreach ( $locations as $location ) {
            $sumLat += $location['lat'];
            $sumLng += $location['lng'];

            if ( $location['lat'] > $maxLat ) {
                $maxLat = $location['lat'];
            }
            if ( $location['lat'] < $minLat ) {
                $minLat = $location['lat'];
            }

            if ( $location['lng'] > $maxLng ) {
                $maxLng = $location['lng'];
            }
            if ( $location['lng'] < $minLng ) {
                $minLng = $location['lng'];
            }
        }

        $center = array( 'lat' => (float ) $sumLat / count( $locations ),
                         'lng' => (float ) $sumLng / count( $locations ) );
        $span   = array( 'lat' => (float ) ( $maxLat - $minLat ),
                         'lng' => (float ) ( $maxLng - $minLng ) );
        $page->assign_by_ref( 'center', $center );
        $page->assign_by_ref( 'span'  , $span   );
    }
}

?>
