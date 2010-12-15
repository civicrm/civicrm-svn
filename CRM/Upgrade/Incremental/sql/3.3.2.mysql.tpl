-- CRM-7171

ALTER TABLE `civicrm_mailing`
   ADD `scheduled_date` datetime default NULL COMMENT 'Date and time this mailing was scheduled.',
   ADD `approver_id` int(10) unsigned default NULL COMMENT 'FK to Contact ID who approved this mailing',
   ADD CONSTRAINT `FK_civicrm_mailing_approver_id` FOREIGN KEY (`approver_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE SET NULL;
   ADD `approval_date` datetime default NULL COMMENT 'Date and time this mailing was approved.',
   ADD `approval_status_id` int unsigned default NULL COMMENT 'The status of this mailing. values: none, approved, rejected',
   ADD `approval_note` longtext default NULL COMMENT 'Note behind the decision.',
   ADD `visibilty` enum('User and User Admin Only','Public User Pages' default 'User and User Admin Only' COMMENT 'In what context(s) is the mailing contents visible (online viewing)';

UPDATE  `civicrm_navigation` SET  `permission` =  'access CiviMail,create mailings,approve mailings,schedule mailings', `permission_operator` =  'OR' WHERE  name = 'Mailings';

--CRM-7180, Change Participant Listing Templates menu title`

UPDATE `civicrm_navigation` SET `label` = '{ts escape="sql"}Participant Listing Options{/ts}', `name`= 'Participant Listing Options' WHERE name = 'Participant Listing Templates';

--CRM--7197
ALTER TABLE civicrm_mailing_job
DROP FOREIGN KEY parent_id, 
DROP INDEX parent_id ,
ADD CONSTRAINT FK_civicrm_mailing_job_parent_id 
FOREIGN KEY (parent_id) REFERENCES civicrm_mailing_job (id) ON DELETE CASCADE;

-- CRM-7206
UPDATE  civicrm_membership_type
   SET  relationship_type_id = NULL,  relationship_direction = NULL
 WHERE  relationship_type_id = 'Array' OR relationship_type_id IS NULL;

-- CRM-7171, Rules Mailing integration
{if $multilingual}
    INSERT INTO civicrm_option_group
        ( name,                   {foreach from=$locales item=locale}description_{$locale},   {/foreach} is_reserved, is_active)
    VALUES
        ( 'mail_approval_status', {foreach from=$locales item=locale}'CiviMail Approval Status',       {/foreach} 0, 1 );
{else}
    INSERT INTO civicrm_option_group
        (name, description, is_reserved, is_active )
    VALUES
        ('mail_approval_status', 'CiviMail Approval Status', 0, 1 );
{/if}

SELECT @mailCompId  := max(id) FROM civicrm_component where name = 'CiviMail';
SELECT @option_group_id_approvalStatus := max(id) from civicrm_option_group where name = 'mail_approval_status';

{if $multilingual}
    INSERT INTO civicrm_option_value
    (option_group_id, {foreach from=$locales item=locale}label_{$locale}, {/foreach} name, value, weight, is_active, component_id, is_default )

    VALUES
        (@option_group_id_approvalStatus, {foreach from=$locales item=locale}'Approved', {/foreach} 'Approved', 1, 1, 1, @mailCompId, 1 ),
        (@option_group_id_approvalStatus, {foreach from=$locales item=locale}'Rejected', {/foreach} 'Rejected', 2, 2, 1, @mailCompId, 0 ),
        (@option_group_id_approvalStatus, {foreach from=$locales item=locale}'None', {/foreach} 'None', 3, 3, 1, @mailCompId, 0 );

{else}
    INSERT INTO civicrm_option_value
    (option_group_id, label, name, value, weight, is_active, component_id, is_default )
 
    VALUES
        (@option_group_id_approvalStatus , '{ts escape="sql"}Approved{/ts}', 'Approved', 1,  1,   1, @mailCompId, 1 ),
        (@option_group_id_approvalStatus , '{ts escape="sql"}Rejected{/ts}', 'Rejected', 2,  2,   1, @mailCompId, 0 ),
	(@option_group_id_approvalStatus , '{ts escape="sql"}None{/ts}',     'None',    3,  3,   1, @mailCompId, 0 );
{/if}
