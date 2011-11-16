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
    
    var $currentJob = null;

    /*
     * Class constructor
     * 
     * @param void
     * @access public
     * 
     */
    public function __construct( ) {
        require_once 'CRM/Core/Config.php';
        $config = CRM_Core_Config::singleton();
        $config->fatalErrorHandler = 'CRM_Core_JobManager_scheduledJobFatalErrorHandler';

        $this->jobs = $this->_getJobs();
    }                                                          

    /*
     * 
     * @param void
     * @access private
     * 
     */
    public function execute( ) {
        $this->logEntry( 'Starting scheduled jobs execution' );
        require_once 'CRM/Utils/System.php';
       if( !CRM_Utils_System::authenticateKey( FALSE ) ) {
           $this->logEntry( 'Could not authenticate the site key.' );
       }
        require_once 'api/api.php';

        foreach( $this->jobs as $job ) {
            if( $job->is_active ) {
                if( $job->needsRunning( ) ) {
                    $this->currentJob = $job;
                    $job->saveLastRun();
                    $this->logEntry( 'Starting execution of ' . $job->name );
                    
                    try {
                        $result = civicrm_api( $job->api_entity, $job->api_action, $job->apiParams );
                    } catch (Exception $e) {
                        $this->logEntry( 'Error while executing ' . $job->name . ': ' . $e->getMessage() );
                    }
;
                    $this->logEntry( 'Finished execution of ' . $job->name . ' with result: ' . $this->_apiResultToMessage( $result )  );
                }
            }
            $this->currentJob = FALSE;
        }
        $this->logEntry( 'Finishing scheduled jobs execution.' );        
    }

    /*
     * Class destructor
     * 
     * @param void
     * @access public
     * 
     */
    public function __destruct( ) {
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
        require_once 'CRM/Core/DAO/JobLog.php';        
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
        $domainID = CRM_Core_Config::domainID( );
        require_once 'CRM/Core/DAO/JobLog.php';
        $dao = new CRM_Core_DAO_JobLog( );

        $dao->domain_id  = $domainID;
        $dao->description = $message;        
        if( $this->currentJob ) {
            $dao->job_id = $this->currentJob->id;
            $dao->name = $this->currentJob->name;
            $dao->command = ts("Prefix:") . " " . $this->currentJob->api_prefix + " " . ts("Entity:") . " " + $this->currentJob->api_entity + " " . ts("Action:") . " " + $this->currentJob->api_action;
            $dao->data = "Parameters raw: \n\n" . $this->currentJob->parameters;
            if( $this->currentJob->apiParams ) {
                $dao->data .= "\n\nParameters parsed: \n\n" . serialize( $this->currentJob->apiParams);
            }
        }
        $dao->save( );
    }

    private function _apiResultToMessage( $apiResult ) {
        $status = $apiResult['is_error'] ? ts('Failure') : ts('Success');
        $message =  $apiResult['is_error'] ? ', Error message: ' . $apiResult['error_message'] : "\n\r" . $apiResult['values'];
        return $status . $message;
    }

 

}

function CRM_Core_JobManager_scheduledJobFatalErrorHandler( $message ) {
    throw new Exception( "{$message['message']}: {$message['code']}" );
}