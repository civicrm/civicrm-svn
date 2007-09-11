<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.9                                                |
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

require_once 'CRM/Mailing/Selector/Browse.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Core/Page.php';

/**
 * This implements the profile page for all contacts. It uses a selector
 * object to do the actual dispay. The fields displayd are controlled by
 * the admin
 */
class CRM_Mailing_Page_Browse extends CRM_Core_Page {

    /**
     * all the fields that are listings related
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /**
     * the mailing id of the mailing we're operating on
     *
     * @int
     * @access protected
     */
    protected $_mailingId;

    /**
     * the action that we are performing (in CRM_Core_Action terms)
     *
     * @int
     * @access protected
     */
    protected $_action;

    protected $_pager = null;

    protected $_sortByCharacter;

    /**
     * Heart of the viewing process. The runner gets all the meta data for
     * the contact and calls the appropriate type of page to view.
     *
     * @return void
     * @access public
     *
     */
    function preProcess() 
    {
        $this->_mailingId = CRM_Utils_Request::retrieve('mid', 'Positive', $this);

        // check that the user has permission to access mailing id
        require_once 'CRM/Mailing/BAO/Mailing.php';
        CRM_Mailing_BAO_Mailing::checkPermission( $this->_mailingId );

        $this->_action    = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->assign('action', $this->_action);
    }

    /** 
     * run this page (figure out the action needed and perform it). 
     * 
     * @return void 
     */ 
    function run( ) {
        $this->preProcess(); 

        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );
        if ( $this->_sortByCharacter == 1 ||
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
            $this->set( 'sortByCharacter', '' );
        }

        $this->search( );

        $config =& CRM_Core_Config::singleton( );

        $params = array( );
        $whereClause = $this->whereClause( $params, false );
        $this->pagerAToZ( $whereClause, $params );

        $params      = array( );
        $whereClause = $this->whereClause( $params, true );
        $this->pager    ( $whereClause, $params );


        list( $offset, $rowCount ) = $this->_pager->getOffsetAndRowCount( );

        $query = "
  SELECT *
    FROM civicrm_mailing
   WHERE $whereClause
ORDER BY name asc
   LIMIT $offset, $rowCount";

        $object = CRM_Core_DAO::executeQuery( $query, $params, true, 'CRM_Mailing_DAO_Mailing' );
        $rowIds = array();
        while ($object->fetch()) {
            $rowIds[] = $object->id;
        }


        $url = CRM_Utils_System::url('civicrm/mailing/browse', 'reset=1');

        if ($this->_action & CRM_Core_Action::DISABLE) {                 
            if (CRM_Utils_Request::retrieve('confirmed', 'Boolean', $this )) {
                require_once 'CRM/Mailing/BAO/Job.php';
                CRM_Mailing_BAO_Job::cancel($this->_mailingId);
                CRM_Utils_System::redirect($url);
            } else {
                $controller =& new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Browse',
                                                               ts('Cancel Mailing'),
                                                               $this->_action );
                $controller->setEmbedded( true );
                
                // set the userContext stack
                $session =& CRM_Core_Session::singleton();
                $session->pushUserContext( $url );
                $controller->run( );
            }
        } else if ($this->_action & CRM_Core_Action::DELETE) {
            if (CRM_Utils_Request::retrieve('confirmed', 'Boolean', $this )) {
                require_once 'CRM/Mailing/BAO/Mailing.php';
                CRM_Mailing_BAO_Mailing::del($this->_mailingId);
                CRM_Utils_System::redirect($url);
            } else {
                $controller =& new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Browse',
                                                               ts('Delete Mailing'),
                                                               $this->_action );
                $controller->setEmbedded( true );
                
                // set the userContext stack
                $session =& CRM_Core_Session::singleton();
                $session->pushUserContext( $url );
                $controller->run( );
            }
        }
            

        CRM_Utils_System::setTitle(ts('Mailings'));

        $selector =& new CRM_Mailing_Selector_Browse( );
        $controller =& new CRM_Core_Selector_Controller(
                                                        $selector ,
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                        $this->get( CRM_Utils_Sort::SORT_ID ),
                                                        CRM_Core_Action::VIEW, 
                                                        $this, 
                                                        CRM_Core_Selector_Controller::TEMPLATE );
        $controller->setEmbedded( true );
        $controller->run( );

        // hack to display results as per search
        $rows = $controller->getRows($controller);
        foreach ($rows as $key => $row) {
            unset($rows[$key]['id']);
            if (! in_array($row['id'], $rowIds)) {
                unset($rows[$key]);
            }
        }
        $this->assign('rows', $rows);

        return parent::run( );
    }

    function search( ) {
        if ( $this->_action &
             ( CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE ) ) {
            return;
        }

        $form = new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Search', ts( 'Search Mailings' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }

    function whereClause( &$params, $sortBy = true ) {
        $values =  array( );

        $clauses = array( );
        $title   = $this->get( 'mailing_name' );
        //echo " name=$title  ";
        if ( $title ) {
            $clauses[] = 'name LIKE %1';
            if ( strpos( $title, '%' ) !== false ) {
                $params[1] = array( $title, 'String', false );
            } else {
                $params[1] = array( $title, 'String', true );
            }
        }

        if ( $sortBy &&
             $this->_sortByCharacter ) {
            $clauses[] = 'name LIKE %2';
            $params[2] = array( $this->_sortByCharacter . '%', 'String' );
        }

        $clauses[] = 'domain_id = %3';
        $params[3] = array( CRM_Core_Config::domainID( ), 'Integer' );
        
        return implode( ' AND ', $clauses );
    }

    function pagerAtoZ( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/PagerAToZ.php';
        
        $query = "
   SELECT DISTINCT UPPER(LEFT(name, 1)) as sort_name
     FROM civicrm_mailing
    WHERE $whereClause
 ORDER BY LEFT(name, 1)
";
        $dao = CRM_Core_DAO::executeQuery( $query, $whereParams );
        
        $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $dao, $this->_sortByCharacter, true );
        $this->assign( 'aToZ', $aToZBar );
    }
 
    function pager( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/Pager.php';

        $params['status']       = ts('Group %%StatusMessage%%');
        $params['csvString']    = null;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
        $params['rowCount']     = $this->get( CRM_Utils_Pager::PAGE_ROWCOUNT );
        if ( ! $params['rowCount'] ) {
            $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
        }

        $query = "
SELECT count(id)
  FROM civicrm_mailing
 WHERE $whereClause";

        $params['total'] = CRM_Core_DAO::singleValueQuery( $query, $whereParams );
        $this->_pager = new CRM_Utils_Pager( $params );
        $this->assign_by_ref( 'pager', $this->_pager );
    }
 
}

?>
