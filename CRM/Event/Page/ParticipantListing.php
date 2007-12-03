<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
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

require_once 'CRM/Core/Page.php';

class CRM_Event_Page_ParticipantListing extends CRM_Core_Page {

    protected $_id;

    protected $_participantListingID;

    protected $_pager;

    function preProcess( ) {
        $this->_id   = CRM_Utils_Request::retrieve( 'id'  , 'Integer', $this, true );

        // ensure that there is a particpant type for this
        $this->_participantListingID = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                                    $this->_id,
                                                                    'participant_listing_id' );
        if ( ! $this->_participantListingID ) {
            CRM_Core_Error::fatal( ts( "You cannot view the participants for this event" ) );
        }
    }

    function run( ) {
        $this->preProcess( );

        $this->assign( 'participantListingType', $this->_participantListingID );

        $fromClause  = "
FROM       civicrm_contact
INNER JOIN civicrm_participant ON civicrm_contact.id = civicrm_participant.contact_id 
INNER JOIN civicrm_event       ON civicrm_participant.event_id = civicrm_event.id
LEFT JOIN  civicrm_email       ON ( civicrm_contact.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1 )
";

        $whereClause = "
WHERE    civicrm_event.id = %1
AND      civicrm_participant.status_id IN ( 1, 2 )";
        $params = array( 1 => array( $this->_id, 'Integer' ) );
        $this->pager( $fromClause, $whereClause, $params );
        $orderBy = $this->orderBy( $this->_participantListingID );

        list( $offset, $rowCount ) = $this->_pager->getOffsetAndRowCount( );
        
        $query = "
SELECT   civicrm_contact.id           as contact_id,
         civicrm_contact.display_name as name      ,
         civicrm_contact.sort_name    as sort_name ,
         civicrm_email.email          as email
         $fromClause
         $whereClause
ORDER BY $orderBy
LIMIT    $offset, $rowCount";

        $rows = array( );
        $object = CRM_Core_DAO::executeQuery( $query, $params );
        while ( $object->fetch( ) ) {
            $row = array( 'id'    => $object->contact_id,
                          'name'  => $object->name      ,
                          'email' => $object->email );
            $rows[] = $row;
        }
        $this->assign_by_ref( 'rows', $rows );

        return parent::run( );
    }

    function pager( $fromClause, $whereClause, $whereParams ) {
        require_once 'CRM/Utils/Pager.php';

        $params = array( );

        $params['status']       = ts('Group %%StatusMessage%%');
        $params['csvString']    = null;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
        $params['rowCount']     = $this->get( CRM_Utils_Pager::PAGE_ROWCOUNT );
        if ( ! $params['rowCount'] ) {
            $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
        }

        $query = "
SELECT count( civicrm_contact.id )
       $fromClause
       $whereClause";

        $params['total'] = CRM_Core_DAO::singleValueQuery( $query, $whereParams );
        $this->_pager = new CRM_Utils_Pager( $params );
        $this->assign_by_ref( 'pager', $this->_pager );
    }

    function orderBy( $participantListingID ) {
        static $headers = null;
        require_once 'CRM/Utils/Sort.php';
        if ( ! $headers ) {
            $headers = array( );
            $headers[1] = array( 'name'      => ts( 'Name' ),
                                 'sort'      => 'civicrm_contact.sort_name',
                                 'direction' => CRM_Utils_Sort::ASCENDING );
            if ( $participantListingID == 2 ) {
                $headers[2] = array( 'name'      => ts( 'Email' ),
                                     'sort'      => 'civicrm_email.email',
                                     'direction' => CRM_Utils_Sort::DONTCARE );
            }
        }
        $sortID = null;
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) {
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) );
        }
        $sort =& new CRM_Utils_Sort( $headers, $sortID );
        $this->assign_by_ref( 'headers', $headers );
        $this->assign_by_ref( 'sort'   , $sort    );
        $this->set( CRM_Utils_Sort::SORT_ID,
                    $sort->getCurrentSortID( ) );
        $this->set( CRM_Utils_Sort::SORT_DIRECTION,
                    $sort->getCurrentSortDirection( ) );

        return $sort->orderBy( );
    }


}

?>
