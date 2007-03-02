<?php

/**
 * Copyright (C) 2006 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

 /* This is the response handler code that will be invoked every time
  * a notification or request is sent by the Google Server
  *
  * To allow this code to receive responses, the url for this file
  * must be set on the seller page under Settings->Integration as the
  * "API Callback URL'
  * Order processing commands can be sent automatically by placing these
  * commands appropriately
  *
  * To use this code for merchant-calculated feedback, this url must be
  * set also as the merchant-calculations-url when the cart is posted
  * Depending on your calculations for shipping, taxes, coupons and gift
  * certificates update parts of the code as required
  *
  */

  //  chdir("..");
session_start( );

require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';

$config =& CRM_Core_Config::singleton();

require_once('Google/library/googleresponse.php');
require_once('Google/library/googlemerchantcalculations.php');
require_once('Google/library/googleresult.php');

//define('RESPONSE_HANDLER_LOG_FILE', 'googlemessage.log');
define('RESPONSE_HANDLER_LOG_FILE', $config->uploadDir . 'CiviCRM.log');

//Setup the log file
if (!$message_log = fopen(RESPONSE_HANDLER_LOG_FILE, "a")) {
    error_func("Cannot open " . RESPONSE_HANDLER_LOG_FILE . " file.\n", 0);
    exit(1);
 }

// Retrieve the XML sent in the HTTP POST request to the ResponseHandler
$xml_response = $HTTP_RAW_POST_DATA;
if (get_magic_quotes_gpc()) {
    $xml_response = stripslashes($xml_response);
 }
$headers = getallheaders();
fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),
                             $xml_response));

// Create new response object
$merchant_id  = '559999327053114';         //Your Merchant ID
$merchant_key = 'R2zv2g60-A7GXKJYl0nR0g';  //Your Merchant Key
$server_type  = "sandbox";                 //provision for live

$response = new GoogleResponse($merchant_id, $merchant_key,
                               $xml_response, $server_type);
$root = $response->root;
$data = $response->data;
fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),
                             $response->root));

//Use the following two lines to log the associative array storing the XML data
//$result = print_r($data,true);
//fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),$result));

//Check status and take appropriate action
$status = $response->HttpAuthentication($headers);

/* Commands to send the various order processing APIs
 * Send charge order : $response->SendChargeOrder($data[$root]
 *    ['google-order-number']['VALUE'], <amount>, $message_log);
 * Send proces order : $response->SendProcessOrder($data[$root]
 *    ['google-order-number']['VALUE'], $message_log);
 * Send deliver order: $response->SendDeliverOrder($data[$root]
 *    ['google-order-number']['VALUE'], <carrier>, <tracking-number>,
 *    <send_mail>, $message_log);
 * Send archive order: $response->SendArchiveOrder($data[$root]
 *    ['google-order-number']['VALUE'], $message_log);
 *
 */

switch ($root) {
 case "request-received": {
     break;
 }
 case "error": {
     break;
 }
 case "diagnosis": {
     break;
 }
 case "checkout-redirect": {
     break;
 }
 case "merchant-calculation-callback": {
     break;
 }
 case "new-order-notification": {
     $response->SendAck();
//      $abc = $data[$root]['shopping-cart']['merchant-private-data']['VALUE'];
//      $abc = stringToArray($abc);
     break;
 }
 case "order-state-change-notification": {
     $response->SendAck();
     $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
     $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];
     
     switch($new_financial_state) {
     case 'REVIEWING': {
         break;
     }
     case 'CHARGEABLE': {
//          $response->SendChargeOrder($data[$root]['google-order-number']['VALUE'], 
//                                     0.5, $message_log);
//          $response->SendProcessOrder($data[$root]['google-order-number']['VALUE'], 
//                                      $message_log);
         break;
     }
     case 'CHARGING': {
         break;
     }
     case 'CHARGED': {
         break;
        }
     case 'PAYMENT_DECLINED': {
         break;
     }
     case 'CANCELLED': {
         break;
     }
     case 'CANCELLED_BY_GOOGLE': {
         //$response->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
         //    "Sorry, your order is cancelled by Google", true, $message_log);
         break;
     }
     default:
         break;
      }
     
     switch($new_fulfillment_order) {
     case 'NEW': {
         break;
     }
     case 'PROCESSING': {
         break;
     }
     case 'DELIVERED': {
         break;
     }
     case 'WILL_NOT_DELIVER': {
         break;
     }
     default:
         break;
     }
 }
 case "charge-amount-notification": {
     $response->SendAck();
     //$response->SendDeliverOrder($data[$root]['google-order-number']['VALUE'], 
     //    <carrier>, <tracking-number>, <send-email>, $message_log);
     //$response->SendArchiveOrder($data[$root]['google-order-number']['VALUE'], 
     //    $message_log);

//      $response->SendDeliverOrder($data[$root]['google-order-number']['VALUE'], 
//                                  'UPS', 'Z9842W69871281267', "false", $message_log);
     break;
 }
 case "chargeback-amount-notification": {
     $response->SendAck();
     break;
 }
 case "refund-amount-notification": {
     $response->SendAck();
     break;
 }
 case "risk-information-notification": {
     $response->SendAck();
     break;
 }
 default: {
     break;
 }
 }
/* In case the XML API contains multiple open tags
 with the same value, then invoke this function and
 perform a foreach on the resultant array.
 This takes care of cases when there is only one unique tag
 or multiple tags.
 Examples of this are "anonymous-address", "merchant-code-string"
 from the merchant-calculations-callback API
*/
function get_arr_result($child_node) {
    $result = array();
    if(isset($child_node)) {
        if(is_associative_array($child_node)) {
            $result[] = $child_node;
        }
        else {
            foreach($child_node as $curr_node){
                $result[] = $curr_node;
            }
        }
    }
    return $result;
}

function stringToArray($str) {
    $vars = $labels = array();
    $labels = explode(',', $str);
    foreach ($labels as $label) {
        $terms = explode('=', $label);
        $vars[$terms[0]] = $terms[1];
    }
    return $vars;
}
/* Returns true if a given variable represents an associative array */
function is_associative_array( $var ) {
    return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
}
?>