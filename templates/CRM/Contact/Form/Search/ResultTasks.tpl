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
{* Form elements for displaying and running action tasks on search results *}
{capture assign=advSearchURL}
{if $context EQ 'smog'}
     {crmURL p='civicrm/group/search/advanced' q="gid=`$group.id`&reset=1&force=1"}
{elseif $context EQ 'amtg'}
     {crmURL p='civicrm/contact/search/advanced' q="context=amtg&amtgID=`$group.id`&reset=1&force=1"}
{else}
    {crmURL p='civicrm/contact/search/advanced' q="reset=1"}
{/if}
{/capture}
{capture assign=searchBuilderURL}
    {crmURL p='civicrm/contact/search/builder' q="reset=1"}
{/capture}

 <div id="search-status">
  <div class="float-right right">
    {if $action eq 256}
        <a href="{$advSearchURL}">&raquo; {ts}Advanced Search{/ts}</a><br />
        {if $context eq 'search'} {* Only show Search Builder link for basic search. *}
            <a href="{$searchBuilderURL}">&raquo; {ts}Search Builder{/ts}</a><br />
        {/if}
        {if $context eq 'smog'}
            {help id="id-smog-criteria"}
        {elseif $context eq 'amtg'}
            {help id="id-amtg-criteria"}
        {else}
            {help id="id-basic-criteria"}
        {/if}
    {elseif $action eq 512}
        <a href="{$searchBuilderURL}">&raquo; {ts}Search Builder{/ts}</a><br />
    {elseif $action eq 8192}
        <a href="{$advSearchURL}">&raquo; {ts}Advanced Search{/ts}</a><br />
    {/if}
  </div>

  <table class="form-layout-compressed">
  <tr>
    <td class="font-size12pt" style="width: 30%;">
        {if $savedSearch.name}{$savedSearch.name} ({ts}smart group{/ts}) - {/if}
        {ts count=$pager->_totalItems plural='%count Results'}%count Result{/ts}
    </td>
    
    {* Search criteria are passed to tpl in the $qill array *}
    <td class="nowrap">
    {if $qill}
      {include file="CRM/common/displaySearchCriteria.tpl"}
    {/if}
    </td>
  </tr>
  <tr>
    <td class="font-size11pt"> {ts}Select Records{/ts}:</td>
    <td class="nowrap">
        {$form.radio_ts.ts_all.html} {ts count=$pager->_totalItems plural='All %count records'}The found record{/ts} &nbsp; {if $pager->_totalItems > 1} {$form.radio_ts.ts_sel.html} {ts}Selected records only{/ts}{/if}
    </td>
  </tr>
  <tr>
    <td colspan="2">
     {* Hide export and print buttons in 'Add Members to Group' context. *}
     {if $context NEQ 'amtg'}
        {if $action eq 512}
<!--<div id="crm-contact-actions-wrapper">
	<div id="crm-contact-actions-link"><span><div class="icon dropdown-icon"></div>Actions</span></div>
		<div class="ac_results" id="crm-contact-actions-list">
			<div class="crm-contact-actions-list-inner">
			</div>
		</div>
	</div>-->
<ul>
   
          {$form._qf_Advanced_next_print.html}&nbsp; &nbsp;
        {elseif $action eq 8192}
          {$form._qf_Builder_next_print.html}&nbsp; &nbsp;
        {elseif $action eq 16384}
          {* since this does not really work for a non standard search
          {$form._qf_Custom_next_print.html}&nbsp; &nbsp;
          *}
        {else}
<!--<div id="crm-contact-actions-wrapper">
	<div id="crm-contact-actions-link"><span><div class="icon dropdown-icon"></div>Actions</span></div>
		<div class="ac_results" id="crm-contact-actions-list">
			<div class="crm-contact-actions-list-inner">
			</div>
		</div>
	</div>-->
<ul>
   
<li class="crm-contact-print crm-button">
                    <div class="icon print-icon"/></div>
<input id="Print" class="form-submit" type="submit" value="{$form._qf_Basic_next_print.value}" name="_qf_Basic_next_print" onclick="return checkPerformAction('mark_x', 'Basic', 1);"/>
                    </li>
<li class="crm-contact-xls crm-button">
                    <div class="icon xls-icon"/></div>
<input id="Excel" class="form-submit" type="submit" value="xls" name="_qf_Basic_next_print" onclick="return checkPerformAction('mark_x', 'Basic', 1);"/>
                    </li>
</ul>
{*$form._qf_Basic_next_print.html}&nbsp; &nbsp;*}

<!--input id="Doc" class="form-submit" type="submit" value="doc" name="_qf_Basic_next_print" onclick="return checkPerformAction('mark_x', 'Basic', 1);"/-->
        {/if}
        {$form.task.html}
     {/if}
     {if $action eq 512}
       {$form._qf_Advanced_next_action.html}
     {elseif $action eq 8192}
       {$form._qf_Builder_next_action.html}&nbsp;&nbsp;
     {elseif $action eq 16384}
       {$form._qf_Custom_next_action.html}&nbsp;&nbsp;
     {else}
       {$form._qf_Basic_next_action.html}
     {/if}
     </td>
  </tr>
  </table>
 </div>

{literal}
<script type="text/javascript">
toggleTaskAction( );
</script>
{/literal}

{literal}
<!--<script>
cj( function($) {
//  var tasks=[];
  $('#task option').each(function(){
    if (this.value)
      $('.crm-contact-actions-list-inner').append("<li task='"+this.value+"' class='double'>"+this.text+"</li>");
  });
//  $('.crm-contact-actions-list-inner').append("<div></div>");
  $('#task').parents('form').prepend("<input type='hidden' name='task' id='jstask'/><input type='hidden' name='_qf_Basic_next_action' value='Go'");
//  $('#Go').remove();
//  $('#task').remove();

  $('#CIVICRM_QFID_ts_all_4').attr("checked","true");

  $('#crm-contact-actions-list li').hover(
  	function(){ cj(this).addClass('ac_over');},
  	function(){ cj(this).removeClass('ac_over');}
	).click(function (){
    $('#jstask').attr("value",$(this).attr('task')).parents('form').submit();
	  $('#crm-contact-actions-list').toggle();
    return false;
  });

});
cj('body').click(function() {
	 	$('#crm-contact-actions-list').hide();
	 	});
cj('#crm-contact-actions-link').click(function(event) {
	cj('#crm-contact-actions-list').toggle();
	event.stopPropagation();
	});

</script>-->
{/literal}

