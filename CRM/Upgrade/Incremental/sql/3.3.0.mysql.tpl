-- CRM-7088 giving respect to 'gotv campaign contacts' permission.
UPDATE   civicrm_navigation 
   SET   permission = CONCAT( permission, ',gotv campaign contacts' )
 WHERE   name in ( 'Other', 'Campaigns', 'Voter Listing' );
