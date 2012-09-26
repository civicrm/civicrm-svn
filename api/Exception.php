<?php
/**
 * Define a custom exception class
 */
class api_Exception extends Exception
{
  private $extraParams = array();
  public $error_code = null;
  public $error_message = null;

  public function __construct($message, $code = 0, $extraParams = array(),Exception $previous = null) {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct(ts($message), $code, $previous);
        $this->extraParams = $extraParams + array('error_code' => $code);
    }
   
    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public function getExtraParams() {
        return $this->extraParams;
    }

    /* 
     * we are going to use short and easy to understand strings for the codes
     */

    public function getErrorCodes(){ 
      return array(
        'NOT_AN_ARRAY' => '$params was not an array',
        'NOT_A_DATE' => 'Invalid Value for Date field',
        'STRING_TOO_LONG' => 'String value is longer than permitted length',
        2000 => '$params was not an array',
        2001 => 'Invalid Value for Date field',
        2100 => 'String value is longer than permitted length'
      );
    }
}
