<?php

require_once 'CRM/DAO/Base.php';

class CRM_Contact_DAO_Relationship extends CRM_DAO_Base {
  public $contact_id;
  public $target_contact_id;
  public $relationship_type_id;

  function __construct() {
    parent::__construct();
  }

  function dbFields() {
    static $fields;
    if ( $fields === null ) {
      $fields = array_merge(
			    parent::dbFields(),
			    array(
				  'contact_id'           => array(CRM_Type::T_INT, self::NOT_NULL),
				  'target_contact_id'    => array(CRM_Type::T_INT, self::NOT_NULL),
				  'relationship_type_id' => array(CRM_Type::T_INT, self::NOT_NULL),
				  ) // end of array
			    );
    }
    return $fields;
  } // end of method dbFields


  function links() {
    static $links;
    if ( $links === null ) {
      $links = array_merge(parent::links(),
			   array('contact_id'           => 'crm_contact:id',
				 'target_contact_id'    => 'crm_contact:id',
				 'relationship_type_id' => 'crm_relationship_type:id',
				 )
			   );
    }
    return $links;
  } // end of method links()

} // end of class CRM_Contact_DAO_Relationship

?>
