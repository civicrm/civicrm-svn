<?php

require_once 'api/v3/utils.php';

/**
 *  Functions to inform caller that Location is obsolete and Address, Phone, Email, Website should be used
 */
function civicrm_api3_location_create ($params) {
  return civicrm_api3_create_error ("The Location API is obsolete, use the Address/Phone/Email/Website API instead");
}
function civicrm_api3_location_get ($params) {
  return civicrm_api3_create_error ("The Location API is obsolete, use the Address/Phone/Email/Website API instead");
}
function civicrm_api3_location_delete ($params) {
  return civicrm_api3_create_error ("The Location API is obsolete, use the Address/Phone/Email/Website API instead");
}

