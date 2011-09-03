<?php

function civicrm_api3_generic_testaction1($apiRequest) {
  return civicrm_api3_create_success(
    array('0' => 'civicrm_api3_generic_testaction1 is ok'),
    $apiRequest['params'],
    $apiRequest['entity'],
    $apiRequest['action']
  );
}
