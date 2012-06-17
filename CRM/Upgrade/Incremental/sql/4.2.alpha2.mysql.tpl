-- CRM-10326
DELETE FROM `civicrm_price_set_entity` WHERE entity_table = "civicrm_contribution" or entity_table = "civicrm_participant";