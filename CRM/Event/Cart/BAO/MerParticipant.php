<?php

require_once('CRM/Event/BAO/Participant.php');

class CRM_Event_Cart_BAO_MerParticipant extends CRM_Event_BAO_Participant
{
  public $email = null;
  public $first_name = null;
  public $last_name = null;

    //XXX
  function __construct($participant = null)
  {
    parent::__construct();
    $a = (array)$participant;
    $this->copyValues($a);
  }

  public static function create( $params )
  {
        $participantParams = array
        (
            'id'                => CRM_Utils_Array::value('id', $params),
            'role_id'           => self::get_attendee_role_id(),
            'status_id'         => self::get_pending_in_cart_status_id(),
            'contact_id'        => $params['contact_id'],
            'event_id'          => $params['event_id'],
            'cart_id'           => $params['cart_id'],
            //XXX
            //'registered_by_id'  =>
            //'discount_amount'   =>
            //'fee_level'         => $params['fee_level'],
        );
        $participant = CRM_Event_BAO_Participant::create($participantParams);

	if ( is_a( $participant, 'CRM_Core_Error') ) {
	  CRM_Core_Error::fatal( ts( 'There was an error creating a cart participant') );
	}

	$mer_participant = new CRM_Event_Cart_BAO_MerParticipant($participant);
        //XXX okay this is a problem, we are trying to avoid creating a contact before the participant has confirmed.
        $mer_participant->email = CRM_Utils_Array::value('email', $params);
        $mer_participant->first_name = CRM_Utils_Array::value('first_name', $params);
        $mer_participant->last_name = CRM_Utils_Array::value('last_name', $params);

        return $mer_participant;
  }

  static function get_attendee_role_id()
  {
    $roles = CRM_Event_PseudoConstant::participantRole(null, "v.label='Attendee'");
    return array_pop(array_keys($roles));
  }

  static function get_pending_in_cart_status_id()
  {
    $status_types = CRM_Event_PseudoConstant::participantStatus(null, "name='Pending in cart'");
    return array_pop(array_keys($status_types));
  }

  public static function find_all_by_cart_id( $event_cart_id )
  {
        if ($event_cart_id == null)
            return null;
        return self::find_all_by_params( array( 'cart_id' => $event_cart_id ) );
  }

  public static function find_all_by_event_and_cart_id( $event_id, $event_cart_id )
  {
        if ($event_cart_id == null)
            return null;
        return self::find_all_by_params( array( 'event_id' => $event_id, 'cart_id' => $event_cart_id ) );
  }

  public static function find_all_by_params( $params )
  {
        $participant = new CRM_Event_BAO_Participant( );
        $participant->copyValues( $params );
        $result = array();
        if ( $participant->find( ) ) {
          while ( $participant->fetch( ) ) {
                $result[] = new CRM_Event_Cart_BAO_MerParticipant(clone( $participant ));
          }
        }
        return $result;
  }


  function load_associations( )
  {
      //XXX
      $this->load_temporary_contact();
      require_once('CRM/Event/Cart/Form/Cart.php');
      if ($this->contact_id && empty($this->email) && !CRM_Event_Cart_Form_Cart::is_administrator())
      {
	  require_once 'CRM/Contact/BAO/Contact.php';
	  $defaults = array( );
	  $params = array( 'id' => $this->contact_id );
	  $contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults );
	  $this->email = self::primary_email_from_contact( $contact );
	  $this->first_name = $contact->first_name;
	  $this->last_name = $contact->last_name;
      }
  }

  static function primary_email_from_contact( $contact )
  {
	foreach ( $contact->email as $email ) {
	  if ( $email['is_primary'] ) {
		return $email['email'];
	  }
	}

	return null;
  }

  static function billing_address_from_contact( $contact )
  {
        foreach ($contact->address as $loc) {
            if ($loc['is_billing']) return $loc;
        }
        foreach ($contact->address as $loc) {
            if ($loc['is_primary']) return $loc;
        }
        return null;
  }

  function custom_data_assign( $custom_id, $value )
  {
    $custom_params = array
    (
      'entityID' => $this->id,
      'custom_'.$custom_id => $value,
    );
    require_once 'CRM/Core/BAO/CustomValueTable.php';
    CRM_Core_BAO_CustomValueTable::setValues( $custom_params );
  }

  function get_form()
  {
    require_once('CRM/Event/Cart/Form/MerParticipant.php');
    return new CRM_Event_Cart_Form_MerParticipant($this);
  }

//TODO figure out a solution for provisional contacts
  function store_temporary_contact()
  {
    $session = CRM_Core_Session::singleton( );
    $cart_contacts = $session->get('cart_contacts');
    if (!isset($cart_contacts)) $cart_contacts = array();

    $cart_contacts[$this->id] = array(
        'email' => $this->email,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
    );
    $session->set('cart_contacts', $cart_contacts);
  }

  function load_temporary_contact()
  {
    $session = CRM_Core_Session::singleton( );
    $cart_contacts = $session->get('cart_contacts');
    if (isset($cart_contacts) && isset($cart_contacts[$this->id]))
    {
      $saved = $cart_contacts[$this->id];
      $this->email = $saved['email'];
      $this->first_name = $saved['first_name'];
      $this->last_name = $saved['last_name'];
    }
  }
}
