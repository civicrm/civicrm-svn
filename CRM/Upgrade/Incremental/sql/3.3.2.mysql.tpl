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

