-- +--------------------------------------------------------------------+
-- | CiviCRM version 3.1                                                |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2010                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+
-- Navigation Menu, Preferences and Mail Settings

SELECT @domainID := id FROM civicrm_domain where name = 'Default Domain Name';

-- Initial default state of system preferences
{literal}
INSERT INTO 
     civicrm_preferences(domain_id, contact_id, is_domain, contact_view_options, contact_edit_options, advanced_search_options, user_dashboard_options, address_options, address_format, mailing_format, display_name_format, sort_name_format, address_standardization_provider, address_standardization_userid, address_standardization_url, editor_id, mailing_backend, contact_autocomplete_options )
VALUES 
     (@domainID,NULL,1,'123456789101113','1234567891011','1234567891011121315161718','1234578','123456891011','{contact.address_name}\n{contact.street_address}\n{contact.supplemental_address_1}\n{contact.supplemental_address_2}\n{contact.city}{, }{contact.state_province}{ }{contact.postal_code}\n{contact.country}','{contact.addressee}\n{contact.street_address}\n{contact.supplemental_address_1}\n{contact.supplemental_address_2}\n{contact.city}{, }{contact.state_province}{ }{contact.postal_code}\n{contact.country}','{contact.individual_prefix}{ }{contact.first_name}{ }{contact.last_name}{ }{contact.individual_suffix}','{contact.last_name}{, }{contact.first_name}',NULL,NULL,NULL,2,'a:1:{s:15:"outBound_option";s:1:"3";}','12');
{/literal}

-- mail settings 

INSERT INTO civicrm_mail_settings (domain_id, name, is_default, domain) VALUES (@domainID, 'default', true, 'FIXME.ORG');

-- dashlets 

INSERT INTO `civicrm_dashboard` 
    ( `domain_id`, `label`, `url`, `content`, `permission`, `permission_operator`, `column_no`, `is_minimized`, `is_active`, `weight`, `created_date`, `is_fullscreen`, `is_reserved`) 
    VALUES 
    ( @domainID, '{ts escape="sql"}Activities{/ts}', 'civicrm/dashlet/activity&reset=1&snippet=4', NULL, 'access CiviCRM', NULL, 0, 0,'1', '1', NULL, 1, 1),
    ( @domainID, '{ts escape="sql"}My Cases{/ts}', 'civicrm/dashlet/myCases&reset=1&snippet=4', NULL, 'access CiviCase', NULL , '0', '0', '1', '1', '1', '1', NULL),
    ( @domainID, '{ts escape="sql"}All Cases{/ts}', 'civicrm/dashlet/allCases&reset=1&snippet=4', NULL, 'access CiviCase', NULL , '0', '0', '1', '1', '1', '1', NULL);

INSERT INTO 'civicrm_dashboard' 
    ('domain_id' , 'label' , 'url' , 'content' , 'permission' , 'permission_operator' , 'column_no' , 'is_minimized' , 'is_fullscreen' , 'is_active' , 'is_reserved' , 'weight' , 'created_date' )
    VALUES 
    ( @domainID, '{ts escape="sql"}My Cases{/ts}', 'civicrm/dashlet/MyCases&reset=1&snippet=4', NULL , 'access CiviCase', NULL , '0', '0', '1', '1', '1', '1', NULL);

INSERT INTO 'civicrm_dashboard' 
    ('domain_id' , 'label' , 'url' , 'content' , 'permission' , 'permission_operator' , 'column_no' , 'is_minimized' , 'is_fullscreen' , 'is_active' , 'is_reserved' , 'weight' , 'created_date' )
    VALUES 
    ( @domainID, '{ts escape="sql"}All Cases{/ts}', 'civicrm/dashlet/AllCases&reset=1&snippet=4', NULL , 'access CiviCase', NULL , '0', '0', '1', '1', '1', '1', NULL);

-- navigation 

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    (  @domainID, NULL, '{ts escape="sql"}Search{/ts}',  'Search...',    NULL, '',  NULL, '1', NULL, 1 );

SET @searchlastID:=LAST_INSERT_ID();
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/contact/search&reset=1',                          '{ts escape="sql"}Find Contacts{/ts}',      'Find Contacts', NULL, '',                      @searchlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/contact/search/advanced&reset=1',                 '{ts escape="sql"}Find Contacts - Advanced Search{/ts}', 'Find Contacts - Advanced Search', NULL, '', @searchlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/contact/search/custom&csid=15&reset=1',           '{ts escape="sql"}Full-text Search{/ts}',   'Full-text Search', NULL, '',                   @searchlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/contact/search/builder&reset=1',                  '{ts escape="sql"}Search Builder{/ts}',     'Search Builder', NULL, '',                     @searchlastID, '1', '1',  4 ), 
    ( @domainID, 'civicrm/case/search&reset=1',                             '{ts escape="sql"}Find Cases{/ts}',         'Find Cases', 'access my cases and activities,access all cases and activities', 'OR',            @searchlastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/contribute/search&reset=1',                       '{ts escape="sql"}Find Contributions{/ts}', 'Find Contributions', 'access CiviContribute', '',  @searchlastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/mailing&reset=1',                                 '{ts escape="sql"}Find Mailings{/ts}',      'Find Mailings', 'access CiviMail', '',         @searchlastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/member/search&reset=1',                           '{ts escape="sql"}Find Members{/ts}',       'Find Members', 'access CiviMember', '',        @searchlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/event/search&reset=1',                            '{ts escape="sql"}Find Participants{/ts}',  'Find Participants',  'access CiviEvent', '',   @searchlastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/pledge/search&reset=1',                           '{ts escape="sql"}Find Pledges{/ts}',       'Find Pledges', 'access CiviPledge', '',        @searchlastID, '1', NULL, 10 ),
    ( @domainID, 'civicrm/activity/search&reset=1',                         '{ts escape="sql"}Find Activities{/ts}',    'Find Activities', NULL,  '',                   @searchlastID, '1', '1',  11 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/contact/search/custom/list&reset=1',              '{ts escape="sql"}Custom Searches...{/ts}', 'Custom Searches...', NULL, '',                 @searchlastID, '1', NULL, 12 );

SET @customSearchlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/contact/search/custom&reset=1&csid=8',            '{ts escape="sql"}Activity Search{/ts}',                  'Activity Search',                  NULL, '', @customSearchlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/contact/search/custom&reset=1&csid=11',           '{ts escape="sql"}Contacts by Date Added{/ts}',           'Contacts by Date Added',           NULL, '', @customSearchlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/contact/search/custom&reset=1&csid=2',            '{ts escape="sql"}Contributors by Aggregate Totals{/ts}', 'Contributors by Aggregate Totals', NULL, '', @customSearchlastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/contact/search/custom&reset=1&csid=6',            '{ts escape="sql"}Proximity Search{/ts}',                 'Proximity Search',                 NULL, '', @customSearchlastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL,  '{ts escape="sql"}Contacts{/ts}', 'Contacts', NULL, '', NULL, '1', NULL, 3 );

SET @contactlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES        
    ( @domainID, 'civicrm/contact/add&reset=1&ct=Individual',       '{ts escape="sql"}New Individual{/ts}',         'New Individual',       'add contacts',     '',             @contactlastID, '1', NULL,  1 ),
    ( @domainID, 'civicrm/contact/add&reset=1&ct=Household',        '{ts escape="sql"}New Household{/ts}',          'New Household',        'add contacts',     '',             @contactlastID, '1', NULL,  2 ),
       ( @domainID, 'civicrm/contact/add&reset=1&ct=Organization',  '{ts escape="sql"}New Organization{/ts}',       'New Organization',     'add contacts',     '',             @contactlastID, '1', 1,     3 );


INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/activity&reset=1&action=add&context=standalone',  '{ts escape="sql"}New Activity{/ts}',           'New Activity',         NULL,               '',             @contactlastID, '1', NULL,  4 ), 
    ( @domainID, 'civicrm/activity/add&atype=3&action=add&reset=1&context=standalone', '{ts escape="sql"}New Email{/ts}',   'New Email',            NULL,               '',             @contactlastID, '1', '1',   5 ), 
    ( @domainID, 'civicrm/import/contact&reset=1',                          '{ts escape="sql"}Import Contacts{/ts}',        'Import Contacts',      'import contacts',  '',             @contactlastID, '1', NULL,  6 ), 
    ( @domainID, 'civicrm/import/activity&reset=1',                         '{ts escape="sql"}Import Activities{/ts}',      'Import Activities',    'import contacts',  '',             @contactlastID, '1', '1',   7 ), 
    ( @domainID, 'civicrm/group/add&reset=1',                               '{ts escape="sql"}New Group{/ts}',              'New Group',            'edit groups',      '',             @contactlastID, '1', NULL,  8 ), 
    ( @domainID, 'civicrm/group&reset=1',                                   '{ts escape="sql"}Manage Groups{/ts}',          'Manage Groups',        'access CiviCRM',   '',             @contactlastID, '1', '1',   9 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1&action=add',                    '{ts escape="sql"}New Tag{/ts}',                'New Tag',              'administer CiviCRM', '',           @contactlastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1',                               '{ts escape="sql"}Manage Tags (Categories){/ts}', 'Manage Tags (Categories)', 'administer CiviCRM', '',     @contactlastID, '1', NULL, 11 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,  '{ts escape="sql"}Contributions{/ts}', 'Contributions', 'access CiviContribute', '',      NULL,           '1', NULL,  4 );

SET @contributionlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/contribute&reset=1',                              '{ts escape="sql"}Dashboard{/ts}',              'Dashboard',              'access CiviContribute', '', @contributionlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/contribute/add&reset=1&action=add&context=standalone', '{ts escape="sql"}New Contribution{/ts}',  'New Contribution',       'access CiviContribute,edit contributions', 'AND', @contributionlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/contribute/search&reset=1',                       '{ts escape="sql"}Find Contributions{/ts}',     'Find Contributions',     'access CiviContribute', '', @contributionlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/contribute/import&reset=1',                       '{ts escape="sql"}Import Contributions{/ts}',   'Import Contributions',   'access CiviContribute,edit contributions', 'AND', @contributionlastID, '1', '1',  4 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID,NULL, '{ts escape="sql"}Pledges{/ts}',  'Pledges', 'access CiviPledge', '', @contributionlastID, '1',  1,   5 );
    
SET @pledgelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/pledge&reset=1',                                  '{ts escape="sql"}Dashboard{/ts}',                  'Dashboard',                 'access CiviPledge',  '',  @pledgelastID,       '1', NULL, 1 ), 
    ( @domainID, 'civicrm/pledge/add&reset=1&action=add&context=standalone', '{ts escape="sql"}New Pledge{/ts}',                'New Pledge',                'access CiviPledge,edit pledges',  'AND',  @pledgelastID,       '1', NULL, 2 ),
    ( @domainID, 'civicrm/pledge/search&reset=1',                           '{ts escape="sql"}Find Pledges{/ts}',               'Find Pledges',              'access CiviPledge',  '',  @pledgelastID,       '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/contribute&reset=1&action=add',             '{ts escape="sql"}New Contribution Page{/ts}',      'New Contribution Page',     'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/contribute&reset=1',                        '{ts escape="sql"}Manage Contribution Pages{/ts}',  'Manage Contribution Pages', 'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', '1',  7 ), 
    ( @domainID, 'civicrm/admin/pcp&reset=1',                               '{ts escape="sql"}Personal Campaign Pages{/ts}',    'Personal Campaign Pages',   'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/contribute/managePremiums&reset=1',         '{ts escape="sql"}Premiums (Thank-you Gifts){/ts}', 'Premiums',                  'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', 1,    9 ),
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                  '{ts escape="sql"}New Price Set{/ts}',              'New Price Set',             'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 10 ),
    ( @domainID, 'civicrm/admin/price&reset=1',                             '{ts escape="sql"}Manage Price Sets{/ts}',          'Manage Price Sets',         'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 11 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Events{/ts}',  'Events', 'access CiviEvent', '', NULL, '1', NULL, 5 );

SET @eventlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/event&reset=1',                                   '{ts escape="sql"}Dashboard{/ts}',          'CiviEvent Dashboard',  'access CiviEvent', '',    @eventlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/participant/add&reset=1&action=add&context=standalone', '{ts escape="sql"}Register Event Participant{/ts}', 'Register Event Participant', 'access CiviEvent,edit event participants', 'AND', @eventlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/event/search&reset=1',                            '{ts escape="sql"}Find Participants{/ts}',  'Find Participants',    'access CiviEvent', '',    @eventlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/event/import&reset=1',                            '{ts escape="sql"}Import Participants{/ts}','Import Participants',  'access CiviEvent,edit event participants', 'AND',    @eventlastID, '1', '1',  4 ), 
    ( @domainID, 'civicrm/event/add&reset=1&action=add',                    '{ts escape="sql"}New Event{/ts}',          'New Event',            'access CiviEvent,administer CiviCRM', 'AND',    @eventlastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/event/manage&reset=1',                            '{ts escape="sql"}Manage Events{/ts}',      'Manage Events',        'access CiviEvent,administer CiviCRM', 'AND',    @eventlastID, '1', 1, 6 ), 
    ( @domainID, 'civicrm/admin/eventTemplate&reset=1',                     '{ts escape="sql"}Event Templates{/ts}',    'Event Templates',      'access CiviEvent,administer CiviCRM', 'AND',    @eventlastID, '1', 1, 7 ), 
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                  '{ts escape="sql"}New Price Set{/ts}',      'New Price Set',        'access CiviEvent,administer CiviCRM', 'AND',    @eventlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/price&reset=1',                             '{ts escape="sql"}Manage Price Sets{/ts}',  'Manage Price Sets',    'access CiviEvent,administer CiviCRM', 'AND',    @eventlastID, '1', NULL, 9 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL, '{ts escape="sql"}Mailings{/ts}', 'Mailings', 'access CiviMail', '', NULL, '1', NULL, 6 );

SET @mailinglastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/mailing/send&reset=1',                            '{ts escape="sql"}New Mailing{/ts}', 'New Mailing',                                          'access CiviMail', '', @mailinglastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/mailing/browse/unscheduled&reset=1&scheduled=false', '{ts escape="sql"}Draft and Unscheduled Mailings{/ts}', 'Draft and Unscheduled Mailings', 'access CiviMail', '', @mailinglastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/mailing/browse/scheduled&reset=1&scheduled=true', '{ts escape="sql"}Scheduled and Sent Mailings{/ts}', 'Scheduled and Sent Mailings',          'access CiviMail', '', @mailinglastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/mailing/browse/archived&reset=1',                 '{ts escape="sql"}Archived Mailings{/ts}', 'Archived Mailings',                              'access CiviMail', '', @mailinglastID, '1', 1,    4 ), 
    ( @domainID, 'civicrm/admin/component&reset=1',                         '{ts escape="sql"}Headers, Footers, and Automated Messages{/ts}', 'Headers, Footers, and Automated Messages', 'access CiviMail,administer CiviCRM', 'AND', @mailinglastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',                  '{ts escape="sql"}Message Templates{/ts}', 'Message Templates',                 'administer CiviCRM', '', @mailinglastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/admin/options/from_email&group=from_email_address&reset=1', '{ts escape="sql"}From Email Addresses{/ts}', 'From Email Addresses', 'administer CiviCRM', '', @mailinglastID, '1', NULL, 7 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL, '{ts escape="sql"}Memberships{/ts}', 'Memberships', 'access CiviMember', '', NULL, '1', NULL, 7 );

SET @memberlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/member&reset=1',                              '{ts escape="sql"}Dashboard{/ts}',           'Dashboard',       'access CiviMember', '', @memberlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/member/add&reset=1&action=add&context=standalone', '{ts escape="sql"}New Membership{/ts}', 'New Membership',  'access CiviMember,edit memberships', 'AND', @memberlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/member/search&reset=1',                       '{ts escape="sql"}Find Members{/ts}',        'Find Members',    'access CiviMember', '', @memberlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/member/import&reset=1',                       '{ts escape="sql"}Import Memberships{/ts}',      'Import Members',  'access CiviMember,edit memberships', 'AND', @memberlastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Other{/ts}', 'Other', 'access CiviGrant,administer CiviCase,access my cases and activities,access all cases and activities', 'OR', NULL, '1', NULL, 9 );
    
SET @otherlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL, '{ts escape="sql"}Cases{/ts}', 'Cases', 'access my cases and activities,access all cases and activities', 'OR', @otherlastID, '1', NULL, 1 );

SET @caselastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/case&reset=1',        '{ts escape="sql"}Dashboard{/ts}', 'Dashboard', 'access my cases and activities,access all cases and activities', 'OR',       @caselastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/case/add&reset=1&action=add&atype=13&context=standalone', '{ts escape="sql"}New Case{/ts}', 'New Case', 'add contacts,access all cases and activities', 'AND', @caselastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/case/search&reset=1', '{ts escape="sql"}Find Cases{/ts}', 'Find Cases', 'access my cases and activities,access all cases and activities', 'OR',     @caselastID, '1', 1, 3 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Grants{/ts}', 'Grants', 'access CiviGrant', '', @otherlastID, '1', NULL, 2 );

SET @grantlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES        
    ( @domainID, 'civicrm/grant&reset=1',           '{ts escape="sql"}Dashboard{/ts}', 'Dashboard', 'access CiviGrant', '',       @grantlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/grant/add&reset=1&action=add&context=standalone', '{ts escape="sql"}New Grant{/ts}', 'New Grant', 'access CiviGrant,edit grants', 'AND', @grantlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/grant/search&reset=1',    '{ts escape="sql"}Find Grants{/ts}', 'Find Grants', 'access CiviGrant', '',   @grantlastID, '1', 1, 3 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL, '{ts escape="sql"}Administer{/ts}', 'Administer', 'administer CiviCRM', '', NULL, '1', NULL, 10 );

SET @adminlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin&reset=1', '{ts escape="sql"}Administration Console{/ts}', 'Administration Console', 'administer CiviCRM', '', @adminlastID, '1', NULL, 1 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Customize{/ts}', 'Customize', 'administer CiviCRM', '', @adminlastID, '1', NULL, 2 );

SET @CustomizelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES            
    ( @domainID, 'civicrm/admin/custom/group&reset=1',      '{ts escape="sql"}Custom Data{/ts}',     'Custom Data',     'administer CiviCRM', '', @CustomizelastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/uf/group&reset=1',          '{ts escape="sql"}CiviCRM Profile{/ts}', 'CiviCRM Profile', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/menu&reset=1',              '{ts escape="sql"}Navigation Menu{/ts}', 'Navigation Menu', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/options/custom_search&reset=1&group=custom_search', '{ts escape="sql"}Manage Custom Searches{/ts}', 'Manage Custom Searches', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/admin/price&reset=1',             '{ts escape="sql"}Price Sets{/ts}',      'Price Sets',      'administer CiviCRM', '', @CustomizelastID, '1', NULL, 5 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Configure{/ts}', 'Configure', 'administer CiviCRM', '', @adminlastID, '1', NULL, 3 );

SET @configurelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/configtask&reset=1',                    '{ts escape="sql"}Configuration Checklist{/ts}', 'Configuration Checklist', 'administer CiviCRM', '', @configurelastID, '1', NULL, 1 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/setting&reset=1',                       '{ts escape="sql"}Global Settings{/ts}',         'Global Settings',         'administer CiviCRM', '', @configurelastID, '1', NULL, 2 );

SET @globalSettinglastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/setting/component&reset=1',             '{ts escape="sql"}Enable CiviCRM Components{/ts}', 'Enable Components', 'administer CiviCRM', '',   @globalSettinglastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/setting/preferences/display&reset=1',   '{ts escape="sql"}Site Preferences (screen and form configuration){/ts}', 'Site Preferences', 'administer CiviCRM', '', @globalSettinglastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/setting/path&reset=1',                  '{ts escape="sql"}Directories{/ts}',        'Directories',      'administer CiviCRM', '',        @globalSettinglastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/setting/url&reset=1',                   '{ts escape="sql"}Resource URLs{/ts}',      'Resource URLs',    'administer CiviCRM', '',      @globalSettinglastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/setting/smtp&reset=1',                  '{ts escape="sql"}Outbound Email (SMTP/Sendmail){/ts}', 'Outbound Email', 'administer CiviCRM', '', @globalSettinglastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/setting/mapping&reset=1',               '{ts escape="sql"}Mapping and Geocoding{/ts}', 'Mapping and Geocoding',   'administer CiviCRM', '', @globalSettinglastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/paymentProcessor&reset=1',              '{ts escape="sql"}Payment Processors{/ts}', 'Payment Processors', 'administer CiviCRM', '', @globalSettinglastID, '1', NULL, 7  ), 
    ( @domainID, 'civicrm/admin/setting/localization&reset=1',          '{ts escape="sql"}Localization{/ts}',       'Localization',     'administer CiviCRM', '',       @globalSettinglastID, '1', NULL, 8  ), 
    ( @domainID, 'civicrm/admin/setting/preferences/address&reset=1',   '{ts escape="sql"}Address Settings{/ts}',   'Address Settings', 'administer CiviCRM', '',   @globalSettinglastID, '1', NULL, 9  ), 
    ( @domainID, 'civicrm/admin/setting/search&reset=1',                '{ts escape="sql"}Search Settings{/ts}',    'Search Settings',  'administer CiviCRM', '',    @globalSettinglastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/setting/date&reset=1',                  '{ts escape="sql"}Date Formats{/ts}',       'Date Formats',     'administer CiviCRM', '',       @globalSettinglastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/setting/uf&reset=1',                    '{ts escape="sql"}CMS Integration{/ts}',    'CMS Integration',  'administer CiviCRM', '',    @globalSettinglastID, '1', NULL, 12 ), 
    ( @domainID, 'civicrm/admin/setting/misc&reset=1',                  '{ts escape="sql"}Miscellaneous (version check, reCAPTCHA...){/ts}', 'Miscellaneous', 'administer CiviCRM', '', @globalSettinglastID, '1', NULL, 13 ), 
    ( @domainID, 'civicrm/admin/options/safe_file_extension&group=safe_file_extension&reset=1', '{ts escape="sql"}Safe File Extensions{/ts}', 'Safe File Extensions', 'administer CiviCRM', '', @globalSettinglastID, '1', NULL, 14 ), 
    ( @domainID, 'civicrm/admin/setting/debug&reset=1',                 '{ts escape="sql"}Debugging{/ts}',      'Debugging', 'administer CiviCRM', '',              @globalSettinglastID, '1', NULL, 15 ), 
    
    ( @domainID, 'civicrm/admin/mapping&reset=1',                      '{ts escape="sql"}Import/Export Mappings{/ts}', 'Import/Export Mappings',   'administer CiviCRM', '', @configurelastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',             '{ts escape="sql"}Message Templates{/ts}',      'Message Templates',        'administer CiviCRM', '', @configurelastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/domain&action=update&reset=1',         '{ts escape="sql"}Domain Information{/ts}',     'Domain Information',       'administer CiviCRM', '', @configurelastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/options/from_email_address&group=from_email_address&reset=1', '{ts escape="sql"}FROM Email Addresses{/ts}', 'FROM Email Addresses',    'administer CiviCRM', '', @configurelastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/setting/updateConfigBackend&reset=1',  '{ts escape="sql"}Update Directory Path and URL{/ts}', 'Update Directory Path and URL',         'administer CiviCRM', '', @configurelastID, '1', NULL, 7 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES         
    ( @domainID, NULL, '{ts escape="sql"}Manage{/ts}', 'Manage', 'administer CiviCRM', '', @adminlastID, '1', NULL, 4 );

SET @managelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/deduperules&reset=1',  '{ts escape="sql"}Find and Merge Duplicate Contacts{/ts}', 'Find and Merge Duplicate Contacts', 'administer CiviCRM', '', @managelastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/access&reset=1',       '{ts escape="sql"}Access Control{/ts}',                    'Access Control',                    'administer CiviCRM', '', @managelastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/synchUser&reset=1',    '{ts escape="sql"}Synchronize Users to Contacts{/ts}',     'Synchronize Users to Contacts',     'administer CiviCRM', '', @managelastID, '1', NULL, 3 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Option Lists{/ts}', 'Option Lists', 'administer CiviCRM', '', @adminlastID, '1', NULL, 5 );
    
SET @optionListlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES         
    ( @domainID, 'civicrm/admin/options/activity_type&reset=1&group=activity_type',                                    '{ts escape="sql"}Activity Types{/ts}',         'Activity Types',                           'administer CiviCRM', '',   @optionListlastID, '1', NULL,  1 ), 
    ( @domainID, 'civicrm/admin/reltype&reset=1',                                                                      '{ts escape="sql"}Relationship Types{/ts}',     'Relationship Types',                       'administer CiviCRM', '',   @optionListlastID, '1', NULL,  2 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1',                                                                          '{ts escape="sql"}Tags (Categories){/ts}',      'Tags (Categories)',                        'administer CiviCRM', '',   @optionListlastID, '1', 1,     3 ), 
    ( @domainID, 'civicrm/admin/options/gender&reset=1&group=gender',                                                  '{ts escape="sql"}Gender Options{/ts}',         'Gender Options',                           'administer CiviCRM', '',   @optionListlastID, '1', NULL,  4 ), 
    ( @domainID, 'civicrm/admin/options/individual_prefix&group=individual_prefix&reset=1',                            '{ts escape="sql"}Individual Prefixes (Ms, Mr...){/ts}', 'Individual Prefixes (Ms, Mr...)', 'administer CiviCRM', '',   @optionListlastID, '1', NULL,  5 ), 
    ( @domainID, 'civicrm/admin/options/individual_suffix&group=individual_suffix&reset=1',                            '{ts escape="sql"}Individual Suffixes (Jr, Sr...){/ts}', 'Individual Suffixes (Jr, Sr...)', 'administer CiviCRM', '',   @optionListlastID, '1', 1,     6 ), 
    ( @domainID, 'civicrm/admin/options/addressee&group=addressee&reset=1',                                            '{ts escape="sql"}Addressee Formats{/ts}',      'Addressee Formats',                        'administer CiviCRM', '',   @optionListlastID, '1', NULL,  7 ), 
    ( @domainID, 'civicrm/admin/options/email_greeting&group=email_greeting&reset=1',                                  '{ts escape="sql"}Email Greetings{/ts}',        'Email Greetings',                          'administer CiviCRM', '',   @optionListlastID, '1', NULL,  8 ), 
    ( @domainID, 'civicrm/admin/options/postal_greeting&group=postal_greeting&reset=1',                                '{ts escape="sql"}Postal Greetings{/ts}',       'Postal Greetings',                         'administer CiviCRM', '',   @optionListlastID, '1', 1,     9 ), 
    ( @domainID, 'civicrm/admin/options/instant_messenger_service&group=instant_messenger_service&reset=1',            '{ts escape="sql"}Instant Messenger Services{/ts}',     'Instant Messenger Services',       'administer CiviCRM', '',   @optionListlastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/locationType&reset=1',                                                                 '{ts escape="sql"}Location Types (Home, Work...){/ts}', 'Location Types (Home, Work...)',   'administer CiviCRM', '',   @optionListlastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/options/mobile_provider&group=mobile_provider&reset=1',                                '{ts escape="sql"}Mobile Phone Providers{/ts}', 'Mobile Phone Providers',                   'administer CiviCRM', '',   @optionListlastID, '1', NULL, 12 ), 
    ( @domainID, 'civicrm/admin/options/phone_type&group=phone_type&reset=1',                                          '{ts escape="sql"}Phone Types{/ts}',            'Phone Types',                              'administer CiviCRM', '',   @optionListlastID, '1', NULL, 13 ), 
    ( @domainID, 'civicrm/admin/options/preferred_communication_method&group=preferred_communication_method&reset=1','{ts escape="sql"}Preferred Communication Methods{/ts}', 'Preferred Communication Methods',   'administer CiviCRM', '',   @optionListlastID, '1', NULL, 14 ),
    ( @domainID, 'civicrm/admin/options/subtype&reset=1',                                                              '{ts escape="sql"}Contact Types{/ts}',       'Contact Types',                         'administer CiviCRM', '',   @optionListlastID, '1', NULL, 15 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,  '{ts escape="sql"}CiviCase{/ts}', 'CiviCase', 'administer CiviCase', NULL, @adminlastID, '1', NULL, 6 );

SET @adminCaselastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/options/case_type&group=case_type&reset=1',            '{ts escape="sql"}Case Types{/ts}',      'Case Types',      'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/options/redaction_rule&group=redaction_rule&reset=1',  '{ts escape="sql"}Redaction Rules{/ts}', 'Redaction Rules', 'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 2 ),
    ( @domainID, 'civicrm/admin/options/case_status&group=case_status&reset=1',  '{ts escape="sql"}Case Statuses{/ts}', 'Case Statuses', 'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 3 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL,  '{ts escape="sql"}CiviContribute{/ts}', 'CiviContribute', 'access CiviContribute,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 7 );
    
SET @adminContributelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/contribute&reset=1&action=add',            '{ts escape="sql"}New Contribution Page{/ts}',      'New Contribution Page',     'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/contribute&reset=1',                       '{ts escape="sql"}Manage Contribution Pages{/ts}',  'Manage Contribution Pages', 'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', '1',  7 ), 
    ( @domainID, 'civicrm/admin/pcp&reset=1',                              '{ts escape="sql"}Personal Campaign Pages{/ts}',    'Personal Campaign Pages',   'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/contribute/managePremiums&reset=1',        '{ts escape="sql"}Premiums (Thank-you Gifts){/ts}', 'Premiums',                  'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', 1,    9 ), 
    ( @domainID, 'civicrm/admin/contribute/contributionType&reset=1',      '{ts escape="sql"}Contribution Types{/ts}',         'Contribution Types',        'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 10), 
    ( @domainID, 'civicrm/admin/options/payment_instrument&group=payment_instrument&reset=1',  '{ts escape="sql"}Payment Instruments{/ts}',    'Payment Instruments',   'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/options/accept_creditcard&group=accept_creditcard&reset=1',    '{ts escape="sql"}Accepted Credit Cards{/ts}',  'Accepted Credit Cards', 'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', 1, 12 ),
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                  '{ts escape="sql"}New Price Set{/ts}',              'New Price Set',             'access CiviContribute,administer CiviCRM', 'AND',  @adminContributelastID, '1', NULL, 13 ),
    ( @domainID, 'civicrm/admin/price&reset=1',                             '{ts escape="sql"}Manage Price Sets{/ts}',          'Manage Price Sets',         'access CiviContribute,administer CiviCRM', 'AND',  @adminContributelastID, '1', NULL, 14 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES         
    ( @domainID, NULL, '{ts escape="sql"}CiviEvent{/ts}', 'CiviEvent', 'access CiviEvent,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 8 );

SET @adminEventlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/event/add&reset=1&action=add',                   '{ts escape="sql"}New Event{/ts}',          'New Event',                        'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/event/manage&reset=1',                           '{ts escape="sql"}Manage Events{/ts}',      'Manage Events',                    'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1,    2 ), 
    ( @domainID, 'civicrm/admin/eventTemplate&reset=1',                    '{ts escape="sql"}Event Templates{/ts}',    'Event Templates',                  'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1,    3 ), 
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                 '{ts escape="sql"}New Price Set{/ts}',      'New Price Set',                    'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/price&reset=1',                            '{ts escape="sql"}Manage Price Sets{/ts}',  'Manage Price Sets',                'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1,    5 ),
    ( @domainID, 'civicrm/admin/options/participant_listing&group=participant_listing&reset=1', '{ts escape="sql"}Participant Listing Templates{/ts}', 'Participant Listing Templates', 'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/options/event_type&group=event_type&reset=1',  '{ts escape="sql"}Event Types{/ts}',    'Event Types',                      'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/admin/participant_status&reset=1',                   '{ts escape="sql"}Participant Statuses{/ts}', 'Participant Statuses',       'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/options/participant_role&group=participant_role&reset=1', '{ts escape="sql"}Participant Roles{/ts}', 'Participant Roles',  'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 9 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}CiviGrant{/ts}', 'CiviGrant', 'access CiviGrant,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 9 );

SET @adminGrantlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/options/grant_type&group=grant_type&reset=1', '{ts escape="sql"}Grant Types{/ts}', 'Grant Types', 'access CiviGrant,administer CiviCRM', 'AND', @adminGrantlastID, '1', NULL, 1 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}CiviMail{/ts}', 'CiviMail', 'access CiviMail,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 10 );

SET @adminMailinglastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/component&reset=1',            '{ts escape="sql"}Headers, Footers, and Automated Messages{/ts}', 'Headers, Footers, and Automated Messages', 'access CiviMail,administer CiviCRM', 'AND', @adminMailinglastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',     '{ts escape="sql"}Message Templates{/ts}', 'Message Templates', 'administer CiviCRM', '',   @adminMailinglastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/options/from_email&group=from_email_address&reset=1', '{ts escape="sql"}From Email Addresses{/ts}', 'From Email Addresses', 'administer CiviCRM', '', @adminMailinglastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/mailSettings&reset=1',         '{ts escape="sql"}Mail Accounts{/ts}', 'Mail Accounts', 'access CiviMail,administer CiviCRM', 'AND',           @adminMailinglastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}CiviMember{/ts}', 'CiviMember', 'access CiviMember,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 11 );

SET @adminMemberlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/member/membershipType&reset=1',    '{ts escape="sql"}Membership Types{/ts}',        'Membership Types',        'access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/member/membershipStatus&reset=1',  '{ts escape="sql"}Membership Status Rules{/ts}', 'Membership Status Rules', 'access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', NULL, 2 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,                                             '{ts escape="sql"}CiviReport{/ts}',              'CiviReport',              'access CiviReport,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 12 );

SET @adminReportlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/report/list&reset=1',                            '{ts escape="sql"}Reports Listing{/ts}',  'Reports Listing', 'access CiviReport',    '', @adminReportlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/report/template/list&reset=1',             '{ts escape="sql"}Create Reports from Templates{/ts}', 'Create Reports from Templates', 'administer Reports', '', @adminReportlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/report/options/report_template&reset=1',   '{ts escape="sql"}Manage Templates{/ts}', 'Manage Templates', 'administer Reports',  '', @adminReportlastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/report/register&reset=1',                  '{ts escape="sql"}Register Report{/ts}',  'Register Report',  'administer Reports',  '', @adminReportlastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Help{/ts}', 'Help', NULL, '',  NULL, '1', NULL, 11);

SET @adminHelplastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'http://documentation.civicrm.org',   '{ts escape="sql"}Documentation{/ts}',      'Documentation',    NULL, 'AND', @adminHelplastID, '1', NULL, 1 ), 
    ( @domainID, 'http://forum.civicrm.org',           '{ts escape="sql"}Community Forums{/ts}',   'Community Forums', NULL, 'AND', @adminHelplastID, '1', NULL, 2 ), 
    ( @domainID, 'http://civicrm.org/participate',     '{ts escape="sql"}Participate{/ts}',        'Participate',      NULL, 'AND', @adminHelplastID, '1', NULL, 3 ), 
    ( @domainID, 'http://civicrm.org/aboutcivicrm',    '{ts escape="sql"}About{/ts}',              'About',            NULL, 'AND', @adminHelplastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql"}Reports{/ts}', 'Reports', 'access CiviReport', '',  NULL, '1', NULL, 8 );

SET @reportlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/report/list&reset=1', '{ts escape="sql"}Reports Listing{/ts}', 'Reports Listing', 'access CiviReport', '', @reportlastID, '1', 1,    1 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/report/template/list&reset=1',  '{ts escape="sql"}Create Reports from Templates{/ts}',  'Create Reports from Templates', 'administer Reports',  '', @reportlastID, '1', 1, 2 );
    
-- sample reports with navigation menus

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    (  @domainID, 'Constituent Report (Summary)', 'contact/summary', 'Provides a list of address and telephone information for constituent records in your system.', 'administer CiviCRM', '{literal}a:21:{s:6:"fields";a:4:{s:12:"display_name";s:1:"1";s:14:"street_address";s:1:"1";s:4:"city";s:1:"1";s:10:"country_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:92:"Provides a list of address and telephone information for constituent records in your system.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Constituent Report (Summary){/ts}',       '{literal}Constituent Report (Summary){/literal}',     'administer CiviCRM',       '',  @reportlastID,  '1', NULL, 3 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Constituent Report (Detail)', 'contact/detail', 'Provides contact-related information on contributions, memberships, events and activities.', 'administer CiviCRM', '{literal}a:15:{s:6:"fields";a:18:{s:12:"display_name";s:1:"1";s:10:"country_id";s:1:"1";s:15:"contribution_id";s:1:"1";s:12:"total_amount";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:12:"receive_date";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:13:"membership_id";s:1:"1";s:18:"membership_type_id";s:1:"1";s:21:"membership_start_date";s:1:"1";s:19:"membership_end_date";s:1:"1";s:14:"participant_id";s:1:"1";s:8:"event_id";s:1:"1";s:21:"participant_status_id";s:1:"1";s:7:"role_id";s:1:"1";s:25:"participant_register_date";s:1:"1";s:9:"fee_level";s:1:"1";s:10:"fee_amount";s:1:"1";}s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"display_name_op";s:3:"has";s:18:"display_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:90:"Provides contact-related information on contributions, memberships, events and activities.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Constituent Report (Detail){/ts}',        '{literal}Constituent Report (Detail){/literal}',      'administer CiviCRM',       '',  @reportlastID,  '1', NULL, 4 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Donor Report (Summary)', 'contribute/summary', 'Shows contribution statistics by month / week / year .. country / state .. type.', 'access CiviContribute', '{literal}a:38:{s:6:"fields";a:1:{s:12:"total_amount";s:1:"1";}s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:13:"total_sum_min";s:0:"";s:13:"total_sum_max";s:0:"";s:12:"total_sum_op";s:3:"lte";s:15:"total_sum_value";s:0:"";s:15:"total_count_min";s:0:"";s:15:"total_count_max";s:0:"";s:14:"total_count_op";s:3:"lte";s:17:"total_count_value";s:0:"";s:13:"total_avg_min";s:0:"";s:13:"total_avg_max";s:0:"";s:12:"total_avg_op";s:3:"lte";s:15:"total_avg_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:9:"group_bys";a:1:{s:12:"receive_date";s:1:"1";}s:14:"group_bys_freq";a:1:{s:12:"receive_date";s:5:"MONTH";}s:11:"description";s:80:"Shows contribution statistics by month / week / year .. country / state .. type.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";s:6:"charts";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Donor Report (Summary){/ts}',             '{literal}Donor Report (Summary){/literal}',           'access CiviContribute',    '',  @reportlastID,  '1', NULL, 5 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Donor Report (Detail)', 'contribute/detail', 'Lists detailed contribution(s) for one / all contacts. Contribution summary report points to this report for specific details.', 'access CiviContribute', '{literal}a:31:{s:6:"fields";a:6:{s:12:"display_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";s:10:"country_id";s:1:"1";s:12:"total_amount";s:1:"1";s:12:"receive_date";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:13:"ordinality_op";s:2:"in";s:16:"ordinality_value";a:0:{}s:11:"description";s:126:"Lists detailed contribution(s) for one / all contacts. Contribution summary report points to this report for specific details.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Donor Report (Detail){/ts}',              '{literal}Donor Report (Detail){/literal}',            'access CiviContribute',    '',  @reportlastID,  '1', NULL, 6 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Donation Summary Report (Repeat)', 'contribute/repeat', 'Given two date ranges, shows contacts (and their contributions) who contributed in both the date ranges with percentage increase / decrease.', 'access CiviContribute', '{literal}a:26:{s:6:"fields";a:3:{s:12:"display_name";s:1:"1";s:13:"total_amount1";s:1:"1";s:13:"total_amount2";s:1:"1";}s:22:"receive_date1_relative";s:13:"previous.year";s:18:"receive_date1_from";s:0:"";s:16:"receive_date1_to";s:0:"";s:22:"receive_date2_relative";s:9:"this.year";s:18:"receive_date2_from";s:0:"";s:16:"receive_date2_to";s:0:"";s:17:"total_amount1_min";s:0:"";s:17:"total_amount1_max";s:0:"";s:16:"total_amount1_op";s:3:"lte";s:19:"total_amount1_value";s:0:"";s:17:"total_amount2_min";s:0:"";s:17:"total_amount2_max";s:0:"";s:16:"total_amount2_op";s:3:"lte";s:19:"total_amount2_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:9:"group_bys";a:1:{s:2:"id";s:1:"1";}s:11:"description";s:140:"Given two date ranges, shows contacts (and their contributions) who contributed in both the date ranges with percentage increase / decrease.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Donation Summary Report (Repeat){/ts}',   '{literal}Donation Summary Report (Repeat){/literal}', 'access CiviContribute',    '',  @reportlastID,  '1', NULL, 7 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'SYBUNT Report', 'contribute/sybunt', 'Some year(s) but not this year. Provides a list of constituents who donated at some time in the history of your organization but did not donate during the time period you specify.', 'access CiviContribute', '{literal}a:15:{s:6:"fields";a:3:{s:12:"display_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"yid_op";s:2:"eq";s:9:"yid_value";s:4:"2009";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:179:"Some year(s) but not this year. Provides a list of constituents who donated at some time in the history of your organization but did not donate during the time period you specify.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"charts";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}SYBUNT Report{/ts}',                      'SYBUNT Report',                                       'access CiviContribute',    '',  @reportlastID,  '1', NULL, 8 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'LYBUNT Report', 'contribute/lybunt', 'Last year but not this year. Provides a list of constituents who donated last year but did not donate during the time period you specify as the current year.', 'access CiviContribute', '{literal}a:16:{s:6:"fields";a:3:{s:12:"display_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"yid_op";s:2:"eq";s:9:"yid_value";s:4:"2009";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:157:"Last year but not this year. Provides a list of constituents who donated last year but did not donate during the time period you specify as the current year.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:6:"charts";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}LYBUNT Report{/ts}',                      'LYBUNT Report',                                       'access CiviContribute',    '',  @reportlastID,  '1', NULL, 9 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Soft Credit Report', 'contribute/softcredit', 'Soft Credit details.', 'access CiviContribute', '{literal}a:21:{s:6:"fields";a:5:{s:21:"display_name_creditor";s:1:"1";s:24:"display_name_constituent";s:1:"1";s:14:"email_creditor";s:1:"1";s:14:"phone_creditor";s:1:"1";s:12:"total_amount";s:1:"1";}s:5:"id_op";s:2:"in";s:8:"id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:20:"Soft Credit details.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Soft Credit Report{/ts}',                 'Soft Credit Report',                                  'access CiviContribute',    '',  @reportlastID,  '1', NULL, 10 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Membership Report (Summary)', 'member/summary', 'Provides a summary of memberships by type and join date.', 'access CiviMember', '{literal}a:18:{s:6:"fields";a:2:{s:18:"membership_type_id";s:1:"1";s:12:"total_amount";s:1:"1";}s:18:"join_date_relative";s:1:"0";s:14:"join_date_from";s:0:"";s:12:"join_date_to";s:0:"";s:21:"membership_type_id_op";s:2:"in";s:24:"membership_type_id_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:0:{}s:9:"group_bys";a:2:{s:9:"join_date";s:1:"1";s:18:"membership_type_id";s:1:"1";}s:14:"group_bys_freq";a:1:{s:9:"join_date";s:5:"MONTH";}s:11:"description";s:56:"Provides a summary of memberships by type and join date.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql"}Membership Report (Summary){/ts}',        '{literal}Membership Report (Summary){/literal}',      'access CiviMember',        '',  @reportlastID,  '1', NULL, 11 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Membership Report (Detail)', 'member/detail', 'Provides a list of members along with their membership status and membership details (Join Date, Start Date, End Date).', 'access CiviMember', '{literal}a:27:{s:6:"fields";a:5:{s:12:"display_name";s:1:"1";s:18:"membership_type_id";s:1:"1";s:21:"membership_start_date";s:1:"1";s:19:"membership_end_date";s:1:"1";s:4:"name";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:18:"join_date_relative";s:1:"0";s:14:"join_date_from";s:0:"";s:12:"join_date_to";s:0:"";s:23:"owner_membership_id_min";s:0:"";s:23:"owner_membership_id_max";s:0:"";s:22:"owner_membership_id_op";s:3:"lte";s:25:"owner_membership_id_value";s:0:"";s:6:"tid_op";s:2:"in";s:9:"tid_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:119:"Provides a list of members along with their membership status and membership details (Join Date, Start Date, End Date).";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Membership Report (Detail){/ts}',         '{literal}Membership Report (Detail){/literal}',       'access CiviMember',        '',  @reportlastID,  '1', NULL, 12 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Membership Report (Lapsed)', 'member/lapse', 'Provides a list of memberships that lapsed or will lapse before the date you specify.', 'access CiviMember', '{literal}a:15:{s:6:"fields";a:5:{s:12:"display_name";s:1:"1";s:18:"membership_type_id";s:1:"1";s:19:"membership_end_date";s:1:"1";s:4:"name";s:1:"1";s:10:"country_id";s:1:"1";}s:6:"tid_op";s:2:"in";s:9:"tid_value";a:0:{}s:28:"membership_end_date_relative";s:1:"0";s:24:"membership_end_date_from";s:0:"";s:22:"membership_end_date_to";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:85:"Provides a list of memberships that lapsed or will lapse before the date you specify.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Membership Report (Lapsed){/ts}',         '{literal}Membership Report (Lapsed){/literal}',       'access CiviMember',        '',  @reportlastID,  '1', NULL, 13 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Event Participant Report (List)', 'event/participantListing', 'Provides lists of participants for an event.', 'access CiviEvent', '{literal}a:25:{s:6:"fields";a:4:{s:12:"display_name";s:1:"1";s:8:"event_id";s:1:"1";s:9:"status_id";s:1:"1";s:7:"role_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:8:"email_op";s:3:"has";s:11:"email_value";s:0:"";s:11:"event_id_op";s:2:"in";s:14:"event_id_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"rid_op";s:2:"in";s:9:"rid_value";a:0:{}s:34:"participant_register_date_relative";s:1:"0";s:30:"participant_register_date_from";s:1:" ";s:28:"participant_register_date_to";s:1:" ";s:6:"eid_op";s:2:"in";s:9:"eid_value";a:0:{}s:16:"blank_column_end";s:0:"";s:11:"description";s:44:"Provides lists of participants for an event.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:9:"parent_id";s:0:"";s:6:"groups";s:0:"";s:7:"options";N;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Event Participant Report (List){/ts}',    '{literal}Event Participant Report (List){/literal}',  'access CiviEvent',         '',  @reportlastID,  '1', NULL, 14 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Event Income Report (Summary)', 'event/summary', 'Provides an overview of event income. You can include key information such as event ID, registration, attendance, and income generated to help you determine the success of an event.', 'access CiviEvent', '{literal}a:18:{s:6:"fields";a:2:{s:5:"title";s:1:"1";s:13:"event_type_id";s:1:"1";}s:5:"id_op";s:2:"in";s:8:"id_value";a:0:{}s:16:"event_type_id_op";s:2:"in";s:19:"event_type_id_value";a:0:{}s:25:"event_start_date_relative";s:1:"0";s:21:"event_start_date_from";s:1:" ";s:19:"event_start_date_to";s:1:" ";s:23:"event_end_date_relative";s:1:"0";s:19:"event_end_date_from";s:1:" ";s:17:"event_end_date_to";s:0:"";s:11:"description";s:181:"Provides an overview of event income. You can include key information such as event ID, registration, attendance, and income generated to help you determine the success of an event.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:9:"parent_id";s:3:"174";s:6:"charts";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Event Income Report (Summary){/ts}',      '{literal}Event Income Report (Summary){/literal}',    'access CiviEvent',         '',  @reportlastID,  '1', NULL, 15 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Event Income Report (Detail)', 'event/income', 'Helps you to analyze the income generated by an event. The report can include details by participant type, status and payment method.', 'access CiviEvent', '{literal}a:7:{s:5:"id_op";s:2:"in";s:8:"id_value";N;s:11:"description";s:133:"Helps you to analyze the income generated by an event. The report can include details by participant type, status and payment method.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Event Income Report (Detail){/ts}',       '{literal}Event Income Report (Detail){/literal}',     'access CiviEvent',         '',  @reportlastID,  '1', NULL, 16 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Attendee List', 'event/participantListing', 'Provides lists of event attendees.', 'access CiviEvent', '{literal}a:25:{s:6:"fields";a:4:{s:12:"display_name";s:1:"1";s:14:"participant_id";s:1:"1";s:9:"status_id";s:1:"1";s:7:"role_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:8:"email_op";s:3:"has";s:11:"email_value";s:0:"";s:11:"event_id_op";s:2:"in";s:14:"event_id_value";a:1:{i:0;s:1:"1";}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"rid_op";s:2:"in";s:9:"rid_value";a:0:{}s:34:"participant_register_date_relative";s:1:"0";s:30:"participant_register_date_from";s:0:"";s:28:"participant_register_date_to";s:0:"";s:6:"eid_op";s:2:"in";s:9:"eid_value";a:0:{}s:16:"blank_column_end";s:1:"1";s:11:"description";s:34:"Provides lists of event attendees.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";s:7:"options";N;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Attendee List{/ts}',                      'Attendee List',                                       'access CiviEvent',         '',  @reportlastID,  '1', NULL, 17 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Activity Report ', 'activity', 'Provides a list of constituent activity including activity statistics for one/all contacts during a given date range(required)', 'administer CiviCRM', '{literal}a:23:{s:6:"fields";a:7:{s:14:"contact_source";s:1:"1";s:16:"contact_assignee";s:1:"1";s:14:"contact_target";s:1:"1";s:16:"activity_type_id";s:1:"1";s:7:"subject";s:1:"1";s:18:"activity_date_time";s:1:"1";s:9:"status_id";s:1:"1";}s:17:"contact_source_op";s:3:"has";s:20:"contact_source_value";s:0:"";s:19:"contact_assignee_op";s:3:"has";s:22:"contact_assignee_value";s:0:"";s:17:"contact_target_op";s:3:"has";s:20:"contact_target_value";s:0:"";s:27:"activity_date_time_relative";s:10:"this.month";s:23:"activity_date_time_from";s:0:"";s:21:"activity_date_time_to";s:0:"";s:10:"subject_op";s:3:"has";s:13:"subject_value";s:0:"";s:19:"activity_type_id_op";s:2:"in";s:22:"activity_type_id_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:9:"group_bys";a:1:{s:17:"source_contact_id";s:1:"1";}s:11:"description";s:126:"Provides a list of constituent activity including activity statistics for one/all contacts during a given date range(required)";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Activity Report{/ts}',                    'activity',                                            'administer CiviCRM',       '',  @reportlastID,  '1', NULL, 18 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Relationship Report', 'contact/relationship', 'Gives relationship details between two contacts', 'administer CiviCRM', '{literal}a:23:{s:6:"fields";a:4:{s:14:"display_name_a";s:1:"1";s:14:"display_name_b";s:1:"1";s:9:"label_a_b";s:1:"1";s:9:"label_b_a";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:17:"contact_type_a_op";s:2:"in";s:20:"contact_type_a_value";a:0:{}s:17:"contact_type_b_op";s:2:"in";s:20:"contact_type_b_value";a:0:{}s:12:"is_active_op";s:2:"eq";s:15:"is_active_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:47:"Gives relationship details between two contacts";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Relationship Report{/ts}',                'Relationship Report',                                 'administer CiviCRM',       '',  @reportlastID,  '1', NULL, 19 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Donation Summary Report (Organization)', 'contribute/organizationSummary', 'Displays a detailed contribution report for Organization relationships with contributors, as to if contribution done was  from an employee of some organization or from that Organization itself.', 'access CiviContribute', '{literal}a:21:{s:6:"fields";a:5:{s:17:"organization_name";s:1:"1";s:12:"display_name";s:1:"1";s:12:"total_amount";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:20:"organization_name_op";s:3:"has";s:23:"organization_name_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:5:"4_b_a";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:1:" ";s:15:"receive_date_to";s:1:" ";s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:11:"description";s:193:"Displays a detailed contribution report for Organization relationships with contributors, as to if contribution done was  from an employee of some organization or from that Organization itself.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"parent_id";s:0:"";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Donation Summary Report (Organization){/ts}', '{literal}Donation Summary Report (Organization){/literal}', 'access CiviContribute', '',  @reportlastID,  '1', NULL, 20 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Donation Summary Report (Household)', 'contribute/householdSummary', 'Provides a detailed report for Contributions made by contributors(Or Household itself) who are having a relationship with household (For ex a Contributor is Head of Household for some household or is a member of.)', 'access CiviContribute', '{literal}a:21:{s:6:"fields";a:5:{s:14:"household_name";s:1:"1";s:12:"display_name";s:1:"1";s:12:"total_amount";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:17:"household_name_op";s:3:"has";s:20:"household_name_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:5:"6_b_a";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:1:" ";s:15:"receive_date_to";s:1:" ";s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:11:"description";s:213:"Provides a detailed report for Contributions made by contributors(Or Household itself) who are having a relationship with household (For ex a Contributor is Head of Household for some household or is a member of.)";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"parent_id";s:0:"";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Donation Summary Report (Household){/ts}',    '{literal}Donation Summary Report (Household){/literal}',    'access CiviContribute', '',  @reportlastID,  '1', NULL, 21 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Top Donors Report', 'contribute/topDonor', 'Provides a list of the top donors during a time period you define. You can include as many donors as you want (for example, top 100 of your donors).', 'access CiviContribute', '{literal}a:20:{s:6:"fields";a:2:{s:12:"display_name";s:1:"1";s:12:"total_amount";s:1:"1";}s:21:"receive_date_relative";s:9:"this.year";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:15:"total_range_min";s:0:"";s:15:"total_range_max";s:0:"";s:14:"total_range_op";s:2:"eq";s:17:"total_range_value";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:148:"Provides a list of the top donors during a time period you define. You can include as many donors as you want (for example, top 100 of your donors).";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Top Donors Report{/ts}',                  'Top Donors Report',            'access CiviContribute',   '',  @reportlastID,  '1', NULL, 22 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Pledge Summary Report', 'pledge/summary', 'Updates you with your Pledge Summary (if any) such as your pledge status, next payment date, amount, payment due, total amount paid etc.', 'access CiviPledge', '{literal}a:25:{s:6:"fields";a:4:{s:12:"display_name";s:1:"1";s:10:"country_id";s:1:"1";s:6:"amount";s:1:"1";s:9:"status_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:27:"pledge_create_date_relative";s:1:"0";s:23:"pledge_create_date_from";s:0:"";s:21:"pledge_create_date_to";s:0:"";s:17:"pledge_amount_min";s:0:"";s:17:"pledge_amount_max";s:0:"";s:16:"pledge_amount_op";s:3:"lte";s:19:"pledge_amount_value";s:0:"";s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:136:"Updates you with your Pledge Summary (if any) such as your pledge status, next payment date, amount, payment due, total amount paid etc.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviPledge";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Pledge Summary Report{/ts}',              'Pledge Summary Report',        'access CiviPledge',       '',  @reportlastID,  '1', NULL, 23 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Pledged But not Paid Report', 'pledge/pbnp', 'Pledged but not Paid Report', 'access CiviPledge', '{literal}a:15:{s:6:"fields";a:6:{s:12:"display_name";s:1:"1";s:18:"pledge_create_date";s:1:"1";s:6:"amount";s:1:"1";s:14:"scheduled_date";s:1:"1";s:10:"country_id";s:1:"1";s:5:"email";s:1:"1";}s:27:"pledge_create_date_relative";s:1:"0";s:23:"pledge_create_date_from";s:0:"";s:21:"pledge_create_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:27:"Pledged but not Paid Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviPledge";s:9:"parent_id";s:3:"174";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql"}Pledged But not Paid Report{/ts}',        'Pledged But not Paid Report',  'access CiviPledge',       '',  @reportlastID,  '1', NULL, 24 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;
