-- db template customization

CREATE TABLE IF NOT EXISTS civicrm_persistent (
     id int(10) unsigned NOT NULL auto_increment COMMENT 'Persistent Record Id',
     context varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'Context for which name data pair is to be stored',
     name varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'Name of Context',
     data longtext collate utf8_unicode_ci COMMENT 'data associated with name',
     is_config tinyint(4) NOT NULL default '0' COMMENT 'Config Settings',
     PRIMARY KEY  (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4;
