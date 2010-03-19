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

    