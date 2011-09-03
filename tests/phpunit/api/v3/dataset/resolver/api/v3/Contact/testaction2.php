<?php

function civicrm_api3_contact_testaction2($params) {
  return civicrm_api3_create_success(
    array('0' => 'civicrm_api3_contact_testaction2 is ok'),
    $params,
    'contact',
    'testaction2'
  );
}
