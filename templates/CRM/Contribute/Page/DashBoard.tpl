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
{* CiviContribute DashBoard (launch page) *}
{if $buildChart}
  {include file = "CRM/Contribute/Form/ContributionCharts.tpl"}
{else} 
  <h3>{ts}Contribution Summary{/ts} {help id="id-contribute-intro"}</h3>&nbsp;
      <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
           <li id="chart_view"   class="ui-corner-top ui-state-active" > 
             {ts}Chart Layout{/ts} </li>&nbsp;
           <li id ="table_view"  class="ui-corner-top ui-state-default" >
             {ts}Table Layout{/ts}
           </li>
{if $isAdmin}
 {capture assign=newPageURL}{crmURL p="civicrm/admin/contribute" q="action=add&reset=1"}{/capture}
 {capture assign=configPagesURL}{crmURL p="civicrm/admin/contribute" q="reset=1"}{/capture}

<div class="float-right">
<table class="form-layout-compressed">
<tr>
    <td>
     <a href="{$configPagesURL}" class="button"><span>&raquo; {ts}Manage Contribution Pages{/ts}
       </span></a>
    </td>
    <td><a href="{$newPageURL}" class="button"><span>&raquo; {ts}New Contribution Page{/ts}
        </span></a>
    </td>
</tr>
</table>
</div>
{/if}
</ul></div>
<table class="report" style="display:none;">
<tr class="columnheader-dark">
    <th scope="col">{ts}Period{/ts}</th>
    <th scope="col">{ts}Total Amount{/ts}</th>
    <th scope="col" title="Contribution Count"><strong>#</strong></th><th></th></tr>
<tr>
    <td><strong>{ts}Current Month-To-Date{/ts}</strong></td>
    <td class="label">{if NOT $monthToDate.Valid.amount}{ts}(n/a){/ts}{else}{$monthToDate.Valid.amount}{/if}</td>
    <td class="label">{$monthToDate.Valid.count}</td>
    <td><a href="{$monthToDate.Valid.url}">{ts}view details{/ts}...</a></td>
</tr>
<tr>
    <td><strong>{ts}Current Fiscal Year-To-Date{/ts}</strong></td>
    <td class="label">{if NOT $yearToDate.Valid.amount}{ts}(n/a){/ts}{else}{$yearToDate.Valid.amount}{/if}</td>
    <td class="label">{$yearToDate.Valid.count}</td>
    <td><a href="{$yearToDate.Valid.url}">{ts}view details{/ts}...</a></td>
</tr>
<tr>
    <td><strong>{ts}Cumulative{/ts}</strong><br />{ts}(since inception){/ts}</td>
    <td class="label">{if NOT $startToDate.Valid.amount}{ts}(n/a){/ts}{else}{$startToDate.Valid.amount}{/if}</td>
    <td class="label">{$startToDate.Valid.count}</td>
    <td><a href="{$startToDate.Valid.url}">{ts}view details{/ts}...</a></td>
</tr>
</table>
 
<div id="chartData"></div>
<div class="spacer"></div>

{if $pager->_totalItems}
    <h3>{ts}Recent Contributions{/ts}</h3>
    <div>
        {include file="CRM/Contribute/Form/Selector.tpl" context="dashboard"}
    </div>
{/if}{literal}
<script type="text/javascript">
       cj('#table_view ,#chart_view').click( function() { 
            cj('.report,.chart,div.form-layout-compressed').toggle();
              if ( cj('#chart_view').hasClass('ui-state-active')){ 
                      cj('#chart_view').removeClass().addClass('ui-state-default');
                }else{ cj('#chart_view').removeClass().addClass('ui-state-active');}
              if ( cj('#table_view').hasClass('ui-state-default')){ 
                      cj('#table_view').removeClass().addClass('ui-state-active');
               }else{ cj('#table_view').removeClass().addClass('ui-state-default');}
	           
           });
       cj('#table_view,#chart_view').hover( function() {
	           cj(this).toggleClass('ui-state-hover');
           });
       cj(document).ready( function( ) {
                 getChart( );
           });        
function getChart( ) {
           var year        = cj('#select_year').val( );
           var charttype   = cj('#chart_type').val( );
	   var date        = new Date()
  	   var currentYear = date.getFullYear( );
	   if ( !charttype ) charttype = 'bvg';     
	   if ( !year ) year           = currentYear;

	  var chartUrl = {/literal}"{crmURL p='civicrm/ajax/chart'}"{literal};
          chartUrl    += "?year=" + year + "&type=" + charttype + "&snippet=" + 4;
	  
          cj.ajax({
                  url     : chartUrl,
		  async    : false,
		  success  : function(html){
                                     cj( "#chartData" ).html( html );
                             }	 
          });
     
}

</script>
{/literal}

{/if}