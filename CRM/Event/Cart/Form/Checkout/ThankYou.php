<?php

require_once 'CRM/Core/DAO/FinancialTrxn.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Event/BAO/Participant.php';

class CRM_Event_Cart_Form_Checkout_ThankYou extends CRM_Event_Cart_Form_Cart
{
  public $line_items = null;
  public $sub_total = 0;

  function buildLineItems( )
  {
	$not_waiting_participants = array( );
	$waiting_participants = array( );
	foreach ( $this->cart->events_in_carts as $event_in_cart ) {
	  $event_in_cart->load_location( );
	}
	$line_items = $this->get( 'line_items' );
	foreach ( $line_items as $line_item ) {
	  foreach ( $this->cart->events_in_carts as $event_in_cart ) {
		if ($line_item['event_id'] == $event_in_cart->event_id) {
		  $line_item['event'] = $event_in_cart->event;
		  $line_item['num_participants'] = $event_in_cart->num_not_waiting_participants();
		  $line_item['participants'] = $event_in_cart->not_waiting_participants();
		  $line_item['num_waiting_participants'] = $event_in_cart->num_waiting_participants();
		  $line_item['waiting_participants'] = $event_in_cart->waiting_participants();
		  $line_item['location'] = $event_in_cart->location;
		}
	  }
	  $this->sub_total += $line_item['amount'];
	  $this->line_items[] = $line_item;
	}
	$this->assign( 'line_items', $this->line_items );
  }

  function buildQuickForm( )
  {
    $defaults = array( );
    $ids = array( );
	$transaction = new CRM_Core_DAO_FinancialTrxn( );
	$transaction->id = $this->get( 'transaction_id' );
	$transaction->find( true );
	$template_params_to_copy = array
	(
	  'billing_name',
	  'billing_city',
	  'billing_country',
	  'billing_postal_code',
	  'billing_state',
	  'billing_street_address',
	  'credit_card_exp_date',
	  'credit_card_type',
	  'credit_card_number',
	);
	foreach ( $template_params_to_copy as $template_param_to_copy ) {
	  $this->assign( $template_param_to_copy, $this->get( $template_param_to_copy ) );
	}
	$this->buildLineItems( );
	$this->assign( 'discounts', $this->get( 'discounts' ) );
	$this->assign( 'events_in_carts', $this->cart->events_in_carts );
	$this->assign( 'transaction', $transaction );
	$this->assign( 'payment_required', $this->get( 'payment_required' ) );
	$this->assign( 'sub_total', $this->sub_total );
	$this->assign( 'total', $this->get( 'total' ) );
	$this->assign( 'trxn_id', $this->get( 'trxn_id' ) );
        // XXX Configure yourself
	//$this->assign( 'site_name', "" );
	//$this->assign( 'site_contact', "" );
  }

  function preProcess( )
  {
    $this->event_cart_id = $this->get( 'last_event_cart_id' );
    parent::preProcess( );
  }
}
