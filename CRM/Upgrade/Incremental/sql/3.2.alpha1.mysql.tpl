--  CRM-5883

--  add table civicrm_website 
    CREATE TABLE civicrm_website (
        id int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Website Id.',
        contact_id int unsigned NULL DEFAULT NULL COMMENT 'FK To Contact ID.',
        url varchar(128) NULL DEFAULT NULL COMMENT 'Website.',
        website_type_id int unsigned NULL DEFAULT NULL COMMENT 'Which Website type does this website belong to.',
      	PRIMARY KEY ( id ),
	INDEX UI_website_type_id( website_type_id ),
	CONSTRAINT FK_civicrm_website_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE
    )  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
    
--  insert home_URL and image_URL for already exists contacts
    INSERT INTO civicrm_website ( contact_id, url, website_type_id ) SELECT cc.id, cc.home_URL, 1 FROM civicrm_contact cc WHERE cc.home_URL IS NOT NULL ;
    INSERT INTO civicrm_website ( contact_id, url, website_type_id ) SELECT cc.id, cc.image_URL, 3 FROM civicrm_contact cc WHERE cc.image_URL IS NOT NULL ;

--  drop columns home_URL and image_URL
    ALTER TABLE civicrm_contact DROP home_URL, DROP image_URL ;

--  add option group website_type
    INSERT INTO civicrm_option_group
        (name, {localize field='description'}description{/localize}, is_reserved, is_active)
    VALUES 
        ('website_type', {localize}'Website Type'{/localize} , 0, 1),
        ('tag_used_for', {localize}'Tag Used For'{/localize}, 0, 1);
    SELECT @option_group_id_website := max(id) FROM civicrm_option_group WHERE name = 'website_type' ;
    SELECT @option_group_id_tuf := max(id) FROM civicrm_option_group WHERE name = 'tag_used_for' ;

    INSERT INTO civicrm_option_value
    	(option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, visibility_id) 
    VALUES
       (@option_group_id_website, 'Home',     1, 'Home',     NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_website, 'Work',     2, 'Work',     NULL, 0, NULL, 2, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_website, 'Image',    3, 'Image',    NULL, 0, NULL, 3, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_website, 'Facebook', 4, 'Facebook', NULL, 0, NULL, 4, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_website, 'Twitter',  5, 'Twitter',  NULL, 0, NULL, 5, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_website, 'Myspace',  6, 'Myspace',  NULL, 0, NULL, 6, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_tuf, {localize}'Contacts'{/localize}, 'civicrm_contact', 'Contacts', NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
       (@option_group_id_tuf, {localize}'Activities'{/localize}, 'civicrm_activity', 'Activities',  NULL, 0, NULL, 2, NULL, 0, 0, 1, NULL, NULL),	
       (@option_group_id_tuf, {localize}'Cases'{/localize}, 'civicrm_case', 'Cases', NULL, 0, NULL, 3, NULL, 0, 0, 1, NULL, NULL);
--  CRM-5962

--  add columns entity_table , entity_id in civicrm_entity_tag
    ALTER TABLE civicrm_entity_tag 
    ADD entity_table varchar(64) NULL DEFAULT NULL COMMENT 'physical tablename for entity being joined to file, e.g. civicrm_contact' AFTER id,
    DROP FOREIGN KEY FK_civicrm_entity_tag_contact_id,
    DROP INDEX UI_contact_id_tag_id, CHANGE contact_id entity_id int unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
    ADD INDEX index_entity (entity_table, entity_id) ;

--  entity_table field for exists records is civicrm_contact
    UPDATE civicrm_entity_tag 
    SET entity_table ='civicrm_contact' ;

--  add is_reserved, is_hidden, used_for in civicrm_tag
    ALTER TABLE civicrm_tag 
    ADD is_reserved tinyint DEFAULT 0, 
    ADD is_hidden tinyint DEFAULT 0, 
    ADD used_for varchar(64) NULL DEFAULT NULL;

   