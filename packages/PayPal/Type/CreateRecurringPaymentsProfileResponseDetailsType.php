<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDType.php';

/**
 * CreateRecurringPaymentsProfileResponseDetailsType
 *
 * @package PayPal
 */
class CreateRecurringPaymentsProfileResponseDetailsType extends XSDType
{
    /**
     * Recurring Billing Profile ID
     */
    var $ProfileID;

    function CreateRecurringPaymentsProfileResponseDetailsType()
    {
        parent::XSDType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ProfileID' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getProfileID()
    {
        return $this->ProfileID;
    }
    function setProfileID($ProfileID, $charset = 'iso-8859-1')
    {
        $this->ProfileID = $ProfileID;
        $this->_elements['ProfileID']['charset'] = $charset;
    }
}
