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
{capture assign=infoTitle}{ts}Preview Mode{/ts}{/capture}
{assign var="infoType" value="info"}
{if $previewField }
  {capture assign=infoMessage}<strong>{ts}Profile Field Preview{/ts}</strong>{/capture}
{else}
  {capture assign=infoMessage}<strong>{ts}Profile Preview{/ts}</strong>{/capture}
{/if}
{include file="CRM/common/info.tpl"}
<div class="crm-form-block" id="crm-UF-preview">

{if ! empty( $fields )}
{if $viewOnly }
{* wrap in crm-container div so crm styles are used *}
<div id="crm-container-inner" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
{include file="CRM/common/CMSUser.tpl"}
  {strip}
  {if $help_pre && $action neq 4}<div class="messages help">{$help_pre}</div>{/if}
  {assign var=zeroField value="Initial Non Existent Fieldset"}
  {assign var=fieldset  value=$zeroField}
  {foreach from=$fields item=field key=fieldName}
  {if $field.groupTitle != $fieldset}
      {if $fieldset != $zeroField}
         </table>
         {if $groupHelpPost}
            <div class="messages help">{$groupHelpPost}</div>
         {/if}
         {if $mode ne 8}
            </fieldset>
         {/if}
      {/if}
     {if $mode ne 8}
          <h3>{$field.groupTitle}</h3>
     {/if}
      {assign var=fieldset  value=`$field.groupTitle`}
      {assign var=groupHelpPost  value=`$field.groupHelpPost`}
      {if $field.groupHelpPre}
          <div class="messages help">{$field.groupHelpPre}</div>
      {/if}
      <table class="form-layout-compressed" id="Fields">
  {/if}
  {* Show explanatory text for field if not in 'view' mode *}
      {if $field.help_pre && $action neq 4}
          <tr class="crm-entity" id="UFField-{$field.field_id}"><td>&nbsp;</td><td class="description crm-editable crmf-help_pre">{$field.help_pre}</td></tr>
        {/if}
    {assign var=n value=$field.name}
    {if $field.options_per_line }
  <tr>
        <td class="option-label">{$form.$n.label}</td>
        <td>
      {assign var="count" value="1"}
        {strip}
        <table class="form-layout-compressed">

         <tr>
          {* sort by fails for option per line. Added a variable to iterate through the element array*}
          {assign var="index" value="1"}
          {foreach name=outer key=key item=item from=$form.$n}
          {if $index < 10}
            {assign var="index" value=`$index+1`}
          {else}
            <td class="labels font-light">{$form.$n.$key.html}</td>
              {if $count == $field.options_per_line}
         </tr>
                   {assign var="count" value="1"}
              {else}
                   {assign var="count" value=`$count+1`}
              {/if}
          {/if}
          {/foreach}
        </table>
  {if $field.html_type eq 'Radio' and $form.formName eq 'Preview'}
           <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$n}', '{$form.formName}'); return false;">{ts}clear{/ts}</a>)</span>
  {/if}
        {/strip}
        </td>
    </tr>
  {else}
        <tr class="crm-entity crm-UFField" id="UFField-{$field.field_id}" data-weight="{$field.weight}" title="{$field.field_type}->{$form.$n.name}"><td class="label"><span class="crm-editable crmf-label">{$form.$n.label}</span></td>
  <td>
        {if $n eq 'group' && $form.group || ( $n eq 'tag' && $form.tag )}
           {include file="CRM/Contact/Form/Edit/TagsAndGroups.tpl" type=$n}
        {elseif $n eq 'email_greeting' or  $n eq 'postal_greeting' or $n eq 'addressee'}
               {include file="CRM/Profile/Form/GreetingType.tpl"}
        {elseif ( $field.data_type eq 'Date' AND $element.skip_calendar NEQ true ) or
                ( $n|substr:-5:5 eq '_date' ) or ( $field.name eq 'activity_date_time' )  }
               {include file="CRM/common/jcalendar.tpl" elementName=$form.$n.name}
        {else}
            {if $n|substr:0:4 eq 'url-'}
                 {assign var="websiteType" value=$n|cat:"-website_type_id"}
                 {$form.$websiteType.html}&nbsp;
            {elseif $n|substr:0:3 eq 'im-'}
               {assign var="provider" value=$n|cat:"-provider_id"}
               {$form.$provider.html}&nbsp;
            {/if}
            {$form.$n.html}
            {if $field.is_view eq 0}
               {if ( $field.html_type eq 'Radio' or  $n eq 'gender') and $form.formName eq 'Preview'}
                       <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$n}', '{$form.formName}'); return false;">{ts}clear{/ts}</a>)</span>
               {elseif $field.html_type eq 'Autocomplete-Select'}
                   {if $field.data_type eq 'ContactReference'}
                       {include file="CRM/Custom/Form/ContactReference.tpl" element_name = $n}
                   {else}
                       {include file="CRM/Custom/Form/AutoComplete.tpl" element_name = $n}
                   {/if}
                {/if}
            {/if}
     {/if}

    </td>
  {/if}
        {* Show explanatory text for field if not in 'view' mode *}
        {if $field.help_post && $action neq 4}
            <tr class="crm-editable" id="UFField-{$field.field_id}"><td>&nbsp;</td><td class="description crm-editable crmf-help_post">{$field.help_post}</td></tr>
        {/if}
    {/foreach}

    {if $addCAPTCHA }
        {include file='CRM/common/ReCAPTCHA.tpl'}
    {/if}
    </table>
    {if $field.groupHelpPost}
    <div class="messages help">{$field.groupHelpPost}</div>
    {/if}
    {/strip}
</div> {* end crm-container div *}
{else}
  {capture assign=infoMessage}{ts}This CiviCRM profile field is view only.{/ts}{/capture}
  {include file="CRM/common/info.tpl"}
{/if}
{else}
  {if $editInPlace}
    <table class="form-layout-compressed" id="table-1">
    <tr class="crm-entity"><td>&nbsp;</td><td class="">Drop a field here to add it to the profile</td></tr>
    </table>
  {/if}
{/if} {* fields array is not empty *}


{if !$editInPlace}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl"}
</div>
{else}
{include file="CRM/Contact/Page/Inline/AddFields.tpl" profileID=$id field_groups="field_groups"}
{/if}

</div>


{if $editInPlace}
<style>
{literal}

.crm-target {position:relative;left:-90px;}
#crm-UF-preview {position:relative; min-width:650px;}
#crm-add_fields {position:absolute;top:0;right:0;}
#crm-container-snippet .form-layout-compressed td.label {width:auto;}

#add_custom_fields ul {clear:both;}
#add_custom_fields form {clear:both;}
#add_custom_fields li {float:left;list-style:none;}
#add_custom_fields li a {background-color:#70716B!important;}
.left-popup-tool,.right-popup-tool {background:white;}
.left-popup-tool {border-radius:10px 0 0 10px;}
.right-popup-tool {border-radius:0px 0 10px 0;}

#crm-container {margin-right:230px;}
{/literal}
</style>
{/if}

<script>
cj.crmURL ('init', '{crmURL p="civicrm/example" q="placeholder"}');
{literal}
//example of usage alert ($.crmURL ('civicrm/admin/uf/group/field/update',{reset:1,action:'update',id:42}));
cj(function ($){

    $('#Preview input').attr("readonly","readonly");
    var render_tpl = function (event) {
      $(this).find('.crm-editable').crmEditable ({
        success: function(entity,field,value,data) {
          var $i=$(this);
          if ($i.is(':checkbox')) { // update the classes required_x or active_x
            var className='';
            if ($i.hasClass('crmf-is_active')) {
              className='active_';
            } else {
              className='required_';
            }
            if ($i.is(':checked')) {
              $i.closest('.crm-entity').removeClass(className+0).addClass(className+1);
            } else {
              $i.closest('.crm-entity').removeClass(className+1).addClass(className+0);
            }
          }
          $i.removeClass ('crm-editable-saving');
          $i.html(value);
        }
      });
    };



    $('#crm-UF-preview').on ('render', 'tr', function (event) { // edit a field
      event.stopPropagation();
      var $this = $(this);
      if ($this.hasClass('ui-droppable')){ // drop a new field
        render_tpl.call($this.next(),event);
        return;
      }
      if (!$this.hasClass('crm-UFField'))
        return;
      $this.next('.param-UFField').find('.crm-editable').crmEditable();
    });

    $("tr.crm-UFField").append ('<td class="edit_tool"><span class="ui-icon ui-icon-pencil" style="display:inline-block"></span></td>');
    $("tr.crm-UFField .edit_tool").click (function (){
     if ($(this).closest('tr').next().hasClass('param-UFField')) {
       $(this).closest('tr').next().remove();
       return;
     }
     var e=$(this).closest('.crm-entity').attr('id').match(/(\S*)-(\S*)/);
     var entity=e[1];
     var id=e[2];
     $(this).closest('tr').crmTemplate ('#tpl-edit-field',{id:id},{method:'after'});
     return false;
   });


    $(document).on ('load','#Field',function (event) { // loading the admin form for a field
      //or create a new custom field
console.log (this);
      $(this).find ('.crm-button_qf_Field_next_new').hide();
      $(this).find ('.crm-button-type-cancel').click(function (){ // cancelled by the used, don't save, just remove the form
        if ($(this).closest('.ui-dialog').length >0) {
          $(this).closest('.ui-dialog').dialog('close').remove();
        } else
          $(this).closest('.crm-target').html('').remove();
        return false;
      });
    });

    $(document).on ('success','tr.param-UFField', function (event,data) { // successfully saved a field from the admin form
      //TODO change the label
    });


    $('#crm-UF-preview').on ('render', 'table .crm-entity', function (event) { // field(s) added, add some jQuery love
      event.stopPropagation();
      render_tpl.call(this,event);
    });


  $('table').on('create','.crm-entity',function (event,data) { // drop a field, already created on the server.
    event.stopPropagation();
    $(this).crmTemplate('#tpl-field',data,{'method':'after'});
    //and now that it's created, let's make it editable. the template triggered a render event
  });

  $('.crm-editable').crmEditable ();


  $('tr.crm-entity').droppable ({
    hoverClass: "ui-state-active",//
    activeClass: "ui-state-hover",//will be added to the droppable while an acceptable draggable is being dragged.
    scope: 'field',
    drop:function (event,ui) {
      var params = {};
      params.field_type = ui.draggable.closest('.groupfields').data('field_type');
      params.field_name = ui.draggable.data('field_name');
      params.label = params.field_name;
      params.sequential=1;
{/literal}
        params.uf_group_id = {$gid};
{literal}
        params.weight =$(this).data('weight')+1;
        params.is_active = 1;

//        $(this).trigger ("create",{values:params});console.log(params);return true; // test drop without creation

        $().crmAPI.call(this,'UFField','Create',params,{success:function (data) {
          $(this).trigger ("create",data);
          ui.draggable.hide();
        }});
      }
    });

});
{/literal}
</script>

{if $editInPlace}

{literal}
<script id="tpl-edit-field" type="text/x-template">
<tr class="param-UFField">
  <td></td>
  <td class="left-popup-tool">
    <input class="required" type="checkbox"><label>Required</label>
    <br/>
    <a href="/civicrm/admin/uf/group/field/update?reset=1&action=update&id={{id}}" class="crm-editable crm-dialog admin-field">
      <span class="ui-icon ui-icon-wrench aicon" style="display:inline-block"></span>Advanced edit
    </a>
    <br>
    <span class="ui-icon ui-icon-trash" style="display:inline-block"></span>Delete
  </td>
  <td class="right-popup-tool"></td>

</tr>
</script>

<script id="tpl-field" type="text/x-template">
{{#values}}
<tr class="crm-UFField crm-entity ui-droppable" id="UFField-{{id}}" data-weight="{{weight}}" title="{{field_type}}->{{field_name}}"><td class="label crm-editable crmf-label">
{{label}}</td><td title="reload to refresh"><i>field {{field_type}}->{{field_name}}</i></td></tr>
{{/values}}
</script>
{/literal}

{crmAPI var="groups" entity="CustomGroup" action="get" sequential="1"}
<div id="add_custom_fields">
<h3>Add a new custom field into</h3>
<ul>
{foreach from=$groups.values item=group name=group}
{assign var="group_id" value=$group.id}
<li id="crm-entity" id="CustomGroup-{$group_id}"><a href="{crmURL p='civicrm/admin/custom/group/field/add' q="reset=1&action=add&gid=$group_id"}" class="crm-dialog crm-editable button" title="add a custom field into {$group.name}">{$group.extend} {$group.name}</a></li>
{/foreach}
<li><a href="{crmURL p='civicrm/admin/custom/group' q='action=add&reset=1'}" id="newCustomDataGroup" class="button crm-dialog crm-editable"><span><div class="icon add-icon"></div>Create New Set of Custom Fields</span></a>
</ul>
</div>
{/if}
