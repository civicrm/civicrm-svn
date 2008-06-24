<?php
class Custom extends DrupalTestCase 
{
    /*
     * Helper function to create Custom Group
     *
     * @return object of created group
     */ 
    function createGroup( $group, $extends ) {
        if ( empty( $group ) ) {
            $group = array(
                           'title'      => 'Test_Group',
                           'name'       => 'test_group',
                           'extends'    => $extends,
                           'style'      => 'Inline',
                           'is_active'  => 1
                           );
        }
        require_once 'CRM/Core/BAO/CustomGroup.php';
        
        $customGroupBAO =& new CRM_Core_BAO_CustomGroup();
        $customGroupBAO->copyValues( $group );
        $customGroup = $customGroupBAO->save();
        $customGroup->table_name = 'civicrm_value_test_group_'.$customGroup->id;
        $customGroup = $customGroupBAO->save();
        $customTable = CRM_Core_BAO_CustomGroup::createTable( $customGroup );
        
        return $customGroup;
    }
    
    /*
     * Helper function to create Custom Field
     *
     * @return object of created field
     */ 
    function createField( $params, $fields = null ) {
        if ( empty( $params ) ){
            $params = array(
                            'custom_group_id' => $fields['groupId'],
                            'label'           => 'test_' . $fields['dataType'],
                            'html_type'       => $fields['htmlType'],
                            'data_type'       => $fields['dataType'],
                            'weight'          => 4,
                            'is_required'     => 1,
                            'is_searchable'   => 0,
                            'is_active'       => 1
                            );
        }
        $customFieldBAO =& new CRM_Core_BAO_CustomField();
        $customFieldBAO->copyValues( $params );
        $customField = $customFieldBAO->save();
        $customFieldBAO->column_name = 'test_'. $fields['dataType'] . '_'.$customField->id;
        $customFieldObject =  $customFieldBAO;
        $customField = $customFieldBAO->save();
        
        require_once 'CRM/Core/BAO/CustomField.php';
        $createField = CRM_Core_BAO_CustomField::createField( $customFieldObject, 'add' );
        
        return $customField;
    }
    
    /*
     * Helper function to delete custom field
     * 
     * @param  object of Custom Field to delete
     * 
     */
    function deleteField( $params ) {
        require_once 'CRM/Core/BAO/CustomField.php';
        CRM_Core_BAO_CustomField::deleteField( $params);
    }
    
    /*
     * Helper function to delete custom group
     * 
     * @param  object Custom Group to delete
     * @return boolean true if Group deleted, false otherwise
     * 
     */
    function deleteGroup( $params ) {
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $deleteCustomGroup = CRM_Core_BAO_CustomGroup::deleteGroup( $params );
        return $deleteCustomGroup;
    }
}
?>
