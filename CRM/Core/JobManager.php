<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * This interface defines methods that need to be implemented
 * by every scheduled job (cron task) in CiviCRM.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class CRM_Core_JobManager
{


    var $jobs = null;

    /*
     * Class constructor
     * 
     * @param void
     * @access public
     * 
     */
    public function __construct( ) {
        $this->logEntry( 'Starting scheduled jobs execution' );
        $this->jobs = $this->_getJobs();
    }                                                          

    /*
     * 
     * @param void
     * @access private
     * 
     */
    public function execute( ) {
        require_once 'CRM/Utils/System.php';        
        CRM_Utils_System::authenticateKey( );
        require_once 'api/api.php';
        foreach( $this->jobs as $job ) {
            $this->logEntry( 'Starting execution of ' . $job->name );
            $result = civicrm_api( $job->apiEntity, $job->apiAction, $job->apiParams );
            $this->logEntry( 'Finished execution of ' . $job->name . ' with result: ' . $this->_apiResultToMessage( $result )  );
        }
        $this->logEntry( 'Executing' );
    }

    /*
     * Class destructor
     * 
     * @param void
     * @access public
     * 
     */
    public function __destruct( ) {
        $this->logEntry( 'Finishing scheduled jobs execution.' );
    }

    /*
     * Retrieves the list of jobs from the database,
     * populates class param.
     * 
     * @param void
     * @access private
     * 
     */
    private function _getJobs( ) {
        $jobs = array();
        require_once 'CRM/Core/DAO/Job.php';
        $dao = new CRM_Core_DAO_Job();
        $dao->orderBy('name');
        $dao->find();
        require_once 'CRM/Core/ScheduledJob.php';
        while ($dao->fetch()) {
            CRM_Core_DAO::storeValues( $dao, $temp);
            $jobs[$dao->id] = new CRM_Core_ScheduledJob( $temp );
        }
        return $jobs;
    }


    /*
     *
     * @return array|null collection of permissions, null if none
     * @access public
     *
     */
    public function logEntry( $message ) {
        CRM_Core_Error::debug_log_message( date('l jS \of F Y h:i:s A') . ": " . $message );
    }


    private function _apiResultToMessage( $apiResult ) {
        $status = $apiResult['is_error'] ? ts('Failure') : ts('Success');
        $message =  $apiResult['is_error'] ?  $apiResult['error_message'] : '';
        $msg = 'Status: ' . $status . ( $apiResult['is_error'] ? ', Error message: ' . $apiResult['error_message'] : '');
        return $msg;
    }

}