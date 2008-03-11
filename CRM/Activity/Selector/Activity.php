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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Activity/BAO/Activity.php';


/**
 * This class is used to retrieve and display activities for a contact
 *
 */
class CRM_Activity_Selector_Activity extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions - Details and Delete.
     *
     * @var array
     * @static
     */
    static $_actionLinks;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * contactId - contact id of contact whose activies are displayed
     *
     * @var int
     * @access protected
     */
    protected $_contactId;

    protected $_admin;

    protected $_context;
    
    protected $_viewOptions;

    /**
     * Class constructor
     *
     * @param int $contactId - contact whose activities we want to display
     * @param int $permission - the permission we have for this contact 
     *
     * @return CRM_Contact_Selector_Activity
     * @access public
     */
    function __construct($contactId, $permission, $admin = false, $context = 'activity' ) 
    {
        $this->_contactId  = $contactId;
        $this->_permission = $permission;
        $this->_admin      = $admin;
        $this->_context    = $context;

        // get all enabled view componentc (check if case is enabled)
        require_once 'CRM/Core/BAO/Preferences.php';
        $this->_viewOptions = CRM_Core_BAO_Preferences::valueOptions( 'contact_view_options', true, null, true );
    }

    /**
     * This method returns the action links that are given for each search row.
     * currently the action links added for each row are 
     * 
     * - View
     *
     * @param string $activityType type of activity
     *
     * @return array
     * @access public
     *
     */
    function actionLinks( $activityTypeId, $sourceRecordId = null ) 
    {
        $activityTypes = CRM_Core_PseudoConstant::activityType( false );

        //show  edit link only for meeting/phone and other activities
        $showUpdate = false;
        $showDelete = false;
        if ( array_key_exists(  $activityTypeId,  $activityTypes ) || $activityTypeId > 9 ) {
            $showUpdate = true;
            $showDelete = true;
            $url      = 'civicrm/contact/view/activity';
            $qsView   = "atype={$activityTypeId}&action=view&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%";
            $qsUpdate = "atype={$activityTypeId}&action=update&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%";
        } elseif ( $activityTypeId == 5 )  { // event registration
            $url      = 'civicrm/contact/view/participant';
            $qsView   = "action=view&reset=1&id={$sourceRecordId}&cid=%%cid%%&context=%%cxt%%";
        } elseif ( $activityTypeId == 6 ) { //contribution
            $url      = 'civicrm/contact/view/contribution';
            $qsView   = "action=view&reset=1&id={$sourceRecordId}&cid=%%cid%%&context=%%cxt%%";
        } elseif ( in_array($activityTypeId, array( 7, 8 ) ) ) {  // membership
            $url      = 'civicrm/contact/view/membership';
            $qsView   = "action=view&reset=1&id={$sourceRecordId}&cid=%%cid%%&context=%%cxt%%";
        } else {
            $showDelete = true;
            $url      = 'civicrm/activity/view';
            $delUrl   = 'civicrm/activity';
            $qsView   = "atype={$activityTypeId}&action=view&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%";
        }

        $qsDelete  = "atype={$activityTypeId}&action=delete&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%";
        
        if ( $this->_context == 'case' ) {
            $qsView   .= "&caseid=%%caseid%%";
            $qsUpdate .= "&caseid=%%caseid%%";
            $qsDelete .= "&caseid=%%caseid%%";
        }
        
        self::$_actionLinks = array(
                                    CRM_Core_Action::VIEW => 
                                    array(
                                          'name'     => ts('View'),
                                          'url'      => $url,
                                          'qs'       => $qsView,
                                          'title'    => ts('View Activity'),
                                          )
                                    );
        if ( $showUpdate ) {
            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::UPDATE => 
                                                                 array(
                                                                       'name'     => ts('Edit'),
                                                                       'url'      => $url,
                                                                       'qs'       => $qsUpdate,
                                                                       'title'    => ts('Update Activity') ) );
        }

        if ( $showDelete ) {
            if ( ! isset($delUrl) || ! $delUrl ) {
                $delUrl = $url;
            }
            
            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::DELETE => 
                                                                 array(
                                                                       'name'     => ts('Delete'),
                                                                       'url'      => $delUrl,
                                                                       'qs'       => $qsDelete,
                                                                       'title'    => ts('Delete Activity') ) );
        }
        
        if ( $this->_context == 'case' ) {
            $qsDetach = "atype={$activityTypeId}&action=detach&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%&caseid=%%caseid%%";

            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::DETACH => 
                                                                 array(
                                                                       'name'     => ts('Detach'),
                                                                       'url'      => $url,
                                                                       'qs'       => $qsDetach,
                                                                       'title'    => ts('Detach Activity') ) );
        }

        return self::$_actionLinks;
    }

    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = ts('Activities %%StatusMessage%%');
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }


    /**
     * returns the column headers as an array of tuples:
     * (name, sortName (key to the sort array))
     *
     * @param string $action the action being performed
     * @param enum   $output what should the result set include (web/email/csv)
     *
     * @return array the column headers that need to be displayed
     * @access public
     */
    function &getColumnHeaders($action = null, $output = null) 
    {
        if ($output==CRM_Core_Selector_Controller::EXPORT || $output==CRM_Core_Selector_Controller::SCREEN) {
            $csvHeaders = array( ts('Activity Type'), ts('Description'), ts('Activity Date'));
            foreach (self::_getColumnHeaders() as $column ) {
                if (array_key_exists( 'name', $column ) ) {
                    $csvHeaders[] = $column['name'];
                }
            }
            return $csvHeaders;
        } else {
            $columnHeaders = self::_getColumnHeaders();
            //unset case if not enabled
            if ( ! $this->_viewOptions['Cases'] ) { 
                unset( $columnHeaders[1]);
            }
            return $columnHeaders;
        }
        
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param string $action - action being performed
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        return CRM_Activity_BAO_Activity::getNumOpenActivity($this->_contactId, $this->_admin, $this->_context );
    }


    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */
    function &getRows($action, $offset, $rowCount, $sort, $output = null, $case = null) 
    {
        $params['contact_id'] = $this->_contactId;

        $rows =& CRM_Activity_BAO_Activity::getOpenActivities($params, $offset, $rowCount, $sort,
                                                              'Activity', $this->_admin, $case, $this->_context );
        
        if ( empty( $rows ) ) {
            return $rows;
        }

        $activityStatus = CRM_Core_PseudoConstant::activityStatus( );
        
        foreach ($rows as $k => $row) {
            $row =& $rows[$k];

            // DRAFTING: provide a facility for db-stored strings
            // localize the built-in activity names for display
            // (these are not enums, so we can't use any automagic here)
            switch ($row['activity_type']) {
                case 'Meeting':    $row['activity_type'] = ts('Meeting');    break;
                case 'Phone Call': $row['activity_type'] = ts('Phone Call'); break;
                case 'Email':      $row['activity_type'] = ts('Email');      break;
                case 'SMS':        $row['activity_type'] = ts('SMS');        break;
                case 'Event':      $row['activity_type'] = ts('Event');      break;
            }

            // add class to this row if overdue
            if ( CRM_Utils_Date::overdue( $row['activity_date_time'] ) && $row['status_id'] == 1 ) {
                $row['overdue'] = 1;
                $row['class']   = 'status-overdue';
            } else {
                $row['overdue'] = 0;
                $row['class']   = 'status-ontime';
            }
                  
            $row['status'] = $row['status_id']?$activityStatus[$row['status_id']]:null;

            $actionLinks = $this->actionLinks( $row['activity_type_id'], $row['source_record_id'] );
            $actionMask  = array_sum(array_keys($actionLinks)) & CRM_Core_Action::mask( $this->_permission );
            
            if ( $output != CRM_Core_Selector_Controller::EXPORT && $output != CRM_Core_Selector_Controller::SCREEN ) {
                $row['action'] = CRM_Core_Action::formLink( $actionLinks,
                                                            $actionMask,
                                                            array('id'     => $row['id'],
                                                                  'cid'    => $this->_contactId,
                                                                  'cxt'    => $this->_context,
                                                                  'caseid' => $row['case_id']) );
            }
            unset($row);
        }
        
        return $rows;
    }
    
    /**
     * name of export file.
     *
     * @param string $output type of output
     * @return string name of the file
     */
    function getExportFileName($output = 'csv')
    {
        return ts('CiviCRM Activity');
    }

    /**
     * get colunmn headers for search selector
     *
     *
     * @return array $_columnHeaders
     * @access private
     */
    private static function &_getColumnHeaders() 
    {
        if (!isset(self::$_columnHeaders)) {
            self::$_columnHeaders = array(
                                          array('name'      => ts('Type'),
                                                'sort'      => 'activity_type',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Case'),
                                                'sort'      => 'case_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Subject'),
                                                'sort'      => 'subject',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Added By'),
                                                'sort'      => 'source_contact_name',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('With'),
                                                'sort'      => 'target_contact_name',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Assigned To'),
                                                'sort'      => 'assignee_contact_name',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Date'),
                                                'sort'      => 'activity_date_time',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Status'),
                                                'sort'      => 'status_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('desc' => ts('Actions')),
                                          );
        }

        return self::$_columnHeaders;
    }
}
?>
