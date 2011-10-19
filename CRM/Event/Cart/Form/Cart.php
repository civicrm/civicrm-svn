<?php

require_once('CRM/Core/Form.php');
class CRM_Event_Cart_Form_Cart extends CRM_Core_Form
{
  public $cart;

  public $_action;
  public $contact;
  public $event_cart_id = null;
  public $_mode;
  public $participants;

  public function preProcess()
  {
    $this->_action = CRM_Utils_Request::retrieve( 'action', 'String', $this, false );
    $this->_mode = 'live';
    $this->loadCart( );

    $this->checkWaitingList( );

    $locationTypes = CRM_Core_PseudoConstant::locationType( );
    $this->_bltID = array_search( 'Billing', $locationTypes);
    $this->assign('bltID', $this->_bltID);
  }

  function load_form_values($form_data, $purge_deletes = false)
  {
    if ($purge_deletes) {
        $this->purge_deletes($form_data);
    }
    require_once('CRM/Event/Cart/Form/MerParticipant.php');
    foreach ( $form_data as $key => $value ) {
      $matches = array();
      //TODO let Form_MerParticipant do this parse
      if ( preg_match( '/'.CRM_Event_Cart_Form_MerParticipant::full_field_name( '(\d+)', '(\d+)', 'email' ).'/', $key, $matches ) )
      {
        $participant_params = array
        (
          'id' => $matches[2],
          'cart_id' => $this->cart->id,
          'event_id' => $matches[1],
          //'registered_by_id' => $this->cart->user_id,
          'contact_id' => $this->cart->user_id, // default until payment confirmed
        );
        $participant = CRM_Event_Cart_Form_MerParticipant::load_form_values($participant_params, $form_data);
        $this->cart->add_participant_to_cart($participant);
      }
    }
    $this->cart->save();
  }

  function loadCart( )
  {
	if ( $this->event_cart_id == null ) {
	  $this->cart = CRM_Event_Cart_BAO_Cart::find_or_create_for_current_session( );
	} else {
	  $this->cart = CRM_Event_Cart_BAO_Cart::find_by_id( $this->event_cart_id );
	}
        $this->cart->load_associations( );
        $this->stub_out_empty_events( );
  }

  function stub_out_empty_events( )
  {
	require_once 'CRM/Event/Cart/BAO/MerParticipant.php';
	require_once 'CRM/Core/Transaction.php';
	$transaction = new CRM_Core_Transaction( );

	foreach ( $this->cart->get_main_events_in_carts( ) as $event_in_cart ) {
	  if ( empty($event_in_cart->participants) ) {
		$participant = CRM_Event_Cart_BAO_MerParticipant::create( array(
                      'cart_id' => $this->cart->id,
		      'event_id' => $event_in_cart->event_id,
		      'contact_id' => $this->getContactID( ),
		) );
                $participant->save();
		$event_in_cart->add_participant( $participant );
	  }
          $event_in_cart->save();
	}
	$transaction->commit( );
  }


  // delete any participants and events not appearing in the form data
  function purge_deletes($form_data)
  {
    $main_events = $this->cart->get_main_events_in_carts();
    foreach ($main_events as $main_event)
    {
      $participants = $main_event->participants;
      foreach ( $participants as $participant)
      {
        if ( !array_key_exists( $participant->get_form()->html_field_name('email'), $form_data ) )
        {
          foreach ($this->cart->get_events_in_carts_by_main_event_id($main_event->event_id) as $event_in_cart)
          {
            $event_in_cart->remove_participant_by_contact_id($participant->contact_id);
          }
          $main_event->remove_participant_by_id($participant->id);
          if (empty($main_event->participants)) {
            $this->cart->remove_event_in_cart($main_event->id);
          }
        }
      }
    }
  }

  public function setDefaultValues( )
  {
    $this->loadCart();

    //TODO move into Form/MerParticipant
    require_once 'CRM/Event/Cart/Form/MerParticipant.php';
    foreach ( $this->cart->events_in_carts as $event_in_cart )
    {
      $custom_fields = CRM_Event_Cart_Form_MerParticipant::get_participant_custom_data_fields( $event_in_cart->event_id );

      foreach ( $event_in_cart->participants as $participant )
      {
        $form = $participant->get_form();
        $defaults[$form->html_field_name('email')] = $participant->email;
        $defaults[$form->html_field_name('first_name')] = $participant->first_name;
        $defaults[$form->html_field_name('last_name')] = $participant->last_name;
        foreach ($custom_fields as $custom_id => $field)
        {
            // XXX one day we will get all custom values in one request
            $custom_field_name = "custom_{$custom_id}";
            $custom_field_params = array(
              'entityID' => $participant->id,
              $custom_field_name => 1
            );
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            $values = CRM_Core_BAO_CustomValueTable::getValues($custom_field_params);
            if (isset($values[$custom_field_name])) {
                $defaults[$form->html_field_name($custom_id)] = $values[$custom_field_name];
            }
        }
      }
    }
    return $defaults;
  }

  function checkWaitingList( )
  {
	require_once 'CRM/Event/BAO/Participant.php';
	foreach ( $this->cart->events_in_carts as $event_in_cart )
	{
	  $empty_seats = $this->checkEventCapacity( $event_in_cart->event_id );
	  if ($empty_seats === null) {
		continue;
	  }
	  foreach ( $event_in_cart->participants as $participant ) {
		if ( $empty_seats <= 0 ) {
		  $participant->must_wait = true;
		}
		$empty_seats--;
	  }
	}
  }

  function checkEventCapacity( $event_id )
  {
	require_once 'CRM/Event/BAO/Participant.php';
	$empty_seats = CRM_Event_BAO_Participant::eventFull( $event_id, true );
	if (is_numeric($empty_seats)) {
	    return $empty_seats;
	} if (is_string($empty_seats)) {
	    return 0;
	} else {
	    return null;
	}
  }

  function get_default_participant_contact_id()
  {
        //TODO handle admin mode
  }

  static function is_administrator()
  {
        global $user;
  	return in_array('administrator', array_values($user->roles));
  }

  function getContactID( )
  {
        //XXX when do we query 'cid' ?
	$tempID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );

	// force to ignore the authenticated user /XXX any admin
	if ( $tempID === '0' ) {
	  return;
	}

	//check if this is a checksum authentication
	$userChecksum = CRM_Utils_Request::retrieve( 'cs', 'String', $this );
	if ( $userChecksum ) {
	  //check for anonymous user.
	  require_once 'CRM/Contact/BAO/Contact/Utils.php';
	  $validUser = CRM_Contact_BAO_Contact_Utils::validChecksum( $tempID, $userChecksum );
	  if ( $validUser ) return  $tempID;
	}

	// check if the user is registered and we have a contact ID
	$session = CRM_Core_Session::singleton( );
	return $session->get( 'userID' );
  }

  function getValuesForPage( $page_name )
  {
	$container = $this->controller->container( );
	return $container['values'][$page_name];
  }
}
