<?php

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Event/Cart/BAO/Cart.php';
require_once 'CRM/Event/Cart/Form/Checkout.php';
require_once 'CRM/Price/BAO/Set.php';
      
class CRM_Event_Cart_Form_Checkout_ParticipantsAndPrices extends CRM_Event_Cart_Form_Checkout
{
  public $price_fields_for_event;

  function buildQuickForm( )
  {
    $this->price_fields_for_event = array();
    foreach ( $this->cart->get_main_events_in_carts( ) as $event_in_cart ) {
      foreach ( $event_in_cart->participants as $participant ) {
        require_once('CRM/Event/Cart/Form/MerParticipant.php');
        CRM_Event_Cart_Form_MerParticipant::get_form($participant)->load_fields( $this );
      }
      $this->price_fields_for_event[$event_in_cart->event_id] = array();
      $base_field_name = "event_{$event_in_cart->event_id}_amount";
      $price_set_id = CRM_Price_BAO_Set::getFor( "civicrm_event", $event_in_cart->event_id );
      if ( $price_set_id === false ) {
        require_once 'CRM/Utils/Money.php';
        CRM_Core_OptionGroup::getAssoc( "civicrm_event.amount.{$event_in_cart->event_id}", $fee_data, true );
        $choices = array();
        foreach ( $fee_data as $fee ) {
          if ( is_array( $fee ) ) {
            $choices[] = $this->createElement( 'radio', null, '', CRM_Utils_Money::format( $fee['value']) . ' ' . $fee['label'], $fee['amount_id'] );
          }
        }
        $this->addGroup( $choices, $base_field_name, "");
        $this->price_fields_for_event[$event_in_cart->event_id][] = $base_field_name;
      } else {
        $price_sets = CRM_Price_BAO_Set::getSetDetail( $price_set_id, true );
        $price_set = $price_sets[$price_set_id];
        $index = -1;
        foreach ( $price_set['fields'] as $field ) {
          $index++;
          $field_name = "event_{$event_in_cart->event_id}_price_{$field['id']}";
          CRM_Price_BAO_Field::addQuickFormElement( $this, $field_name, $field['id'], false, true );
          $this->price_fields_for_event[$event_in_cart->event_id][] = $field_name;
        }
      }
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

  function validate()
  {
    parent::validate();
    if ($this->_errors)
        return false;
    $this->cart->load_associations( );

    foreach ( $this->cart->get_main_events_in_carts( ) as $event_in_cart ) {
      $price_set_id = CRM_Price_BAO_Set::getFor( "civicrm_event", $event_in_cart->event_id );
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
        
        if ( empty( $check ) ) {
          $this->_errors['_qf_default'] = ts( "Select at least one option from Event Fee(s)." );
        }

        $lineItem = array( );
        if ( is_array( $this->_values['fee']['fields'] ) ) {
          CRM_Price_BAO_Set::processAmount( $this->_values['fee']['fields'], $fields, $lineItem );
          //XXX total...
          if ($fields['amount'] < 0) {
          $this->_errors['_qf_default'] = ts( "Event Fee(s) can not be less than zero. Please select the options accordingly" );
          }
        }
      }
      
      foreach ( $event_in_cart->participants as $mer_participant ) {
        $contact = CRM_Contact_BAO_Contact::matchContactOnEmail( $mer_participant->email );
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
            $this->_errors[$participant_form->email_field_name( )] = "The participant {$mer_participant->email} is already registered for {$event_in_cart->event->title} ({$event_in_cart->event->start_date}).";
          }
        }
      }
    }
    return empty( $this->_errors ) ? true : $this->_errors;
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
      //XXX but we can't validate until the deleted records have been cleaned up
      $this->load_form_values($this->_submitValues, $this->controller->_actionName[1] == 'upload' /* purge deletes */);
      $this->cart->load_associations();
    }
  }
}
