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

interface CRM_Contact_Form_Search_Interface {
    /**
     * The constructor gets the submitted form values
     */
    function __construct( &$formValues );

    /**
     * Builds the quickform for this search
     */
    function buildForm( &$form );

    /**
     * Builds the search query for various cases. We break it down into finer cases
     * since you can optimize each query independently. All the functions below return
     * a sql clause with only SELECT, FROM, WHERE sub-parts. The ORDER BY and LIMIT is
     * added at a later stage
     */

    /**
     * Count of records that match the current input parameters
     * Used by pager
     */
    function count     ( &$queryParams );

    /**
     * List of contact ids that match the current input parameters
     * Used by different tasks. Will be also used to optimize the
     * 'all' query below to avoid excessive LEFT JOIN blowup
     */
    function contactIDs( &$queryParams, $offset, $rowcount, $sort );

    /**
     * Retrieve all the values that match the current input parameters
     * Used by the selector
     */
    function all       ( &$queryParams, $offset, $rowcount, $sort );

    /**
     * The below two functions (from and where) are ONLY used if you want to
     * expose a custom group as a smart group and be able to send a mailing
     * to them via CiviMail. civicrm_email should be part of the from clause
     * CiviMail will pick up the contacts where the email is primary and
     * is not on hold / opt out / do not email
     *
     */

    /**
     * The from clause for the query 
     */
    function from      ( &$queryParams );

    /**
     * The where clause for the query 
     */
    function where     ( &$queryParams );

    /**
     * The template FileName to use to display the results
     */
    function  templateFile( );

    /**
     * Returns an array of column headers and field names and sort options
     */
    function &columns( );

}

?>
