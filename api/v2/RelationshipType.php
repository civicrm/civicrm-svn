<?
//require ("api/v2/Relationship.php");

/**
 * Function to update relationship type
 *
 * @param  array $params   Associative array of property name/value pairs to update the relationship type.
 *
 * @return array Array with relationship type information
 *
 * @access public
 *
 * @todo Requires some work
 */
function civicrm_relationship_type_update( $params ) {
    return civicrm_relationship_type_add( $params );
}

/**
 * Function to create relationship type
 *
 * @param  array $params   Associative array of property name/value pairs to insert in new relationship type.
 *
 * @return Newly created Relationship_type object
 *
 * @access public
 *
 */
function civicrm_relationship_type_add( $params ) {
    
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Parameter is not an array' ) );
    }

    if( ! isset( $params['contact_types_a'] ) &&
        ! isset( $params['contact_types_b'] ) && 
        ! isset( $params['name_a_b'] ) &&
        ! isset( $params['name_b_a'] )) { 
        
        return civicrm_create_error( ts('Missing some required parameters (contact_types_a contact_types_b name_a_b name b_a)'));
    }

    if (! isset( $params['label_a_b']) ) 
      $params['label_a_b'] = $params['name_a_b'];

    if (! isset( $params['label_b_a']) ) 
      $params['label_b_a'] = $params['name_b_a'];

    require_once 'CRM/Utils/Rule.php';
/**
 * Function to get all relationship type
 * retruns  An array of Relationship_type
 * * @access  public
 */
function civicrm_relationship_types_get( $params = null ) 
{
    _civicrm_initialize();
    require_once 'CRM/Contact/DAO/RelationshipType.php';
    $relationshipTypes = array();
    $relationshipType  = array();
    $relationType      = new CRM_Contact_DAO_RelationshipType();
    if ( !empty( $params ) && is_array( $params ) ) {
        $properties = array_keys( $relationType->fields() );
        foreach ($properties as $name) {
            if ( array_key_exists( $name, $params ) ) {
                $relationType->$name = $params[$name];
            }
        }
    }
    $relationType->find();
    while( $relationType->fetch() ) {
        _civicrm_object_to_array( clone($relationType), $relationshipType );
        $relationshipTypes[] = $relationshipType; 
    }
    return $relationshipTypes;
}



