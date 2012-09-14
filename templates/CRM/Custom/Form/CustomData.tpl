{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{if $formEdit}
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
    {if $cd_edit.is_multiple and ( ( $cd_edit.max_multiple eq '' )  or ( $cd_edit.max_multiple > 0 and $cd_edit.max_multiple >= $cgCount ) ) }
        <div id="add-more-link-{$cgCount}"><a href="javascript:buildCustomData('{$cd_edit.extends}',{if $cd_edit.subtype}'{$cd_edit.subtype}'{else}'{$cd_edit.extends_entity_column_id}'{/if}, '', {$cgCount}, {$group_id}, true );">{ts 1=$cd_edit.title}Add another %1 record{/ts}</a></div>	
    {/if}
{else}
{foreach from=$groupTree item=cd_edit key=group_id name=custom_sets} 
{assign var=tableID value=$cd_edit.table_id}
{assign var=divName value=$group_id|cat:"_$tableID"}    
 <div id="{$cd_edit.name|cat:"_$divName"}" class="crm-accordion-wrapper crm-accordion_title-accordion {if $cd_edit.collapse_display and !$skipTitle}crm-accordion-closed{else}crm-accordion-open{/if}">
  {if !$skipTitle}
  <div class="crm-accordion-header">
   <div class="icon crm-accordion-pointer"></div>
    {$cd_edit.title}
   </div><!-- /.crm-accordion-header -->
  {/if} 
  <div id="{$cd_edit.name}" class="crm-accordion-body">
            {if $cd_edit.help_pre}
                <div class="messages help">{$cd_edit.help_pre}</div>
            {/if}
	     {if $cd_edit.is_multiple eq '1'} 
	       {if $cd_edit.table_id}  
 		<table class="no-border">
 		<tr id="statusmessg_{$group_id|cat:"_$tableID"}" class="hiddenElement">
 		<td><span class="success-status"></span></td>
 	    </tr>	    
 	    <tr>
 	           <div class="crm-submit-buttons">
 		       	<a href="javascript:showDelete( {$tableID}, '{$cd_edit.name}_{$group_id|cat:"_$tableID"}', {$group_id}, {$contactId} );" class="button delete-button" title="{ts 1=$cv_edit.title}Delete this %1 record{/ts}">
 			 <span><div class="icon delete-icon"></div>{ts}Delete{/ts}</span>
             		 </a>
             	      </div>  
 		</tr>
 		</table>
	      {/if}
 	  {/if}	
            <table class="form-layout-compressed">
                {foreach from=$cd_edit.fields item=element key=field_id}
                   {include file="CRM/Custom/Form/CustomField.tpl"}
                {/foreach}
            </table>
	    <div class="spacer"></div>
            {if $cd_edit.help_post}<div class="messages help">{$cd_edit.help_post}</div>{/if}
        {if $cd_edit.is_multiple and ( ( $cd_edit.max_multiple eq '' )  or ( $cd_edit.max_multiple > 0 and $cd_edit.max_multiple >= $cgCount ) ) }
            <div id="add-more-link-{$cgCount}"><a href="javascript:buildCustomData('{$cd_edit.extends}',{if $cd_edit.subtype}'{$cd_edit.subtype}'{else}'{$cd_edit.extends_entity_column_id}'{/if}, '', {$cgCount}, {$group_id}, true );">{ts 1=$cd_edit.title}Add another %1 record{/ts}</a></div>	
        {/if}
   </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
    <div id="custom_group_{$group_id}_{$cgCount}"></div>
{/foreach}
    <script type="text/javascript">
    {literal}
        cj(function() {
           cj().crmaccordions(); 
        });        
    {/literal}
    </script>
{/if}
<script type="text/javascript">
     {literal}
     function hideStatus( valueID, groupID ) {
         cj( '#statusmessg_'  + groupID + '_' + valueID ).hide( );
     }
 
 
     function showDelete( valueID, elementID, groupID, contactID ) {
         var confirmMsg = '{/literal}{ts}Are you sure you want to delete this record?{/ts}{literal} &nbsp; <a href="javascript:deleteCustomValue( ' + valueID + ',\'' + elementID + '\',' + groupID + ',' + contactID + ' );" style="text-decoration: underline;">{/literal}{ts}Yes{/ts}{literal}</a>&nbsp;&nbsp;&nbsp;<a href="javascript:hideStatus( ' + valueID + ', ' +  groupID + ' );" style="text-decoration: underline;">{/literal}{ts}No{/ts}{literal}</a>';
         cj( 'tr#statusmessg_' + groupID + '_' + valueID ).show( ).children().find('span').html( confirmMsg );
     }
 
     function deleteCustomValue( valueID, elementID, groupID, contactID ) {
         var postUrl = {/literal}"{crmURL p='civicrm/ajax/customvalue' h=0 }"{literal};
         cj.ajax({
           type: "POST",
           data:  "valueID=" + valueID + "&groupID=" + groupID +"&contactId=" + contactID + "&key={/literal}{crmKey name='civicrm/ajax/customvalue'}{literal}",    
           url: postUrl,
           success: function(html){
               cj( '#'+ elementID ).hide( );
               var resourceBase   = {/literal}"{$config->resourceBase}"{literal};
               var successMsg = '{/literal}{ts}The selected record has been deleted.{/ts}{literal} &nbsp;&nbsp;<a href="javascript:hideStatus( ' + valueID + ',' + groupID + ');"><img title="{/literal}{ts}close{/ts}{literal}" src="' +resourceBase+'i/close.png"/></a>';
               cj( 'tr#statusmessg_'  + groupID + '_' + valueID ).show( ).children().find('span').html( successMsg );
 			  var element = cj( '.ui-tabs-nav #tab_custom_' + groupID + ' a' );
 			  cj(element).html(cj(element).attr('title') + ' ('+ html+') ');
           }
         });
     }
     {/literal}
 </script>