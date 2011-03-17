-- CRM-7743
SELECT @option_group_id_languages := MAX(id) FROM civicrm_option_group WHERE name = 'languages';
UPDATE civicrm_option_value SET name = 'ce_RU' WHERE value = 'ce' AND option_group_id = @option_group_id_languages;

-- CRM-7750
SELECT @option_group_id_report := MAX(id)     FROM civicrm_option_group WHERE name = 'report_template';
SELECT @weight                 := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_report;
SELECT @contributeCompId       := MAX(id)     FROM civicrm_component    WHERE name = 'CiviContribute';

INSERT INTO civicrm_option_value
  (option_group_id, {localize field='label'}label{/localize}, value, name, weight, {localize field='description'}description{/localize}, is_active, component_id) 
  VALUES
  (@option_group_id_report, {localize}'Personal Campaign Page Report'{/localize}, 'contribute/pcp', 'CRM_Report_Form_Contribute_PCP', @weight := @weight + 1, {localize}'Shows Personal Campaign Page Report.'{/localize}, 1, @contributeCompId );

-- CRM-7681
UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:31:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:14:"street_address";s:1:"1";s:4:"city";s:1:"1";s:10:"country_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:92:"Provides a list of address and telephone information for constituent records in your system.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
  WHERE report_id   = 'contact/summary';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:25:{s:6:"fields";a:30:{s:9:"sort_name";s:1:"1";s:10:"country_id";s:1:"1";s:15:"contribution_id";s:1:"1";s:12:"total_amount";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:12:"receive_date";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:13:"membership_id";s:1:"1";s:18:"membership_type_id";s:1:"1";s:21:"membership_start_date";s:1:"1";s:19:"membership_end_date";s:1:"1";s:20:"membership_status_id";s:1:"1";s:14:"participant_id";s:1:"1";s:8:"event_id";s:1:"1";s:21:"participant_status_id";s:1:"1";s:7:"role_id";s:1:"1";s:25:"participant_register_date";s:1:"1";s:9:"fee_level";s:1:"1";s:10:"fee_amount";s:1:"1";s:15:"relationship_id";s:1:"1";s:20:"relationship_type_id";s:1:"1";s:12:"contact_id_b";s:1:"1";s:2:"id";s:1:"1";s:16:"activity_type_id";s:1:"1";s:7:"subject";s:1:"1";s:17:"source_contact_id";s:1:"1";s:18:"activity_date_time";s:1:"1";s:18:"activity_status_id";s:1:"1";s:17:"target_contact_id";s:1:"1";s:19:"assignee_contact_id";s:1:"1";}s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:90:"Provides contact-related information on contributions, memberships, events and activities.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
  WHERE report_id   = 'contact/detail';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:27:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:8:"event_id";s:1:"1";s:9:"status_id";s:1:"1";s:7:"role_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:8:"email_op";s:3:"has";s:11:"email_value";s:0:"";s:11:"event_id_op";s:2:"in";s:14:"event_id_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"rid_op";s:2:"in";s:9:"rid_value";a:0:{}s:34:"participant_register_date_relative";s:1:"0";s:30:"participant_register_date_from";s:0:"";s:28:"participant_register_date_to";s:0:"";s:6:"eid_op";s:2:"in";s:9:"eid_value";a:0:{}s:11:"custom_4_op";s:2:"in";s:14:"custom_4_value";a:0:{}s:16:"blank_column_end";s:0:"";s:11:"description";s:44:"Provides lists of participants for an event.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:6:"groups";s:0:"";s:7:"options";N;s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'event/participantListing';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:26:{s:6:"fields";a:6:{s:16:"contact_assignee";s:1:"1";s:14:"contact_target";s:1:"1";s:16:"activity_type_id";s:1:"1";s:16:"activity_subject";s:1:"1";s:18:"activity_date_time";s:1:"1";s:9:"status_id";s:1:"1";}s:17:"contact_source_op";s:3:"has";s:20:"contact_source_value";s:0:"";s:19:"contact_assignee_op";s:3:"has";s:22:"contact_assignee_value";s:0:"";s:17:"contact_target_op";s:3:"has";s:20:"contact_target_value";s:0:"";s:15:"current_user_op";s:2:"eq";s:18:"current_user_value";s:1:"0";s:27:"activity_date_time_relative";s:10:"this.month";s:23:"activity_date_time_from";s:0:"";s:21:"activity_date_time_to";s:0:"";s:19:"activity_subject_op";s:3:"has";s:22:"activity_subject_value";s:0:"";s:19:"activity_type_id_op";s:2:"in";s:22:"activity_type_id_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:11:"description";s:126:"Provides a list of constituent activity including activity statistics for one/all contacts during a given date range(required)";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"group_bys";N;s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'activity';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:18:{s:6:"fields";a:2:{s:18:"membership_type_id";s:1:"1";s:12:"total_amount";s:1:"1";}s:18:"join_date_relative";s:1:"0";s:14:"join_date_from";s:0:"";s:12:"join_date_to";s:0:"";s:21:"membership_type_id_op";s:2:"in";s:24:"membership_type_id_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:0:{}s:9:"group_bys";a:2:{s:9:"join_date";s:1:"1";s:18:"membership_type_id";s:1:"1";}s:14:"group_bys_freq";a:1:{s:9:"join_date";s:5:"MONTH";}s:11:"description";s:56:"Provides a summary of memberships by type and join date.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'member/summary';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:27:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:10:"country_id";s:1:"1";s:6:"amount";s:1:"1";s:9:"status_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:27:"pledge_create_date_relative";s:1:"0";s:23:"pledge_create_date_from";s:0:"";s:21:"pledge_create_date_to";s:0:"";s:17:"pledge_amount_min";s:0:"";s:17:"pledge_amount_max";s:0:"";s:16:"pledge_amount_op";s:3:"lte";s:19:"pledge_amount_value";s:0:"";s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:13:"Pledge Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviPledge";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'pledge/summary';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:17:{s:6:"fields";a:5:{s:9:"sort_name";s:1:"1";s:18:"pledge_create_date";s:1:"1";s:6:"amount";s:1:"1";s:14:"scheduled_date";s:1:"1";s:10:"country_id";s:1:"1";}s:27:"pledge_create_date_relative";s:1:"0";s:23:"pledge_create_date_from";s:0:"";s:21:"pledge_create_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:27:"Pledged but not Paid Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviPledge";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'pledge/pbnp';



UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:28:{s:6:"fields";a:5:{s:9:"sort_name";s:1:"1";s:18:"membership_type_id";s:1:"1";s:21:"membership_start_date";s:1:"1";s:19:"membership_end_date";s:1:"1";s:4:"name";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:18:"join_date_relative";s:1:"0";s:14:"join_date_from";s:0:"";s:12:"join_date_to";s:0:"";s:23:"owner_membership_id_min";s:0:"";s:23:"owner_membership_id_max";s:0:"";s:22:"owner_membership_id_op";s:3:"lte";s:25:"owner_membership_id_value";s:0:"";s:6:"tid_op";s:2:"in";s:9:"tid_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:119:"Provides a list of members along with their membership status and membership details (Join Date, Start Date, End Date).";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'member/detail';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:16:{s:6:"fields";a:5:{s:9:"sort_name";s:1:"1";s:18:"membership_type_id";s:1:"1";s:19:"membership_end_date";s:1:"1";s:4:"name";s:1:"1";s:10:"country_id";s:1:"1";}s:6:"tid_op";s:2:"in";s:9:"tid_value";a:0:{}s:28:"membership_end_date_relative";s:1:"0";s:24:"membership_end_date_from";s:0:"";s:22:"membership_end_date_to";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:85:"Provides a list of memberships that lapsed or will lapse before the date you specify.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'member/lapse';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:40:{s:6:"fields";a:2:{s:9:"sort_name";s:1:"1";s:25:"application_received_date";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:12:"gender_id_op";s:2:"in";s:15:"gender_id_value";a:0:{}s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:13:"grant_type_op";s:2:"in";s:16:"grant_type_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:18:"amount_granted_min";s:0:"";s:18:"amount_granted_max";s:0:"";s:17:"amount_granted_op";s:3:"lte";s:20:"amount_granted_value";s:0:"";s:20:"amount_requested_min";s:0:"";s:20:"amount_requested_max";s:0:"";s:19:"amount_requested_op";s:3:"lte";s:22:"amount_requested_value";s:0:"";s:34:"application_received_date_relative";s:1:"0";s:30:"application_received_date_from";s:0:"";s:28:"application_received_date_to";s:0:"";s:28:"money_transfer_date_relative";s:1:"0";s:24:"money_transfer_date_from";s:0:"";s:22:"money_transfer_date_to";s:0:"";s:23:"grant_due_date_relative";s:1:"0";s:19:"grant_due_date_from";s:0:"";s:17:"grant_due_date_to";s:0:"";s:11:"description";s:19:"Grant Report Detail";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviGrant";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'grant/detail';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:25:{s:6:"fields";a:10:{s:9:"sort_name";s:1:"1";s:12:"receive_date";s:1:"1";s:12:"total_amount";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:7:"trxn_id";s:1:"1";s:10:"invoice_id";s:1:"1";s:12:"check_number";s:1:"1";s:21:"payment_instrument_id";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:2:"id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:11:"description";s:37:"Shows Bookkeeping Transactions Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/bookkeeping';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:19:{s:6:"fields";a:3:{s:9:"sort_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"yid_op";s:2:"eq";s:9:"yid_value";s:4:"2011";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:157:"Last year but not this year. Provides a list of constituents who donated last year but did not donate during the time period you specify as the current year.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/lybunt';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:42:{s:6:"fields";a:1:{s:12:"total_amount";s:1:"1";}s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:13:"total_sum_min";s:0:"";s:13:"total_sum_max";s:0:"";s:12:"total_sum_op";s:3:"lte";s:15:"total_sum_value";s:0:"";s:15:"total_count_min";s:0:"";s:15:"total_count_max";s:0:"";s:14:"total_count_op";s:3:"lte";s:17:"total_count_value";s:0:"";s:13:"total_avg_min";s:0:"";s:13:"total_avg_max";s:0:"";s:12:"total_avg_op";s:3:"lte";s:15:"total_avg_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:9:"group_bys";a:1:{s:12:"receive_date";s:1:"1";}s:14:"group_bys_freq";a:1:{s:12:"receive_date";s:5:"MONTH";}s:11:"description";s:80:"Shows contribution statistics by month / week / year .. country / state .. type.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/summary';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:29:{s:6:"fields";a:3:{s:9:"sort_name";s:1:"1";s:13:"total_amount1";s:1:"1";s:13:"total_amount2";s:1:"1";}s:22:"receive_date1_relative";s:13:"previous.year";s:18:"receive_date1_from";s:0:"";s:16:"receive_date1_to";s:0:"";s:22:"receive_date2_relative";s:9:"this.year";s:18:"receive_date2_from";s:0:"";s:16:"receive_date2_to";s:0:"";s:17:"total_amount1_min";s:0:"";s:17:"total_amount1_max";s:0:"";s:16:"total_amount1_op";s:3:"lte";s:19:"total_amount1_value";s:0:"";s:17:"total_amount2_min";s:0:"";s:17:"total_amount2_max";s:0:"";s:16:"total_amount2_op";s:3:"lte";s:19:"total_amount2_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:9:"group_bys";a:1:{s:2:"id";s:1:"1";}s:11:"description";s:140:"Given two date ranges, shows contacts (and their contributions) who contributed in both the date ranges with percentage increase / decrease.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/repeat';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:34:{s:6:"fields";a:7:{s:9:"sort_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";s:10:"country_id";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:12:"receive_date";s:1:"1";s:12:"total_amount";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:13:"ordinality_op";s:2:"in";s:16:"ordinality_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:126:"Lists detailed contribution(s) for one / all contacts. Contribution summary report points to this report for specific details.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/detail';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:20:{s:6:"fields";a:5:{s:17:"organization_name";s:1:"1";s:9:"sort_name";s:1:"1";s:12:"total_amount";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:20:"organization_name_op";s:3:"has";s:23:"organization_name_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:5:"4_b_a";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:11:"description";s:193:"Displays a detailed contribution report for Organization relationships with contributors, as to if contribution done was  from an employee of some organization or from that Organization itself.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/organizationSummary';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:18:{s:6:"fields";a:3:{s:9:"sort_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"yid_op";s:2:"eq";s:9:"yid_value";s:4:"2011";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:179:"Some year(s) but not this year. Provides a list of constituents who donated at some time in the history of your organization but did not donate during the time period you specify.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/sybunt';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:23:{s:6:"fields";a:5:{s:21:"display_name_creditor";s:1:"1";s:24:"display_name_constituent";s:1:"1";s:14:"email_creditor";s:1:"1";s:14:"phone_creditor";s:1:"1";s:12:"total_amount";s:1:"1";}s:5:"id_op";s:2:"in";s:8:"id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:20:"Soft Credit details.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/softcredit';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:21:{s:6:"fields";a:5:{s:14:"household_name";s:1:"1";s:9:"sort_name";s:1:"1";s:12:"total_amount";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:17:"household_name_op";s:3:"has";s:20:"household_name_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:5:"6_b_a";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:11:"description";s:213:"Provides a detailed report for Contributions made by contributors(Or Household itself) who are having a relationship with household (For ex a Contributor is Head of Household for some household or is a member of.)";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contribute/householdSummary';


UPDATE civicrm_report_instance 
   SET form_values = '{literal}a:28:{s:6:"fields";a:4:{s:11:"sort_name_a";s:1:"1";s:11:"sort_name_b";s:1:"1";s:9:"label_a_b";s:1:"1";s:9:"label_b_a";s:1:"1";}s:14:"sort_name_a_op";s:3:"has";s:17:"sort_name_a_value";s:0:"";s:14:"sort_name_b_op";s:3:"has";s:17:"sort_name_b_value";s:0:"";s:17:"contact_type_a_op";s:2:"in";s:20:"contact_type_a_value";a:0:{}s:17:"contact_type_b_op";s:2:"in";s:20:"contact_type_b_value";a:0:{}s:12:"is_active_op";s:2:"eq";s:15:"is_active_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:19:"Relationship Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'
 WHERE report_id   = 'contact/relationship';



-- CRM-7775
ALTER TABLE `civicrm_activity`
ADD `engagement_level` int(10) unsigned default NULL COMMENT 'Assign a specific level of engagement to this activity. Used for tracking constituents in ladder of engagement.';


{if $renameColumnVisibility}
 ALTER TABLE `civicrm_mailing` CHANGE `visibilty` `visibility` ENUM( 'User and User Admin Only', 'Public Pages' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'User and User Admin Only' COMMENT 'In what context(s) is the mailing contents visible (online viewing)';
{/if}

-- CRM-7453
 UPDATE `civicrm_navigation` 
    SET `url` = 'civicrm/activity/email/add&atype=3&action=add&reset=1&context=standalone' WHERE `name` = 'New Email';

