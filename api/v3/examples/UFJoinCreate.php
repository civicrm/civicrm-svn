<?php

function UF_join_create_example( )
{
  $params =  array(
            'module'       => 'CiviContribute',
            'entity_table' => 'civicrm_contribution_page',
            'entity_id'    => 1,
            'weight'       => 1,
            'uf_group_id'  => 11,
            'is_active'    => 1,
            'version'			 => 3,

  );
  $result = civicrm_api( 'civicrm_UF_join_create','UFJoin',$params );
  return $result;
}

function UF_join_create_expectedresult(){

  $expectedResult =

  array(
            'is_error'           => 0,
            'count' => 1,
      			'version' => 3,
            'values' => Array  (
  array('id' => 10,
                                   'is_active' => 1,
                                   'module' => 'CiviContribute',
                                   'entity_table'=>'civicrm_contribution_page',
  													       'entity_id' => 1,
                   								 'weight' => 1,
                   								 'uf_group_id' => 11,
  )));

   
   

   
  return $expectedResult  ;
}
