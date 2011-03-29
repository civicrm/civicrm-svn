{php}
$ogNames = array( 'petition' => ts('Message Template Workflow for Petition', array('escape' => 'sql')) );
        
$ovNames = array( 'petition' => 
                  array( 'petition_sign' => ts('Petition - signature added', array('escape' => 'sql')),
                         'petition_confirmation_needed' => ts('Petition - need verification', array('escape' => 'sql')),
                         ), 
                );
  $this->assign('ogNames',  $ogNames);
  $this->assign('ovNames',  $ovNames);
{/php}

INSERT INTO `civicrm_option_group` 
    ( `name`, {localize field='label'}label{/localize}, {localize field='description'}description{/localize}, `is_reserved`, `is_active` ) 
VALUES 
    ( 'msg_tpl_workflow_petition', {localize}'Message Template Workflow for Petition'{/localize},{localize}'Message Template Workflow for Petition'{/localize}, 0, 1 );

SELECT @option_group_id := MAX(id) from civicrm_option_group WHERE name = 'msg_tpl_workflow_petition';

INSERT INTO `civicrm_option_value` 
    ( `option_group_id`, {localize field='label'}label{/localize}, `name`, `value`, `weight`, `is_active` ) 
VALUES
        ( @option_group_id, {localize}'Petition - signature added'{/localize}, 'petition_sign', 1, 1, 1 ),
        ( @option_group_id, {localize}'Petition - need verification'{/localize}, 'petition_confirmation_needed', 2, 2, 1 );

{foreach from=$ovNames key=gName item=ovs}
{foreach from=$ovs key=vName item=label}
    SELECT @tpl_ovid_{$vName} := MAX(id) FROM civicrm_option_value WHERE option_group_id = @option_group_id AND name = '{$vName}';
{/foreach}
{/foreach}

INSERT INTO civicrm_msg_template 
    (msg_title, msg_subject, msg_text, msg_html, workflow_id, is_default, is_reserved) VALUES
{foreach from=$ovNames key=gName item=ovs name=for_groups}
{foreach from=$ovs key=vName item=title name=for_values}
    {fetch assign=subject file="`$smarty.const.SMARTY_DIR`/../../CRM/Upgrade/3.4.beta2.msg_template/message_templates/`$vName`_subject.tpl"}    {fetch assign=text    file="`$smarty.const.SMARTY_DIR`/../../CRM/Upgrade/3.4.beta2.msg_template/message_templates/`$vName`_text.tpl"}
    {fetch assign=html    file="`$smarty.const.SMARTY_DIR`/../../CRM/Upgrade/3.4.beta2.msg_template/message_templates/`$vName`_html.tpl"}
    ('{$title}', '{$subject|escape:"quotes"}', '{$text|escape:"quotes"}', '{$html|escape:"quotes"}', @tpl_ovid_{$vName}, 1, 0),
    ('{$title}', '{$subject|escape:"quotes"}', '{$text|escape:"quotes"}', '{$html|escape:"quotes"}', @tpl_ovid_{$vName}, 0, 1){if $smarty.foreach.for_groups.last and $smarty.foreach.for_values.last};{else},{/if}
{/foreach}
{/foreach}