<?php

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Event/Cart/BAO/Cart.php';
require_once 'CRM/Event/Cart/Form/Cart.php';
require_once 'CRM/Price/BAO/Set.php';
      
class CRM_Event_Cart_Form_Checkout_ParticipantsAndPrices extends CRM_Event_Cart_Form_Cart
{
  public $price_fields_for_event;

  function buildQuickForm( )
  {
    $this->price_fields_for_event = array();
    require_once('CRM/Event/Cart/Form/MerParticipant.php');
    foreach ( $this->cart->get_main_event_participants( ) as $participant )
    {
      $form = new CRM_Event_Cart_Form_MerParticipant($participant);
      $form->buildQuickForm($this);
    }
    foreach ($this->cart->get_main_events_in_carts() as $event_in_cart)
    {
      $this->price_fields_for_event[$event_in_cart->event_id] = $this->build_price_options($event_in_cart->event);
    }
    $this->addElement('text', 'discountcode', ts('If you have a discount code, enter it here'));
    $this->assign( 'events_in_carts', $this->cart->get_main_events_in_carts() );
    $this->assign( 'price_fields_for_event', $this->price_fields_for_event );
    $this->addButtons( 
      array ( 
      array ( 'type' => 'upload',
      'name' => ts('Continue >>'),
      'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
      'isDefault' => true )
      )
    );
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

  function build_price_options($event)
  {
    $price_fields_for_event = array();
    $base_field_name = "event_{$event->id}_amount";
    $price_set_id = CRM_Price_BAO_Set::getFor( "civicrm_event", $event->id );
    if ( $price_set_id === false && $event->is_monetary) {
      require_once 'CRM/Utils/Money.php';
      //$fee_data = array();
      CRM_Core_OptionGroup::getAssoc( "civicrm_event.amount.{$event->id}", $fee_data, true );
      $choices = array();
      foreach ( $fee_data as $fee ) {
        if ( is_array( $fee ) ) {
          $choices[] = $this->createElement( 'radio', null, '', CRM_Utils_Money::format( $fee['value']) . ' ' . $fee['label'], $fee['amount_id'] );
        }
      }
      $this->addGroup( $choices, $base_field_name, t("Price Levels"));
      $this->addRule($base_field_name, ts("Select at least one option from Price Levels"), 'required');
      $price_fields_for_event[] = $base_field_name;
    } elseif ($price_set_id) {
      $price_sets = CRM_Price_BAO_Set::getSetDetail( $price_set_id, true );
      $price_set = $price_sets[$price_set_id];
      $index = -1;
      foreach ( $price_set['fields'] as $field ) {
        $index++;
        $field_name = "event_{$event->id}_price_{$field['id']}";
        CRM_Price_BAO_Field::addQuickFormElement( $this, $field_name, $field['id'] );
        $price_fields_for_event[] = $field_name;
      }
    }
    return $price_fields_for_event;
  }

  function validate()
  {
    parent::validate();
    if ($this->_errors)
        return false;
    $this->cart->load_associations( );
    $fields = $this->_submitValues;

    foreach ( $this->cart->get_main_events_in_carts( ) as $event_in_cart ) {
      $price_set_id = CRM_Event_BAO_Event::usesPriceSet( $event_in_cart->event_id );
      if ( $price_set_id ) {
        $priceField = new CRM_Price_DAO_Field( );
        $priceField->price_set_id = $price_set_id;
        $priceField->find( );
        
        $check = array( );
        
        while ( $priceField->fetch( ) ) {
          if ( ! empty( $fields["event_{$event_in_cart->event_id}_price_{$priceField->id}"] ) ) {
            $check[] = $priceField->id; 
          }
        }
        
        //XXX
        if ( empty( $check ) ) {
          $this->_errors['_qf_default'] = ts( "Select at least one option from Price Levels." );
        }

        $lineItem = array( );
        if ( is_array( $this->_values['fee']['fields'] ) ) {
          CRM_Price_BAO_Set::processAmount( $this->_values['fee']['fields'], $fields, $lineItem );
          //XXX total...
          if ($fields['amount'] < 0) {
          $this->_errors['_qf_default'] = ts( "Price Levels can not be less than zero. Please select the options accordingly" );
          }
        }
      }
      
      foreach ( $event_in_cart->participants as $mer_participant ) {
        $contact = self::matchAnyContactOnEmail( $mer_participant->email );
        if ($contact != null) {
          require_once('CRM/Event/BAO/Participant.php');
          $participant = new CRM_Event_BAO_Participant();
          $participant->event_id = $event_in_cart->event_id;
          $participant->contact_id = $mer_participant->contact_id;
          $num_found = $participant->find();
          if ($num_found > 0)
          {
            require_once('CRM/Event/Cart/Form/MerParticipant.php');
            $participant->fetch();
            $participant_form = CRM_Event_Cart_Form_MerParticipant::get_form($participant);
            $this->_errors[$participant_form->html_field_name('email')] = "The participant {$mer_participant->email} is already registered for {$event_in_cart->event->title} ({$event_in_cart->event->start_date}).";
          }
        }
      }
    }
    return empty( $this->_errors ) ? true : $this->_errors;
  }

  public function setDefaultValues( )
  {
    $this->loadCart();

    $defaults = array();
    require_once 'CRM/Event/Cart/Form/MerParticipant.php';
    foreach ( $this->cart->get_main_event_participants() as $participant )
    {
        if ($participant->contact_id == self::getContactID()
          && empty($participant->email)
          && !CRM_Event_Cart_Form_Cart::is_administrator()
          && ($participant->get_participant_index() == 1))
        {
          require_once 'CRM/Contact/BAO/Contact.php';
          $defaults = array( );
          $params = array( 'id' => self::getContactID() );
          $contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults );
          $participant->email = self::primary_email_from_contact( $contact );
          $participant->first_name = $contact->first_name;
          $participant->last_name = $contact->last_name;
        }
        $form = $participant->get_form();
        $defaults += $form->setDefaultValues();
    }
    return $defaults;
  }

  function preProcess( )
  {
    //TODO decouple
    if ( $this->getContactID() === NULL ) {
      CRM_Core_Session::setStatus( ts( "You must log in or create an account to register for events." ) );
      return CRM_Utils_System::redirect( "/user?destination=civicrm/event/cart_checkout&reset=1" );
    }
    else {
      parent::preProcess( );
      $this->load_form_values();
    }
  }

  function load_form_values()
  {
    if (!array_key_exists('event', $this->_submitValues)) return;
    foreach ( $this->_submitValues['event'] as $event_id => $participants ) {
      foreach ($participants['participant'] as $participant_id => $fields) {
	require_once 'CRM/Contact/BAO/Contact.php';
        $contact_id = self::find_or_create_contact($fields['email']);

        $participant = $this->cart->get_event_in_cart_by_event_id($event_id)->get_participant_by_id($participant_id);
        if ($participant->contact_id && $contact_id != $participant->contact_id)
        {
          foreach ($this->cart->get_subparticipants($participant) as $subparticipant) {
            $subparticipant->contact_id = $contact_id;
            $subparticipant->save();
          }
        }

        //TODO security check that participant ids are already in this cart
        $participant_params = array
        (
          'id' => $participant_id,
          'cart_id' => $this->cart->id,
          'event_id' => $event_id,
          'contact_id' => $contact_id,
          //'registered_by_id' => $this->cart->user_id,
          'email' => $fields['email'],
          'first_name' => $fields['first_name'],
          'last_name' => $fields['last_name'],
        );
        $participant = new CRM_Event_Cart_BAO_MerParticipant($participant_params);
        $participant->store_temporary_name();
        $participant->save();
        $this->cart->add_participant_to_cart($participant);

        if (array_key_exists('field', $this->_submitValues)) {
          $custom_fields = array_merge($participant->get_form()->get_participant_custom_data_fields());

          CRM_Contact_BAO_Contact::createProfileContact($this->_submitValues['field'][$participant_id], $custom_fields, $contact->contact_id);
        }
      }
    }
    $this->cart->save();
  }

  static function find_or_create_contact($email)
  {
    $contact = self::matchAnyContactOnEmail( $email );
    if ($contact == null) {
      require_once 'CRM/Contact/BAO/Group.php';

      //XXX
      $params = array( 'name' => 'RegisteredByOther' );
      $values = array( );
      $group = CRM_Contact_BAO_Group::retrieve( $params, $values );
      $add_to_groups = array( );
      if ( $group != null ) {
        $add_to_groups[] = $group->id;
      }
      // still add the employer id of the signed in user  //???
      $contact_params = array(
        'email-Primary' => $fields['email'],
        'first_name' => $fields['first_name'],
        'last_name' => $fields['last_name'],
        'is_deleted' => true,
      );
      $no_fields = array( );
      $contact_id = CRM_Contact_BAO_Contact::createProfileContact( $contact_params, $no_fields, null, $add_to_groups );
      if (!$contact_id) {
        CRM_Core_Error::displaySessionError("Could not create or match that a contact with that email address.  Please contact the webmaster.");
      }
      return $contact_id;
    }
    else {
      return $contact->contact_id;
    }
  }

  static function &matchAnyContactOnEmail($mail) 
  {
     $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
     $mail = $strtolower( trim( $mail ) );

     $query = " 
SELECT     contact_id
FROM       civicrm_email
WHERE      email = %1";
     $p = array( 1 => array( $mail, 'String' ) );
     $query .= " ORDER BY is_primary DESC";
     
     $dao =& CRM_Core_DAO::executeQuery( $query, $p );

     if ( $dao->fetch() ) {
        return $dao;
     }
     require_once('CRM/Contact/BAO/Contact.php');
     return CRM_Contact_BAO_Contact::matchContactOnEmail($mail);
  }
}
