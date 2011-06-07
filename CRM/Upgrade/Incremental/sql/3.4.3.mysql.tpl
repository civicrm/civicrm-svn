-- CRM-6134

{include file='../CRM/Upgrade/3.4.3.msg_template/civicrm_msg_template.tpl'}

--added on behalf of organization profile

INSERT INTO civicrm_uf_group
    ( name, group_type, {localize field='title'}title{/localize}, is_reserved )

VALUES
    ( 'on_behalf_organization', 'Contact,Organization,Contribution,Membership',  {localize}'On Behalf Of Organization'{/localize}, 1 );
    
SELECT @uf_group_id_onBehalfOrganization := max(id) from civicrm_uf_group where name = 'on_behalf_organization';

INSERT INTO civicrm_uf_join
   ( is_active, module, entity_table, entity_id, weight, uf_group_id ) 

VALUES
   ( 1, 'Profile', NULL, NULL, 7, @uf_group_id_onBehalfOrganization );
   
INSERT INTO civicrm_uf_field
   ( uf_group_id, field_name, is_required, is_reserved, weight, visibility, in_selector, is_searchable, location_type_id, {localize field='label'}label{/localize}, field_type, {localize field='help_post'}help_post{/localize}, phone_type_id ) 

VALUES
   ( @uf_group_id_onBehalfOrganization,   'organization_name',  1, 0, 1, 'User and User Admin Only',  0, 0, NULL, 
            {localize}'Organization Name'{/localize}, 'Organization', {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'phone',              1, 0, 2, 'User and User Admin Only',  0, 0, 3, 
            {localize}'Phone (Main) '{/localize},     'Contact',      {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'email',              1, 0, 3, 'User and User Admin Only',  0, 0, 3,  
            {localize}'Email (Main) '{/localize},     'Contact',      {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'street_address',     1, 0, 4, 'User and User Admin Only',  0, 0, 3,  
            {localize}'Street Address'{/localize},    'Contact',      {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'city',               1, 0, 5, 'User and User Admin Only',  0, 0, 3,  
            {localize}'City'{/localize},              'Contact',      {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'postal_code',        1, 0, 6, 'User and User Admin Only',  0, 0, 3,
            {localize}'Postal Code'{/localize},       'Contact',      {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'country',            1, 0, 7, 'User and User Admin Only',  0, 0, 3,
            {localize}'Country'{/localize},           'Contact',      {localize}NULL{/localize},  NULL ),
   ( @uf_group_id_onBehalfOrganization,   'state_province',     1, 0, 8, 'User and User Admin Only',  0, 0, 3,    
            {localize}'State / Province'{/localize},  'Contact',      {localize}NULL{/localize},  NULL );