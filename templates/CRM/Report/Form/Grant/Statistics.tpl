{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{include file="CRM/Report/Form.tpl"}

<table class="report-layout">
{foreach from=$grantStatistics key=key item=val}
  <tr>
     {if $val.count}
         <th class="statistics" scope="row">{$val.title}</th>
     {else}
         <th class="statistics" scope="row">{$val.title}</th>
     {/if}
     <td>
       <table>
         {if $val.count}
           <tr>
             <td>{$val.count}</td>  
           </tr>
         {/if}
           {foreach from=$val.value key=type item=row}
            <tr>
               <td>{$type} : {$row.count} Grants, &nbsp;&nbsp;
                   {foreach from=$row.currency item=values key=field}
                      {$field} {$values}
		   {/foreach}
	       </td>
            </tr>
           {/foreach}
       </table>
     </td>
  </tr>      
{/foreach}
</table>