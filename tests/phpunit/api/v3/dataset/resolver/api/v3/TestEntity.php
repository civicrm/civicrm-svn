<?php

function civicrm_api3_test_entity_testaction3($params) {
  return civicrm_api3_create_success(
    array('0' => 'civicrm_api3_test_entity_testaction3 is ok'),
    $params,
    'test_entityt',
    'testaction3'
  );
}
