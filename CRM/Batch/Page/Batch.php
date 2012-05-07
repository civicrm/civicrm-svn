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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


/**
 * Page for displaying list of current batches 
 */
class CRM_Batch_Page_Batch extends CRM_Core_Page_Basic 
{
  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = null;

  /**
   * Get BAO Name
   *
   * @return string Classname of BAO.
   */
  function getBAOName() {
    return 'CRM_Core_BAO_Batch';
  }

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {            
    if ( !( self::$_links ) ) {
      self::$_links = array(
        CRM_Core_Action::COPY  => array(
          'name'  => ts('Enter records'),
          'url'   => 'civicrm/batch/entry',
          'qs'    => 'id=%%id%%&reset=1',
          'title' => ts('Batch Entry') 
        ),
        CRM_Core_Action::UPDATE  => array(
          'name'  => ts('Edit'),
          'url'   => 'civicrm/batch',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Batch') 
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/batch',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Batch') 
        )
      );
    }
    return self::$_links;
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_Batch_Form_Batch';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return ts('Batch Processing');
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = null) 
  {
    return  CRM_Utils_System::currentPath( );
  }

  /**
   * browse all entities.
   *
   * @param int $action
   *
   * @return void
   * @access public
   */
  function browse( ) {
    $n = func_num_args();
    $action = ($n > 0) ? func_get_arg(0) : null;
    $links =& $this->links();
    if ($action == null) {
      if ( ! empty( $links ) ) {
        $action = array_sum(array_keys($links));
      }
    }

    eval( '$object = new ' . $this->getBAOName( ) . '( );' );

    $values = array();

    $object->orderBy ( 'title asc' );

    $batchTypes    = CRM_Core_PseudoConstant::getBatchType();
    $batchStatuses = CRM_Core_PseudoConstant::getBatchStatus();        

    // find all objects
    $object->find();
    $permission = array();
    $creatorIds = array();
    while ($object->fetch()) {
      $action = array_sum(array_keys($links));
      $values[$object->id] = array( );
      CRM_Core_DAO::storeValues( $object, $values[$object->id]);
      
      $values[$object->id]['status'] = $batchStatuses[$object->status_id];
      $values[$object->id]['type'  ] = $batchTypes[$object->type_id];

      // if batch is closed don't allow delete and update action
      if ( $object->status_id == 2 ){
        $action -= CRM_Core_Action::UPDATE;
        $action -= CRM_Core_Action::DELETE;
      }

      if ( !in_array( $object->created_id, $creatorIds ) ) {
        $creatorIds[] = $object->created_id;
      }

      // populate action links
      $this->action( $object, $action, $values[$object->id], $links, $permission );
    }

    // get sort name of the creators
    if ( !empty( $creatorIds ) ) {
      $query = "SELECT id, sort_name from civicrm_contact where id IN ( ". implode(',', $creatorIds).")";
      $dao = CRM_Core_DAO::executeQuery( $query );
      $creatorNames = array();
      while( $dao->fetch() ) {
        $creatorNames[$dao->id] = $dao->sort_name;
      }
      $this->assign( 'creatorNames', $creatorNames );
    }

    $this->assign( 'rows', $values );
  }

}
