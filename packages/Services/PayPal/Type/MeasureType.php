<?php
/**
 * @package Services_PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'Services/PayPal/Type/XSDSimpleType.php';

/**
 * MeasureType
 *
 * @package Services_PayPal
 */
class MeasureType extends XSDSimpleType
{
    function MeasureType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:CoreComponentTypes';
        $this->_attributes = array_merge($this->_attributes,
            array (
              'unit' => 
              array (
                'name' => 'unit',
                'type' => 'xs:token',
                'use' => 'required',
              ),
            ));
    }

}
