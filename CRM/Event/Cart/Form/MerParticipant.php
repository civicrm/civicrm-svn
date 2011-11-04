<?php
class CRM_Event_Cart_Form_MerParticipant
{
  public $participant = null;

  public $input_fields = array( );

  function __construct($participant)
  {
    //XXX
    $this->participant = $participant;
  }

  function get_fields()
  {
        $this->input_fields = array();

	$this->input_fields[] = array(
            'html_type' => 'text',
            'name' => $this->email_field_name(),
            'label' => ts('Email Address'),
            'required' => true,
        );
	$this->input_fields[] = array(
            'html_type' => 'text',
            'name' => $this->html_field_name( 'first_name' ),
            'label' => ts('First Name'),
            'required' => true,
        );
	$this->input_fields[] = array(
            'html_type' => 'text',
            'name' => $this->html_field_name( 'last_name' ),
            'label' => ts('Last Name'),
            'required' => true,
        );

        $custom_fields = self::get_participant_custom_data_fields($this->participant->event_id);
        foreach ($custom_fields as $custom_id => $field)
        {
            $this->input_fields[] = array(
                'html_type' => $field['html_type'],
                'name' => $this->html_field_name( $custom_id ),
                'label' => $field['label'],
                'required' => false
            );
        }

        return $this->input_fields;
  }

  // XXX rename form_add_fields
  function load_fields( $form )
  {
        $fields = $this->get_fields();
        foreach ($fields as $field)
        {
            $form->add($field['html_type'], $field['name'], $field['label'], array( 'size' => 30, 'maxlength' => 60 ), $field['required']);
        }
  }
    
  static function get_participant_custom_data_fields($event_id)
  {
    require_once 'CRM/Core/OptionGroup.php';
    require_once 'CRM/Core/BAO/CustomField.php';

    $params = array('id' => $event_id);
    $event_values = array( );
    $event = CRM_Event_BAO_Event::retrieve($params, $event_values);

    $_eventTypeCustomDataTypeID = CRM_Core_OptionGroup::getValue( 'custom_data_type', 'ParticipantEventType', 'name' );
    $customFieldsForEventType = CRM_Core_BAO_CustomField::getFields(
        'Participant',
        false,
        false,
        $event->event_type_id,
        $_eventTypeCustomDataTypeID );
    return $customFieldsForEventType;
  }

  function email_field_name( )
  {
	return $this->html_field_name( "email" );
  }

  static function full_field_name( $event_id, $participant_id, $field_name )
  {
	return "event_{$event_id}_participant_{$participant_id}_$field_name";
  }

  function html_field_name( $field_name )
  {
	return self::full_field_name( $this->participant->event_id, $this->participant->id, $field_name );
  }

  function name( )
  {
	return "Participant {$this->number()}";
  }

  function number( )
  {
        //XXX
        $cart = CRM_Event_Cart_BAO_Cart::find_by_id($this->participant->cart_id);
        $cart->load_associations();
        $index = $cart->get_participant_index_from_id($this->participant->id);
	return $index + 1;
  }

  //XXX poor name
  static public function get_form($participant)
  {
    return new CRM_Event_Cart_Form_MerParticipant($participant);
  }

  static function load_form_values($participant_params, $form_data)
  {
    $participant = new CRM_Event_Cart_BAO_MerParticipant($participant_params);
    $form = self::get_form($participant);

    $safe_field_names = array( 'email', 'first_name', 'last_name' );
    foreach ($safe_field_names as $key) {
        $value = CRM_Utils_Array::value( $form->html_field_name($key), $form_data );
        $form->participant->$key = $value;
    }
    //XXX
    $form->participant->store_temporary_contact();

    $custom_fields = self::get_participant_custom_data_fields($form->participant->event_id);
    foreach ($custom_fields as $custom_id => $field) {
        $value = CRM_Utils_Array::value( $form->html_field_name($custom_id), $form_data );
        if ($value) {
            $form->participant->custom_data_assign($custom_id, $value);
        }
    }
    $form->participant->save();
    return $form->participant;
  }
}
