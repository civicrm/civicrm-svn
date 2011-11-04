<?php

class CRM_Event_Cart_Page_CheckoutAJAX
{
  function add_participant_to_cart( )
  {
    $cart_id = $_GET['cart_id'];
    $event_id = $_GET['event_id'];

    require_once 'CRM/Event/Cart/BAO/Cart.php';
    $cart = CRM_Event_Cart_BAO_Cart::find_by_id($_GET['cart_id']);

    //XXX security
    require_once 'CRM/Event/Cart/BAO/MerParticipant.php';
    $participant = CRM_Event_Cart_BAO_MerParticipant::create( array(
        'cart_id' => $cart->id,
        'contact_id' => $cart->user_id,
	'event_id' => $event_id,
    ) );
    $participant->save( );

    require_once 'CRM/Core/Form.php';
    $form = new CRM_Core_Form( );
    $participant_form = $participant->get_form();
    $participant_form->load_fields($form);
    $renderer = $form->getRenderer();
    $form->accept($renderer);
    $template = CRM_Core_Smarty::singleton ();
    $template->assign( 'form', $renderer->toArray() );
    $template->assign( 'participant', $participant );
    $output = $template->fetch( "CRM/Event/Cart/Form/Checkout/Participant.tpl" );
    echo $output;
    CRM_Utils_System::civiExit( );
  }
}

