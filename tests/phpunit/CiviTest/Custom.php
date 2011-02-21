<?php


class Custom extends CiviUnitTestCase
{
    /*
     * Helper function to create Custom Group
     *
     * @return object of created group
     */ 
    function createGroup( $group, $extends =  null, $isMultiple = false ) 
    {
        if ( empty( $group ) ) {
            if ( isset( $extends ) &&
                 ! is_array( $extends ) ) {
                $extends = array( $extends );
            }
            $group = array(
                           'title'       => 'Test_Group',
                           'name'        => 'test_group',
                           'extends'     => $extends,
                           'style'       => 'Inline',
                           'is_multiple' => $isMultiple,
                           'is_active'   => 1
                           );
            
        } else {
            // this is done for backward compatibility
            // with tests older than 3.2.3
            if ( isset( $group['extends'] ) &&
                 ! is_array( $group['extends'] ) ) {
                $group['extends'] = array( $group['extends'] );
            }
        }

        $result = civicrm_api('custom_group', 'create', $group );

        if ( $result['is_error'] ) {
            return null;
        }

        // this is done for backward compatibility
        // with tests older than 3.2.3
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $group = new CRM_Core_BAO_CustomGroup( );
        $group->id = $result['id'];
        $group->find( true );




        return $group;
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
        
        $result = civicrm_api('custom_field', 'create', $params );

        if ( $result['is_error'] ) {
            return null;
        }

        // this is done for backward compatibility
        // with tests older than 3.2.3
        $customField = new CRM_Core_DAO_CustomField();
        $customField->id = $result['result']['customFieldId'];
        $customField->find( true );

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
        $deleteCustomGroup = CRM_Core_BAO_CustomGroup::deleteGroup( $params, true );
        return $deleteCustomGroup;
    }
}
?>
