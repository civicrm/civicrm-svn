{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* Custom Data form*}
{foreach from=$groupTree item=cd_edit key=group_id}    
    <div id="{$cd_edit.name}_show_{$cgCount}" class="section-hidden section-hidden-border">
            <a href="#" onclick="cj('#{$cd_edit.name}_show_{$cgCount}').hide(); cj('#{$cd_edit.name}_{$cgCount}').show(); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a>
            <label>{$cd_edit.title}</label><br />
    </div>

    <div id="{$cd_edit.name}_{$cgCount}" class="form-item">
	<fieldset>
	    <legend><a href="#" onclick="cj('#{$cd_edit.name}_{$cgCount}').hide(); cj('#{$cd_edit.name}_show_{$cgCount}').show(); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{$cd_edit.title}</legend>
            {if $cd_edit.help_pre}
                <div class="messages help">{$cd_edit.help_pre}</div>
            {/if}
            <table class="form-layout-compressed">
                {foreach from=$cd_edit.fields item=element key=field_id}
                   {include file="CRM/Custom/Form/CustomField.tpl"}
                {/foreach}
            </table>
            <div class="spacer"></div>
            {if $cd_edit.help_post}<div class="messages help">{$cd_edit.help_post}</div>{/if}
        </fieldset>
        {if $cd_edit.is_multiple and ( ( $cd_edit.max_multiple eq '' )  or ( $cd_edit.max_multiple > 0 and $cd_edit.max_multiple >= $cgCount ) ) }
            <div id="add-more-link-{$cgCount}"><a href="javascript:buildCustomData('{$cd_edit.extends}',{if $cd_edit.subtype}'{$cd_edit.subtype}'{else}'{$cd_edit.extends_entity_column_id}'{/if}, '', {$cgCount}, {$group_id}, true );">{ts 1=$cd_edit.title}Add another %1 record{/ts}</a></div>	
        {/if}
    </div>
    <div id="custom_group_{$group_id}_{$cgCount}"></div>

    <script type="text/javascript">
    {if $cd_edit.collapse_display eq 0 }
            cj('#{$cd_edit.name}_show_{$cgCount}').hide(); cj('#{$cd_edit.name}_{$cgCount}').show();
    {else}
            cj('#{$cd_edit.name}_show_{$cgCount}').show(); cj('#{$cd_edit.name}_{$cgCount}').hide();
    {/if}
    </script>
{/foreach}
