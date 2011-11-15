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

  function loadCart( )
  {
	if ( $this->event_cart_id == null ) {
	  $this->cart = CRM_Event_Cart_BAO_Cart::find_or_create_for_current_session( );
	} else {
	  $this->cart = CRM_Event_Cart_BAO_Cart::find_by_id( $this->event_cart_id );
	}
        $this->cart->load_associations( );
        $this->stub_out_and_inherit( );
  }

  function stub_out_and_inherit( )
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

  static function is_administrator()
  {
        global $user;
  	return CRM_Core_Permission::check( 'administer CiviCRM' );
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
