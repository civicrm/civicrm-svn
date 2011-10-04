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

abstract class CRM_Core_ScheduledJob
{

    /*
     * Name of the job.
     * @var boolean true if running.
     */
    public $name;

    public $cli;

    /*
     * Class constructor
     * 
     * @param string $namespace namespace prefix for component's files
     * @access public
     * 
     */
    public function __construct( )
    {
        $this->_logStart();
    }                                                          

    public function __destruct( )
    {
        $this->_logEnd();
    }


    /**
     * Starts execution of the job.
     *
     * @return boolean 
     * @access public
     *
     */
    abstract protected function run();

    /**
     *
     * @return array|null collection of permissions, null if none
     * @access public
     *
     */
    public function logEntry() {
    }


    /**
     * Logs jobs start.
     * 
     * @return void
     * @access public
     *
     */
    private function _logStart( )
    {
        //TBD
        CRM_Core_Error::debug_log_message( $name );
    }


    /**
     * Logs jobs finish.
     * 
     * @return void
     * @access public
     *
     */
    private function _logEnd( )
    {
        //TBD
        CRM_Core_Error::debug_log_message( $name );        
    }

}