<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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

//require_once 'CRM/Contact/Form/Task.php';
require_once 'CRM/Contact/BAO/Contact.php';

/**
 * This class gets the name of the file to upload
 */
class CRM_Export_Form_Select extends CRM_Core_Form 
{
   
    /**
     * various Contact types
     */
    const
        EXPORT_ALL      = 1,
        EXPORT_SELECTED = 2;

    /**
     * export modes
     */
    const
        CONTACT_EXPORT     = 1,
        CONTRIBUTE_EXPORT  = 2,
        MEMBER_EXPORT      = 3,
        EVENT_EXPORT       = 4;


    /**
     * current export mode
     *
     * @var int
     */
    public $_exportMode;
    
    /**
     * build all the data structures needed to build the form
     *
     * @param
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        //special case for custom search, directly give option to download csv file
        $customSearchID = $this->get( 'customSearchID' );
        if ( $customSearchID ) {
            require_once 'CRM/Contact/BAO/Export.php';
            $export =& new CRM_Contact_BAO_Export( );
            $export->exportCustom( $this->get( 'customSearchClass' ),
                                   $this->get( 'formValues' ),
                                   $this->get( CRM_Utils_Sort::SORT_ORDER ) );
        }

        $this->_recordIds = array( ); 
        $this->_selectAll  = false;
        $this->_exportMode = self::CONTACT_EXPORT;

        // get the submitted values of the search form 
        // we'll need to get fv from either search or adv search in the future 
        if ( $this->_action == CRM_Core_Action::ADVANCED ) { 
            $values = $this->controller->exportValues( 'Advanced' ); 
        } else if ( $this->_action == CRM_Core_Action::PROFILE ) { 
            $values = $this->controller->exportValues( 'Builder' ); 
        } else if ( $this->_action == CRM_Core_Action::COPY ) {
            $values = $this->controller->exportValues( 'Custom' ); 
        } else {
            // we need to determine component export
            $stateMachine  =& $this->controller->getStateMachine( );
            $formName      = CRM_Utils_System::getClassName($stateMachine);
            $componentName = explode( '_', $formName );
            $components    = array( 'Contribute', 'Member', 'Event');
            
            if ( in_array( $componentName[1], $components ) ) {
                eval( '$this->_exportMode = self::' . strtoupper( $componentName[1] ) . '_EXPORT;');
                require_once "CRM/{$componentName[1]}/Form/Task.php";
                eval('CRM_' . $componentName[1] . '_Form_Task::preprocess();');
                $values = $this->controller->exportValues( 'Search' ); 
            } else {
                $values = $this->controller->exportValues( 'Basic' ); 
            }
        } 

        require_once 'CRM/Contact/Task.php';
        $this->_task = $values['task']; 
        if ( $this->_exportMode == self::CONTACT_EXPORT ) {
            $contactTasks = CRM_Contact_Task::taskTitles(); 
            $taskName = $contactTasks[$this->_task]; 
        } else {
            $this->assign( 'taskName', "Export $componentName[1]" ); 
            $this->_task = $values['task'];
            eval( '$componentTasks = CRM_'. $componentName[1] .'_Task::tasks();' );
            $taskName = $componentTasks[$this->_task];
        }
        $this->assign('taskName', $taskName);

        // all contacts or action = save a search 
        if (($values['radio_ts'] == 'ts_all') || ($this->_task == CRM_Contact_Task::SAVE_SEARCH)) { 
            $this->_selectAll = true;
            $this->assign( 'totalSelectedRecords', $this->get( 'rowCount' ) );
        } else if($values['radio_ts'] == 'ts_sel') { 
            // selected contacts only 
            // need to perform action on only selected contacts 
            foreach ( $values as $name => $value ) { 
                if ( substr( $name, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) { 
                    $this->_recordIds[] = substr( $name, CRM_Core_Form::CB_PREFIX_LEN ); 
                } 
            } 
            $this->assign( 'totalSelectedRecords', count( $this->_recordIds ) ); 
        }
        
        $this->set( 'recordIds', $this->_recordIds );
        $this->set( 'selectAll' , $this->_selectAll  );
        $this->set( 'exportMode' , $this->_exportMode );
        $this->set( 'componentClause', $this->_componentClause );
    }


    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        //export option
        $exportoptions = array();        
        $exportOptions[] = HTML_QuickForm::createElement('radio',
                                                         null, null,
                                                         ts('Export PRIMARY fields'),
                                                         self::EXPORT_ALL,
                                                         array( 'onClick' => 'showMappingOption( );' ));
        $exportOptions[] = HTML_QuickForm::createElement('radio',
                                                         null, null,
                                                         ts('Select fields for export'),
                                                         self::EXPORT_SELECTED,
                                                         array( 'onClick' => 'showMappingOption( );' ));

        $this->addGroup($exportOptions, 'exportOption', ts('Export Type'), '<br/>');
        
        $this->buildMapping( );

        $this->setDefaults(array('exportOption' => self::EXPORT_ALL ));

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Continue >>'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }

    /**
     * Process the uploaded file
     *
     * @return void
     * @access public
     */
    public function postProcess( ) 
    {
        $exportOption = $this->controller->exportValue( $this->_name, 'exportOption' ); 

        $mappingId = $this->controller->exportValue( $this->_name, 'mapping' ); 
        if ( $mappingId ) {
            $this->set('mappingId', $mappingId);
        } else {
            $this->set('mappingId', null);
        }

        require_once 'CRM/Contact/BAO/Export.php';
        $export =& new CRM_Contact_BAO_Export( );

        if ( $exportOption == self::EXPORT_ALL ) {
            require_once "CRM/Export/BAO/Export.php";
            CRM_Export_BAO_Export::exportComponents( $this->_selectAll,
                                                     $this->_recordIds,
                                                     $this->get( 'queryParams' ),
                                                     $this->get( CRM_Utils_Sort::SORT_ORDER ),
                                                     null,
                                                     $this->get( 'returnProperties' ),
                                                     $this->_exportMode,
                                                     $this->_componentClause
                                                     );
        }
        
        //reset map page
        $this->controller->resetPage( 'Map' );
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) {
        return ts('Export All or Selected Fields');
    }

    /**
     * Function to build mapping form element
     *
     */
    function buildMapping( ) 
    {
        switch ( $this->_exportMode ) {
        case CRM_Export_Form_Select::CONTACT_EXPORT : 
            $exportType = 'Export Contact';
            break;
        case CRM_Export_Form_Select::CONTRIBUTE_EXPORT : 
            $exportType = 'Export Contribution';
            break;
        case CRM_Export_Form_Select::MEMBER_EXPORT : 
            $exportType = 'Export Membership';
            break;
        case CRM_Export_Form_Select::EVENT_EXPORT : 
            $exportType = 'Export Participant';
            break;
        }

        require_once "CRM/Core/BAO/Mapping.php";
        $mappingTypeId = CRM_Core_OptionGroup::getValue( 'mapping_type', $exportType, 'name' );
        $this->set( 'mappingTypeId', $mappingTypeId );

        $mappings  = CRM_Core_BAO_Mapping::getMappings( $mappingTypeId );
        if ( !empty( $mappings ) ) {
            $this->add('select','mapping', ts('Mapping Option'), array('' => '-select-') + $mappings );
        }
    }

}


