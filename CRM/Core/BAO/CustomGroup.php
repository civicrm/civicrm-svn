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

require_once 'CRM/Core/DAO/CustomGroup.php';

/**
 * Business object for managing custom data groups
 *
 */
class CRM_Core_BAO_CustomGroup extends CRM_Core_DAO_CustomGroup 
{

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }

    /**
     * takes an associative array and creates a custom group object
     *
     * @param array $params (reference) an assoc array of name/value pairs
     *
     * @return object CRM_Core_DAO_CustomGroup object 
     * @access public
     * @static
     */
    static function create(&$params)
    {
        $customGroupBAO =& new CRM_Core_BAO_CustomGroup();
        $customGroupBAO->copyValues($params);
        return $customGroupBAO->save();
    }



    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_DAO_CustomGroup object
     * @access public
     * @static
     */
    static function retrieve(&$params, &$defaults)
    {
        return CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_CustomGroup', $params, $defaults );
    }

    /**
     * update the is_active flag in the db
     *
     * @param  int      $id         id of the database record
     * @param  boolean  $is_active  value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     * @access public
     */
    static function setIsActive($id, $is_active) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_CustomGroup', $id, 'is_active', $is_active );
    }

    /**
     * Get custom groups/fields for type of entity.
     *
     * An array containing all custom groups and their custom fields is returned.
     *
     * @param string $entityType - of the contact whose contact type is needed
     * @param int    $entityId   - optional - id of entity if we need to populate the tree with custom values. 
     * @param int    $groupId    - optional group id (if we need it for a single group only)
     *                           - if groupId is 0 it gets for inline groups only
     *                           - if groupId is -1 we get for all groups
     *
     * @return array $groupTree  - array consisting of all groups and fields and optionally populated with custom data values.
     *
     * @access public
     *
     * @static
     *
     */
    public static function &getTree( $entityType,
                                     $entityID = null,
                                     $groupID  = null,
                                     $subType  = null )
    {
        // create a new tree
        $groupTree = array();
        $strSelect = $strFrom = $strWhere = $orderBy = ''; 
        $tableData = array();

        // using tableData to build the queryString 
        $tableData =
            array(
                  'civicrm_custom_field' =>
                  array('id',
                        'label',
                        'column_name',
                        'data_type',
                        'html_type',
                        'default_value',
                        'attributes',
                        'is_required',
                        'help_post',
                        'options_per_line',
                        'start_date_years',
                        'end_date_years',
                        'date_parts',
                        'option_group_id' ),
                  'civicrm_custom_group' =>
                  array('id',
                        'name',
                        'table_name',
                        'title',
                        'help_pre',
                        'help_post',
                        'collapse_display', ),
                  );

       // create select
        $select = array( );
        foreach ($tableData as $tableName => $tableColumn) {
            foreach ($tableColumn as $columnName) {
                $alias = $tableName . "_" . $columnName;
                $select[] = "{$tableName}.{$columnName} as {$tableName}_{$columnName}";
            }
        }
        $strSelect = "SELECT " . implode( ', ', $select );

        // from, where, order by
        $strFrom = "
FROM     civicrm_custom_group
LEFT JOIN civicrm_custom_field ON (civicrm_custom_field.custom_group_id = civicrm_custom_group.id)
";

        // if entity is either individual, organization or household pls get custom groups for 'contact' too.
        if ($entityType == "Individual" || $entityType == 'Organization' || $entityType == 'Household') {
            $in = "'$entityType', 'Contact'";
        } else {
            $in = "'$entityType'";
        }

        $domainID = CRM_Core_Config::domainID( );
        if ( $subType ) {
            $strWhere = "
WHERE civicrm_custom_group.domain_id = $domainID
  AND civicrm_custom_group.is_active = 1 
  AND civicrm_custom_field.is_active = 1 
  AND civicrm_custom_group.extends IN ($in)
  AND ( civicrm_custom_group.extends_entity_column_value = '$subType'
   OR   civicrm_custom_group.extends_entity_column_value IS NULL )
";
        } else {
            $strWhere = "
WHERE civicrm_custom_group.domain_id = $domainID
  AND civicrm_custom_group.is_active = 1 
  AND civicrm_custom_field.is_active = 1 
  AND civicrm_custom_group.extends IN ($in)
  AND civicrm_custom_group.extends_entity_column_value IS NULL
";
        }
 
        $params = array( );
        if ( $groupID > 0 ) {
            // since we want a specific group id we add it to the where clause
            $strWhere .= " AND civicrm_custom_group.style = 'Tab' AND civicrm_custom_group.id = %1";
            $params[1] = array( $groupID, 'Integer' );
        } else if ( ! $groupID ){
            // since groupID is false we need to show all Inline groups
            $strWhere .= " AND civicrm_custom_group.style = 'Inline'";
        }

        require_once 'CRM/Core/Permission.php';
        // ensure that the user has access to these custom groups
        $strWhere .= 
            " AND " .
            CRM_Core_Permission::customGroupClause( CRM_Core_Permission::VIEW,
                                                    'civicrm_custom_group.' );
        
        $orderBy = "
ORDER BY civicrm_custom_group.weight,
         civicrm_custom_group.title,
         civicrm_custom_field.weight,
         civicrm_custom_field.label
";

        // final query string
        $queryString = "$strSelect $strFrom $strWhere $orderBy";

        // dummy dao needed
        $crmDAO =& CRM_Core_DAO::executeQuery( $queryString, $params );
        
        $customValueTables = array( );

        // process records
        while( $crmDAO->fetch( ) ) {
            // get the id's 
            $groupID = $crmDAO->civicrm_custom_group_id;
            $fieldId = $crmDAO->civicrm_custom_field_id;

            // create an array for groups if it does not exist
            if ( ! array_key_exists( $groupID, $groupTree ) ) {
                $groupTree[$groupID]       = array();
                $groupTree[$groupID]['id'] = $groupID;
                
                // populate the group information
                foreach ( $tableData['civicrm_custom_group'] as $fieldName ) {
                    $fullFieldName = "civicrm_custom_group_$fieldName";
                    if ( $fieldName == 'id' ||
                         is_null( $crmDAO->$fullFieldName ) ) {
                        continue;
                    }
                    $groupTree[$groupID][$fieldName] = $crmDAO->$fullFieldName;
                }
                $groupTree[$groupID]['fields'] = array();
                
                $customValueTables[$crmDAO->civicrm_custom_group_table_name] = array( );
            }

            // add the fields now (note - the query row will always contain a field)
            // we only reset this once, since multiple values come is as multiple rows
            if ( ! array_key_exists( $fieldId, $groupTree[$groupID]['fields'] ) ) {
                $groupTree[$groupID]['fields'][$fieldId] = array();
            }
            $customValueTables[$crmDAO->civicrm_custom_group_table_name][$crmDAO->civicrm_custom_field_column_name] = 1;
            $groupTree[$groupID]['fields'][$fieldId]['id'] = $fieldId;
            // populate information for a custom field
            foreach ($tableData['civicrm_custom_field'] as $fieldName) {
                $fullFieldName = "civicrm_custom_field_$fieldName";
                if ( $fieldName == 'id' ||
                     is_null( $crmDAO->$fullFieldName ) ) {
                    continue;
                } 
                $groupTree[$groupID]['fields'][$fieldId][$fieldName] = $crmDAO->$fullFieldName;                    
            }
        }

        // now that we have all the groups and fields, lets get the values
        // since we need to know the table and field names
        if ( $entityID ) {
            $entityID = CRM_Utils_Type::escape( $entityID, 'Integer' );
        }

        // add info to groupTree
        if ( ! empty( $customValueTables ) ) {
            $groupTree['info'] = array( 'tables' => $customValueTables );
            
            $select = $from = $where = array( );
            foreach ( $groupTree['info']['tables'] as $table => $fields ) {
                $from[]   = $table;
                $select[] = "{$table}.id as {$table}_id";
                $select[] = "{$table}.entity_id as {$table}_entity_id";
                foreach ( $fields as $column => $dontCare ) {
                    $select[] = "{$table}.{$column} as {$table}_{$column}";
                }
                if ( $entityID ) {
                    $where[]  = "{$table}.entity_id = $entityID";
                }
            }

            $groupTree['info']['select'] = $select;
            $groupTree['info']['from'  ] = $from  ;
            $groupTree['info']['where' ] = null   ;

            if ( $entityID ) {
                $groupTree['info']['where' ] = $where ;
                $select = implode( ', '   , $select );

                // this is a hack to find a table that has some values for this
                // entityID to make the below LEFT JOIN work (CRM-2518)
                $firstTable = null;
                foreach ( $from as $table ) {
                    $query = "
SELECT id
FROM   $table
WHERE  entity_id = $entityID
";
                    $recordExists = CRM_Core_DAO::singleValueQuery( $query,
                                                                    CRM_Core_DAO::$_nullArray );
                    if ( $recordExists ) {
                        $firstTable = $table;
                        break;
                    }
                }

                if ( $firstTable ) {
                    $fromSQL    = $firstTable;
                    foreach ( $from as $table ) {
                        if ( $table != $firstTable ) {
                            $fromSQL .= "\nLEFT JOIN $table USING (entity_id)";
                        }
                    }

                    $query = "
SELECT $select
  FROM $fromSQL
 WHERE {$firstTable}.entity_id = $entityID
";

                    $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
                
                    if ( $dao->fetch( ) ) {
                        foreach ( $groupTree as $groupID => $group ) {
                            if ( $groupID === 'info' ) {
                                continue;
                            }
                            $table = $groupTree[$groupID]['table_name'];
                            foreach ( $group['fields'] as $fieldID => $dontCare ) {
                                $column    = $groupTree[$groupID]['fields'][$fieldID]['column_name'];
                                $idName    = "{$table}_id";
                                $fieldName = "{$table}_{$column}";
                                if ( isset($dao->$fieldName) ) {
                                    $dataType  = $groupTree[$groupID]['fields'][$fieldID]['data_type'];
                                    if ( $dataType == 'File' ) {
                                        require_once 'CRM/Core/DAO/File.php';
                                        $config =& CRM_Core_Config::singleton( );
                                        $fileDAO =& new CRM_Core_DAO_File();
                                        $fileDAO->id = $dao->$fieldName;
                                        if ( $fileDAO->find(true) ) {
                                            $entityIDName = "{$table}_entity_id";
                                            $customValue['data']    = $fileDAO->uri;
                                            $customValue['fid']     = $fileDAO->id;
                                            $customValue['fileURL'] = 
                                                CRM_Utils_System::url( 'civicrm/file', "reset=1&id={$fileDAO->id}&eid={$dao->$entityIDName}" );
                                            $customValue['displayURL'] = null;
                                            $deleteExtra = ts('Are you sure you want to delete attached file.');
                                            $deleteURL =
                                                array( CRM_Core_Action::DELETE  =>
                                                       array(
                                                             'name'  => ts('Delete Attached File'),
                                                             'url'   => 'civicrm/file',
                                                             'qs'    => 'reset=1&id=%%id%%&eid=%%eid%%&fid=%%fid%%&action=delete',
                                                             'extra' => 
                                                             'onclick = "if (confirm( \''. $deleteExtra .'\' ) ) this.href+=\'&amp;confirmed=1\'; else return false;"'
                                                             ) 
                                                       );
                                            $customValue['deleteURL'] = 
                                                CRM_Core_Action::formLink( $deleteURL,
                                                                           CRM_Core_Action::DELETE,
                                                                           array( 'id'  => $fileDAO->id,
                                                                                  'eid' => $dao->$entityIDName,
                                                                                  'fid' => $fieldID ) );
                                            $customValue['fileName'] = basename( $fileDAO->uri );
                                            if ( $fileDAO->mime_type =="image/jpeg" ||
                                                 $fileDAO->mime_type =="image/gif"  ||
                                                 $fileDAO->mime_type =="image/png" ) {
                                                $customValue['displayURL'] = $customValue['fileURL'];
                                            }
                                        }
                                    } else {
                                        $customValue = array( 'id'   => $dao->$idName,
                                                              'data' => $dao->$fieldName );
                                    }
                                    $groupTree[$groupID]['fields'][$fieldID]['customValue'] = $customValue;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $uploadNames = array();
        foreach ($groupTree as $key1 => $group) { 
            if ( $key1 === 'info' ) {
                continue;
            }

            foreach ( $group['fields'] as $key2 => $field ) {
                if ( $field['data_type'] == 'File' ) {
                    $fieldId          = $field['id'];                 
                    $elementName      = "custom_$fieldId";
                    $uploadNames[]    = $elementName; 
                }
            }
        }
        //hack for field type File
        $session = & CRM_Core_Session::singleton( );
        $session->set('uploadNames', $uploadNames);
        return $groupTree;
    }


    /**
     * Update custom data.
     *
     *  - custom data is modified as follows
     *    - if custom data is changed it's updated.
     *    - if custom data is newly entered in field, it's inserted into db, finally
     *    - if existing custom data is cleared, it is deleted from the table.
     *
     * @param  array  &$groupTree - array of all custom groups, fields and values.
     * @param  string $entityType - type of entity being extended
     * @param  int    $entityId   - id of the contact whose custom data is to be updated
     * @return void
     *
     * @access public
     * @static
     *
     */
    public static function updateCustomData(&$groupTree, $entityType, $entityId)
    {
        $tableName = self::_getTableName($entityType);
        $update = array( );
        foreach ( $groupTree as $groupID => $group ) {
            if ( $groupID === 'info' ) {
                continue;
            }
            $table = $groupTree[$groupID]['table_name'];
            foreach ( $group['fields'] as $fieldID => $field ) {
                if ( isset( $field['customValue'] ) ) {
                    $column    = $groupTree[$groupID]['fields'][$fieldID]['column_name'];
                    $update[] = "{$table}.{$column} = '{$field['customValue']['data']}'";
                }
            }

            $query = "
SELECT entity_id 
FROM   {$table} 
WHERE  {$table}.entity_id = {$entityId}";

            $crmDAO =& CRM_Core_DAO::singleValueQuery( $query, CRM_Core_DAO::$_nullArray ); 
            if ( ! empty( $update ) ) {
                $tables = implode( ', ', $groupTree['info']['from'  ] );
                if ( $groupTree['info']['where' ] ) {
                    if( $crmDAO ) {
                        $sqlOP = 'UPDATE';
                        $where = ' WHERE ' . implode( ', ', $groupTree['info']['where' ] );
                    } else {
                        $sqlOP = 'INSERT INTO';
                        $where  = null;
                        $update[] = "{$table}.domain_id = " . CRM_Core_Config::domainID();
                        $update[] = "{$table}.entity_id = '{$entityId}'";
                    }
                } else {
                    $sqlOP  = 'SELECT';
                    $where  = null;
                }
                $update = implode( ', ', $update );
                                   
                $query = "
$sqlOP $tables
   SET $update
$where       
";
                $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            }
        }
    }


    /**
     * Get the group title.
     *
     * @param int $id id of group.
     * @return string title 
     *
     * @access public
     * @static
     *
     */
    public static function getTitle( $id )
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', $id, 'title' );
    }

    /**
     * Get custom group details for a group.
     *
     * An array containing custom group details (including their custom field) is returned.
     *
     * @param int     $groupId    - group id whose details are needed
     * @param boolean $searchable - is this field searchable
     * @param array   $extends    - which table does it extend if any 
     * @return array $groupTree - array consisting of all group and field details
     *
     * @access public
     *
     * @static
     *
     */
    public static function &getGroupDetail($groupId = null, $searchable = null, &$extends = null)
    {
        // create a new tree
        $groupTree = array();
        $select = $from = $where = $orderBy = ''; 

        $tableData = array();

        // using tableData to build the queryString 
        $tableData =
            array(
                  'civicrm_custom_field' =>
                  array('id',
                        'label',
                        'data_type',
                        'html_type',
                        'default_value',
                        'attributes',
                        'is_required',
                        'help_post',
                        'options_per_line',
                        'is_searchable',
                        'start_date_years',
                        'end_date_years',
                        'is_search_range',
                        'date_parts',
                        'note_columns',
                        'note_rows',
                        'column_name' ),
                  'civicrm_custom_group' =>
                  array('id',
                        'name',
                        'title',
                        'help_pre',
                        'help_post',
                        'collapse_display',
                        'extends',
                        'extends_entity_column_value',
                        'table_name' ),
                  );

        // create select
        $select = "SELECT"; 
        $s = array( );
        foreach ($tableData as $tableName => $tableColumn) {
            foreach ($tableColumn as $columnName) {
                $s[] = "{$tableName}.{$columnName} as {$tableName}_{$columnName}";
            }
        }
        $select = 'SELECT ' . implode( ', ', $s );
        $params     = array( );
        // from, where, order by
        $from = " FROM civicrm_custom_field, civicrm_custom_group";
        $where = " WHERE civicrm_custom_field.custom_group_id = civicrm_custom_group.id
                            AND civicrm_custom_group.is_active = 1
                            AND civicrm_custom_field.is_active = 1 ";
        if ( $groupId ) {
            $params[1] = array( $groupId, 'Integer' );
            $where .= " AND civicrm_custom_group.id = %1";
        }

        if ( $searchable ) {
            $where .= " AND civicrm_custom_field.is_searchable = 1";
        }

        if ( $extends ) {
            $clause = array( );
            foreach ( $extends as $e ) {
                $clause[] = "civicrm_custom_group.extends = '$e'";
            }
            $where .= " AND ( " . implode( ' OR ', $clause ) . " ) ";
        }

        $orderBy = " ORDER BY civicrm_custom_group.weight, civicrm_custom_field.weight";

        // final query string
        $queryString = $select . $from . $where . $orderBy;

        // dummy dao needed
        $crmDAO =& CRM_Core_DAO::executeQuery( $queryString, $params );
        
        // process records
        while($crmDAO->fetch()) {
            $groupId = $crmDAO->civicrm_custom_group_id;
            $fieldId = $crmDAO->civicrm_custom_field_id;

            // create an array for groups if it does not exist
            if (!array_key_exists($groupId, $groupTree)) {
                $groupTree[$groupId] = array();
                $groupTree[$groupId]['id'] = $groupId;
                
                foreach ($tableData['civicrm_custom_group'] as $v) {
                    $fullField = "civicrm_custom_group_" . $v;
                    
                    if ($v == 'id' || is_null($crmDAO->$fullField)) {
                        continue;
                    }
                    
                    $groupTree[$groupId][$v] = $crmDAO->$fullField;                    
                }
                
                $groupTree[$groupId]['fields'] = array();
                
            }
            
            // add the fields now (note - the query row will always contain a field)
            $groupTree[$groupId]['fields'][$fieldId] = array();
            $groupTree[$groupId]['fields'][$fieldId]['id'] = $fieldId;

            foreach ($tableData['civicrm_custom_field'] as $v) {
                $fullField = "civicrm_custom_field_" . $v;
                if ($v == 'id' || is_null($crmDAO->$fullField)) {
                    continue;
                }
                $groupTree[$groupId]['fields'][$fieldId][$v] = $crmDAO->$fullField;                    
            }
        }

        return $groupTree;
    }


    /**
     *
     * This function does 2 things - 
     *   1 - Create menu tabs for all custom groups with style 'Tab'
     *   2 - Updates tab for custom groups with style 'Inline'. If there
     *       are no inline groups it removes the 'Custom Data' tab
     *
     *
     * @param string $entityType  - what entity are we extending here ?
     * @param string $path        - what should be the starting path for the new menus ?
     * @param int    $startWeight - weight to start the local menu tabs
     *
     * @return void
     *
     * @access public
     * @static
     *
     */
    public static function addMenuTabs($entityType, $path, $startWeight)
    {
        $groups =& self::getActiveGroups( $entityType, $path );

        foreach( $groups as $group ) {
            $group['weight']  = $startWeight++;
            CRM_Core_Menu::add( $group );
        }
    }

    public static function &getActiveGroups( $entityType, $path, $cidToken = '%%cid%%' ) {
        // for Group's
        $customGroupDAO =& new CRM_Core_DAO_CustomGroup();

        // get only 'Tab' groups
        $customGroupDAO->whereAdd("style = 'Tab'");
        $customGroupDAO->whereAdd("is_active = 1");

        $customGroupDAO->whereAdd("domain_id =" . CRM_Core_Config::domainID() );
        
        // add whereAdd for entity type
        self::_addWhereAdd($customGroupDAO, $entityType);

        $groups = array( );

        $permissionClause = CRM_Core_Permission::customGroupClause( );
        $customGroupDAO->whereAdd( $permissionClause );
        
        // order by weight
        $customGroupDAO->orderBy('weight');
        $customGroupDAO->find();

        // process each group with menu tab
        while ($customGroupDAO->fetch( ) ) {
            $group = array();
            $group['id']      = $customGroupDAO->id;
            $group['path']    = $path;
            $group['title']   = "$customGroupDAO->title";
            $group['query']   = "reset=1&gid={$customGroupDAO->id}&cid={$cidToken}";
            $group['type']    = CRM_Core_Menu::CALLBACK;
            $group['crmType'] = CRM_Core_Menu::LOCAL_TASK;
            $group['extra' ]  = array( 'gid' => $customGroupDAO->id );
            $groups[] = $group;
        }
     
        return $groups;
    }

    /**
     * Get the table name for the entity type
     * currently if entity type is 'Contact', 'Individual', 'Household', 'Organization'
     * tableName is 'civicrm_contact'
     * 
     * @param string $entityType  what entity are we extending here ?
     *
     * @return string $tableName
     *
     * @access private
     * @static
     *
     */
    private static function _getTableName($entityType)
    {
        $tableName = '';
        switch($entityType) {
        case 'Contact':
        case 'Individual':
        case 'Household':
        case 'Organization':
            $tableName = 'civicrm_contact';
            break;
        case 'Contribution':
            $tableName = 'civicrm_contribution';
            break;
        case 'Group':
            $tableName = 'civicrm_group';
            break;
        // DRAFTING: Verify if we cannot make it pluggable
        case 'Activity':  
            $tableName = 'civicrm_activity';
            break;
        case 'Relationship':  
            $tableName = 'civicrm_relationship';
            break;
        case 'Membership':
            $tableName = 'civicrm_membership';
            break;
        case 'Participant':
            $tableName = 'civicrm_participant';
            break;
        case 'Event':
            $tableName = 'civicrm_event';
            break;
        case 'Grant':
            $tableName = 'civicrm_grant';
            break;  
            // need to add cases for Location, Address
        }
       
        return $tableName;
    }
    
    /**
     * Add the whereAdd clause for the DAO depending on the type of entity
     * the custom group is extending.
     *
     * @param object CRM_Core_DAO_CustomGroup (reference) - Custom Group DAO.
     * @param string $entityType    - what entity are we extending here ?
     *
     * @return void
     *
     * @access private
     * @static
     *
     */
    private static function _addWhereAdd(&$customGroupDAO, $entityType)
    {
        switch($entityType) {
        case 'Contact':
            // if contact, get all related to contact
            $customGroupDAO->whereAdd("extends IN ('Contact', 'Individual', 'Household', 'Organization')");
            break;
        case 'Individual':
        case 'Household':
        case 'Organization':
            // is I/H/O then get I/H/O and contact
            $customGroupDAO->whereAdd("extends IN ('Contact', '$entityType')");
            break;
        case 'Location':
        case 'Address':
            $customGroupDAO->whereAdd("extends IN ('$entityType')");
            break;
        }
    }


    /**
     * Delete the Custom Group.
     *
     * @param  int    $id  Group Id 
     * 
     * @return boolean   false if field exists for this group, true if group gets deleted.
     *
     * @access public
     * @static
     *
     */
    public static function deleteGroup( $group )
    { 
        require_once 'CRM/Core/DAO/CustomField.php';

        //check wheter this contain any custom fields
        $customField = & new CRM_Core_DAO_CustomField();
        $customField->custom_group_id = $group->id;
        $customField->find();
        if ($customField->fetch()) {
            return false;
        }

        // drop the table associated with this custom group
        require_once 'CRM/Core/BAO/SchemaHandler.php';
        CRM_Core_BAO_SchemaHandler::dropTable( $group->table_name );

        //delete  custom group
        $group->delete();
        return true;
    }

    static function setDefaults( &$groupTree, &$defaults, $viewMode, $inactiveNeeded ) 
    {
        foreach ( $groupTree as $id => $group ) {
            if ( $id === 'info' ) {
                continue;
            }
            
            $groupId = $group['id'];
            foreach ($group['fields'] as $field) {
                if ( CRM_Utils_Array::value( 'customValue', $field ) !== null ) {
                    $value = $field['customValue']['data'];
                } else if ( CRM_Utils_Array::value( 'default_value', $field ) !== null ) {
                    $value = $viewMode ? null : $field['default_value'];
                } else {
                    continue;
                }

                $fieldId = $field['id'];
                $elementName = 'custom_' . $fieldId;
                switch($field['html_type']) {

                case 'CheckBox':
                    $defaults[$elementName] = array( );
                    $customOption = CRM_Core_BAO_CustomOption::getCustomOption($field['id'], $inactiveNeeded);
                    if ($viewMode) {
                        $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
                        if ( isset($value) ) {
                            foreach( $customOption as $val ) {
                                if ( in_array( $val['value'], $checkedData ) ) {
                                    $defaults[$elementName][$val['value']] = 1;
                                } else {
                                    $defaults[$elementName][$val['value']] = 0;
                                }
                            }
                        }
                    } else {
                        if ( isset( $field['customValue']['data'] ) ) {
                            $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,substr($field['customValue']['data'],1,-1));
                            foreach( $customOption as $val ) {
                                if ( in_array( $val['value'], $checkedData ) ) {
                                    $defaults[$elementName][$val['value']] = 1;
                                } else {
                                    $defaults[$elementName][$val['value']] = 0;
                                }
                            }
                        } else {
                            $checkedValue = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
                            foreach($customOption as $val) {
                                if ( in_array($val['value'], $checkedValue) ) {
                                    $defaults[$elementName][$val['value']] = 1;
                                } else {
                                    $defaults[$elementName][$val['value']] = 0;
                                }
                            }                            
                        }
                    }
                    break;
                    
                //added a case for Multi-Select option                    
                case 'Multi-Select':
                    if ($viewMode) {
                        $customOption = CRM_Core_BAO_CustomOption::getCustomOption($field['id'], $inactiveNeeded);
                        $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
                        $defaults[$elementName] = array();
                        if(isset($value)) {
                            foreach($customOption as $val) {
                                if (in_array($val['value'], $checkedData)) {
                                    $defaults[$elementName][$val['value']] = $val['value'];
                                }
                            }
                        }
                    } else {
                        $customOption = CRM_Core_BAO_CustomOption::getCustomOption($field['id'], $inactiveNeeded);
                        $defaults[$elementName] = array();
                        if (isset($field['customValue']['data'])) {
                            $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($field['customValue']['data'],1,-1));
                            foreach($customOption as $val) {
                                if (in_array($val['value'], $checkedData)) {
                                    //$defaults[$elementName][$val['value']] = 1;
                                    $defaults[$elementName][$val['value']] = $val['value'];
                                } 
                            }
                        } else {
                            $checkedValue = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
                            foreach($customOption as $val) {
                                if ( in_array($val['value'], $checkedValue) ) {
                                    $defaults[$elementName][$val['value']] = $val['value'];
                                }
                            }                            
                        }
                    }
                    break;
                    
                case 'Select Date':
                    if (isset($value)) {
                        $defaults[$elementName] = CRM_Utils_Date::unformat( $value, '-' );
                    }
                   
                    break;
                    
                case 'Select Country':
                    if ( $value ) {
                        $defaults[$elementName] = $value;
                    } else {
                        $config          =& CRM_Core_Config::singleton( );
                        $defaults[$elementName] = $config->defaultContactCountry;
                    }
                    break;

                default:
                    if ($field['data_type'] == "Float") {
                        $defaults[$elementName] = (float)$value;
                    } else { 
                        $defaults[$elementName] = $value;
                    }
                } 
            }
        }
    }

    static function postProcess( &$groupTree, &$params ) 
    {
        // Get the Custom form values and groupTree        
        // first reset all checkbox and radio data
        foreach ($groupTree as $groupID => $group) {
            if ( $groupID === 'info' ) {
                continue;
            }              
            foreach ($group['fields'] as $field) {
                $groupId = $group['id'];
                $fieldId = $field['id'];

                //added Multi-Select option in the below if-statement
                if ( $field['html_type'] == 'CheckBox' || $field['html_type'] == 'Radio' || $field['html_type'] == 'Multi-Select' ) {
                    $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = 'NULL';
                }

                $v = CRM_Utils_Array::value( 'custom_' . $field['id'], $params );

                if ( ! isset($groupTree[$groupId]['fields'][$fieldId]['customValue'] ) ) {
                    // field exists in db so populate value from "form".
                    $groupTree[$groupId]['fields'][$fieldId]['customValue'] = array();
                }

                switch ( $groupTree[$groupId]['fields'][$fieldId]['html_type'] ) {

                //added for CheckBox
                case 'CheckBox':  
                    if ( ! empty( $v ) ) {
                        $customValue = array_keys( $v );
                        $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = 
                            CRM_Core_BAO_CustomOption::VALUE_SEPERATOR.implode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $customValue).CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
                    } else {
                        $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = null;
                    }
                    break;

                //added for Multi-Select
                case 'Multi-Select':  
                    if ( ! empty( $v ) ) {
                        $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = 
                            CRM_Core_BAO_CustomOption::VALUE_SEPERATOR.implode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $v).CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
                    } else {
                        $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = null;
                    }
                    break;

                case 'Select Date':
                    $date = CRM_Utils_Date::format( $v );
                    $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = $date;
                    break;
         
                case 'File':
                    //store the file in d/b
                    $entityId   = explode( '=', $groupTree['info']['where'][0] );
                    $fileParams = array( 'uri'        =>  $filename,
                                         'mime_type'  => $_FILES['custom_' . $fieldId]['type'],
                                         'upload_date'=> date('Ymdhis') );
                    
                    if ( $groupTree[$groupId]['fields'][$fieldId]['customValue']['fid'] ) {
                        $fileParams['id'] = $groupTree[$groupId]['fields'][$fieldId]['customValue']['fid'];
                    }     
                    require_once 'CRM/Core/BAO/File.php';
                    CRM_Core_BAO_File::filePostProcess($v, 
                                                       $groupTree[$groupId]['fields'][$fieldId]['customValue']['fid'], 
                                                       $groupTree[$groupId]['table_name'],
                                                       $entityId[1],
                                                       false,
                                                       true,
                                                       $fileParams,
                                                       'custom_' . $fieldId
                                                       );
                    $defaults   = array( );
                    $paramsFile =  array( 'entity_table' => $groupTree[$groupId]['table_name'],
                                          'entity_id'    => $entityId[1] );
                    
                    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_EntityFile',
                                                 $paramsFile,
                                                 $defaults);
                    
                    $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = $defaults['file_id'];
                    break;
                    
                default:
                    $groupTree[$groupId]['fields'][$fieldId]['customValue']['data'] = $v;
                    break;
                }
            }
        }
    }

    /**
     * generic function to build all the form elements for a specific group tree
     *
     * @param CRM_Core_Form $form      the form object
     * @param array         $groupTree the group tree object
     * @param string        $showName  
     * @param string        $hideName
     *
     * @return void
     * @access public
     * @static
     */
    static function buildQuickForm( &$form,
                                    &$groupTree,
                                    $showName = 'showBlocks',
                                    $hideName = 'hideBlocks',
                                    $inactiveNeeded = false,
                                    $alwaysShow = false ) {
        require_once 'CRM/Core/BAO/CustomField.php';
        require_once 'CRM/Core/BAO/CustomOption.php';

        //this is fix for calendar for date field
        foreach ($groupTree as $key1 => $group) { 
            if ( $key1 === 'info' ) {
                continue;
            }

            foreach ($group['fields'] as $key2 => $field) {
                if ($field['data_type'] == 'Date' && $field['date_parts'] ) {
                    $datePart = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR , $field['date_parts']);
                    $datePart = array_flip( $datePart);
                    
                    if (( !array_key_exists( 'M', $datePart))&&
                        ( !array_key_exists( 'd', $datePart))&&
                        ( !array_key_exists( 'Y', $datePart))) {
                        $groupTree[$key1]['fields'][$key2]['skip_calendar'] = true;
                    }
                    if (array_key_exists( 'H', $datePart)){
                        $groupTree[$key1]['fields'][$key2]['skip_ampm'] = true; 
                    }
                }
            }
                
        }
    
        
        $form->assign_by_ref( 'groupTree', $groupTree );
        $sBlocks = array( );
        $hBlocks = array( );

        // this is fix for date field
        $form->assign('currentYear',date('Y'));
       
        require_once 'CRM/Core/ShowHideBlocks.php'; 
        foreach ($groupTree as $id => $group) { 
            if ( $id === 'info' ) {
                continue;
            }

            CRM_Core_ShowHideBlocks::links( $form, $group['title'], '', ''); 
                 
            $groupId = $group['id']; 
            foreach ($group['fields'] as $field) { 
                $required = $field['is_required'];
                //fix for CRM-1620
                if ( $field['data_type']  == 'File') {
                    if ( isset($field['customValue']['data']) ) {
                        $required = 0;
                    }
                }

                $fieldId = $field['id'];                 
                $elementName = 'custom_' . $fieldId;
                require_once "CRM/Core/BAO/CustomField.php";
                CRM_Core_BAO_CustomField::addQuickFormElement($form, $elementName, $fieldId, $inactiveNeeded, $required); 
            } 
 
            if ( $group['collapse_display'] && ! $alwaysShow ) { 
                $sBlocks[] = "'". $group['name'] . "_show'" ; 
                $hBlocks[] = "'". $group['name'] ."'"; 
            } else { 
                $hBlocks[] = "'". $group['name'] . "_show'" ; 
                $sBlocks[] = "'". $group['name'] ."'"; 
            } 
        } 
             
        $showBlocks = implode(",",$sBlocks); 
        $hideBlocks = implode(",",$hBlocks); 
        $form->assign( $showName, $showBlocks ); 
        $form->assign( $hideName, $hideBlocks ); 
    }

    /**
     * generic function to build all the view form elements for a specific group tree
     *
     * @param CRM_Core_Form $page      the page object
     * @param array         $groupTree the group tree object
     * @param string        $viewName  what name should all the values be stored under
     * @param string        $showName  
     * @param string        $hideName
     *
     * @return void
     * @access public
     * @static
     */
    static function buildViewHTML( &$page, &$groupTree,
                                   $viewName = 'viewForm',
                                   $showName = 'showBlocks1',
                                   $hideName = 'hideBlocks1' ) {
        //showhide blocks for Custom Fields inline
        $sBlocks = array();
        $hBlocks = array();
        $form = array();

        foreach ($groupTree as $key => $group) {
            if ( $key === 'info' ) {
                continue;
            }

            $groupId = $group['id'];
            foreach ($group['fields'] as $field) {
                $fieldId = $field['id'];                
                $elementName = 'custom_' . $fieldId;
                $form[$elementName] = array( );
                $form[$elementName]['name'] = $elementName;
                $form[$elementName]['html'] = null;
                
                if ( $field['data_type'] == 'String' ||
                     $field['data_type'] == 'Int' ||
                     $field['data_type'] == 'Float' ||
                     $field['data_type'] == 'Money') {

                    //added check for Multi-Select in the below if-statement
                    if ( $field['html_type'] == 'Radio'    ||
                         $field['html_type'] == 'CheckBox' ||
                         $field['html_type'] == 'Multi-Select' ) {
                        $freezeString =  "";
                        $freezeStringChecked = "";
                        $customData = array();

                        if ( isset( $field['customValue'] ) ) {
                            //added check for Multi-Select in the below if-statement
                            if ( $field['html_type'] == 'CheckBox' || $field['html_type'] == 'Multi-Select') {
                                $customData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $field['customValue']['data']);
                            } else {
                                $customData[] = $field['customValue']['data'];
                            }
                        }
                        
                        $query = "
SELECT   v.label as label, v.value as value
  FROM   civicrm_option_value v,
         civicrm_option_group g
 WHERE   v.option_group_id = g.id
   AND   g.id = %1
ORDER BY weight ASC, label ASC";
                        $params = array( 1 => array( $field['option_group_id'], 'Integer' ) );
                        $coDAO  = CRM_Core_DAO::executeQuery( $query, $params );

                        $counter = 1;
                        while($coDAO->fetch()) {
                            //to show only values that are checked
                           if(in_array($coDAO->value, $customData)){
                               $checked = in_array($coDAO->value, $customData) ? $freezeStringChecked : $freezeString;
                               if ($counter!=1) {
                                   $form[$elementName]['html'] .= $checked .",&nbsp;".$coDAO->label;
                               } else {
                                   $form[$elementName]['html'] .= $checked .$coDAO->label;
                               }
                               $form[$elementName][$counter]['html'] = $checked .$coDAO->label."\n";
                               $counter++;
                           }
                        }
                    } else {
                        if ( $field['html_type'] == 'Select' ) {
                            $query = "
SELECT   v.label as label, v.value as value
  FROM   civicrm_option_value v,
         civicrm_option_group g
 WHERE   v.option_group_id = g.id
   AND   g.id = %1
ORDER BY weight ASC, label ASC";
                            $params = array( 1 => array( $field['option_group_id'], 'Integer' ) );
                            $coDAO  = CRM_Core_DAO::executeQuery( $query, $params );

                            while($coDAO->fetch()) {
                                if ( isset( $field['customValue'] ) &&
                                     $coDAO->value == $field['customValue']['data'] ) {
                                    $form[$elementName]['html'] = $coDAO->label;
                                }
                            }
                        } else {
                            if ($field['data_type'] == "Float") {
                                $form[$elementName]['html'] = (float)$field['customValue']['data'];
                            } else {
                                if ( isset ($field['customValue']['data'] ) ) {
                                    $form[$elementName]['html'] = $field['customValue']['data'];
                                }
                            }
                        }
                    }
                } else {
                    if ( isset($field['customValue']['data']) ) {
                        switch ($field['data_type']) {
                            
                        case 'Boolean':
                            $freezeString = "";
                            $freezeStringChecked = "";
                            if ( isset($field['customValue']['data']) ) {
                                if ( $field['customValue']['data'] == '1' ) {
                                    $form[$elementName]['html'] = $freezeStringChecked."Yes\n";
                                } else {
                                    $form[$elementName]['html'] = $freezeStringChecked."No\n";
                                }
                            } else {
                                $form[$elementName]['html'] = "\n";
                            }                        
                            break;
                            
                        case 'StateProvince':
                            $form[$elementName]['html'] = CRM_Core_PseudoConstant::stateProvince( $field['customValue']['data'] );
                            break;
                            
                        case 'Country':
                            $form[$elementName]['html'] = CRM_Core_PseudoConstant::country( $field['customValue']['data'] );
                            break;
                            
                        case 'Date':
                            $parts = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $field['date_parts']);
                            $form[$elementName]['html'] = CRM_Utils_Date::customFormat( $field['customValue']['data'], null, $parts);
                            break;

                        case 'Link':
                            $form[$elementName]['html'] =
                                CRM_Utils_System::formatWikiURL( $field['customValue']['data'] );
                            break;

                        default:
                            $form[$elementName]['html'] = $field['customValue']['data'];
                        }                    
                    }
                }
            }

            //showhide group
            if ( $group['collapse_display'] ) {
                $sBlocks[] = "'". $group['name'] . "_show'" ;
                $hBlocks[] = "'". $group['name'] ."'";
            } else {
                $hBlocks[] = "'". $group['name'] . "_show'" ;
                $sBlocks[] = "'". $group['name'] ."'";
            }
        }
        
        $showBlocks = implode(",",$sBlocks);
        $hideBlocks = implode(",",$hBlocks);
        
        $page->assign( $viewName, $form );
        $page->assign( $showName, $showBlocks );
        $page->assign( $hideName, $hideBlocks );

        $page->assign_by_ref('groupTree', $groupTree);
    }

    /**
     * Function to extract the get params from the url, validate
     * and store it in session
     *
     * @param CRM_Core_Form $form the form object
     * @param string        $type the type of custom group we are using

     * @return void
     * @access public
     * @static
     */
    static function extractGetParams( &$form, $type ) {
        // if not GET params return
        if ( empty( $_GET ) ) {
            return;
        }
        
        $groupTree    =& CRM_Core_BAO_CustomGroup::getTree( $type );
        $customFields =& CRM_Core_BAO_CustomField::getFields( $type );

        $customValue  = array();
        $htmlType     = array('CheckBox','Multi-Select','Select','Radio');

        foreach ($groupTree as $group) {
            if ( ! isset( $group['fields'] ) ) {
                continue;
            }
            foreach( $group['fields'] as $key => $field) {
                $fieldName = 'custom_' . $key;
                $value = CRM_Utils_Request::retrieve( $fieldName, 'String',
                                                      $form );

                if ( $value ) {
                    if ( ! in_array( $customFields[$key][3], $htmlType ) ||
                         $customFields[$key][2] =='Boolean' ) {
                        $valid = CRM_Core_BAO_CustomValue::typecheck( $customFields[$key][2], $value);
                    }
                    if ( $customFields[$key][3] == 'CheckBox' ||
                         $customFields[$key][3] =='Multi-Select' ) {
                        $value = str_replace("|",",",$value);
                        $mulValues = explode( ',' , $value );
                        $customOption = CRM_Core_BAO_CustomOption::getCustomOption( $key, true );
                        $val = array (); 
                        foreach( $mulValues as $v1 ) {
                            foreach( $customOption as $v2 ) {
                                if ( strtolower(trim($v2['label'])) == strtolower(trim($v1)) ) {
                                    $val[$v2['value']] = 1;
                                }
                            }
                        }
                        if (! empty ($val) ) {
                            $value = $val;
                            $valid = true; 
                        } else {
                            $value = null;
                        }
                    } else if ($customFields[$key][3] == 'Select' || 
                               ( $customFields[$key][3] =='Radio' &&
                                 $customFields[$key][2] !='Boolean' ) ) {
                        $customOption = CRM_Core_BAO_CustomOption::getCustomOption($key, true );
                        foreach( $customOption as $v2 ) {
                            if ( strtolower(trim($v2['label'])) == strtolower(trim($value)) ) {
                                $value = $v2['value'];
                                $valid = true; 
                            }
                        }
                    } else if ( $customFields[$key][2] == 'Date' ) {
                        require_once 'CRM/Utils/Date.php';
                        if( is_numeric( $value ) ){
                            $value = CRM_Utils_Date::unformat( $value , null );
                        } else {
                            $value = CRM_Utils_Date::unformat( $value , $separator = '-' );
                        }
                        $valid = true; 
                    }
                    if ( $valid ) {
                        $customValue[$fieldName] = $value;
                    }
                }
            }
        }

        $form->set( 'customGetValues', $customValue );
        $form->set( 'groupTree', $groupTree );
    }

    /**
     * Function to check the type of custom field type (eg: Used for Individual, Contribution, etc) 
     * this function is used to get the custom fields of a type (eg: Used for Individual, Contribution, etc )
     *
     * @param  int     $customFieldId          custom field id
     * @param  array   $removeCustomFieldTypes remove custom fields of a type eg: array("Individual") ;
     *
     *
     * @return boolean false if it matches else true      
     * @static
     * @access public
     */
    static function checkCustomField($customFieldId, &$removeCustomFieldTypes ) 
    {
        $query = "SELECT cg.extends as extends
                  FROM civicrm_custom_group as cg, civicrm_custom_field as cf
                  WHERE cg.id = cf.custom_group_id
                    AND cf.id =" . CRM_Utils_Type::escape($customFieldId, 'Integer');

        $extends = CRM_Core_DAO::singleValueQuery( $query, CRM_Core_DAO::$_nullArray );
        
        if ( in_array( $extends, $removeCustomFieldTypes ) ) {
            return false;
        }
        return true;
    }

    static function mapTableName( $table ) {
        switch ( $table ) {
        case 'Contact':
        case 'Individual':
        case 'Household':
        case 'Organization':
            return 'civicrm_contact';

        case 'Activity':
            return 'civicrm_activity';

        case 'Group':
            return 'civicrm_group';

        case 'Contribution':
            return 'civicrm_contribution';
            
        case 'Relationship':
            return 'civicrm_relationship';
            
        case 'Event':
            return 'civicrm_event';
        
        case 'Membership':
            return 'civicrm_membership';
        
        case 'Participant':
            return 'civicrm_participant';
            
        case 'Grant':
            return 'civicrm_grant';
            
        default:
            CRM_Core_Error::fatal( );
        }
    }

    static function createTable( $group ) {
        $params = array(
                        'name'           => $group->table_name,
                        'extends_name'   => self::mapTableName( $group->extends ),
                        );

        require_once 'CRM/Core/BAO/CustomField.php';
        $tableParams =& CRM_Core_BAO_CustomField::defaultCustomTableSchema( $params );

        require_once 'CRM/Core/BAO/SchemaHandler.php';
        CRM_Core_BAO_SchemaHandler::createTable( $tableParams );
    }

}

?>
