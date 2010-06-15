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
{if $overlayProfile } 
{if ! empty( $profileFields_7 )}
<table>
<tr><td>
<div class="crm-summary-col-0">
  <div class="crm-section phone_1-section">
  	<div class="label">{$profileFields_7.phone_1.label}</div>
  	<div class="content">{$profileFields_7.phone_1.value}</div>
  	<div class="clear"></div>
  </div>

  <div class="crm-section phone_2-section">
  	<div class="label">{$profileFields_7.phone_2.label}</div>
  	<div class="content">{$profileFields_7.phone_2.value}</div>
  	<div class="clear"></div>
  </div>
  
  <div class="crm-section street_address_Primary-section">
  	<div class="label">{$profileFields_7.street_address_Primary.label}</div>
  	<div class="content">{$profileFields_7.street_address_Primary.value}<br />
  						{$profileFields_7.city_Primary.value} {$profileFields_7.state_province_Primary.value} {$profileFields_7.postal_code_Primary.value}
  	</div>
  	<div class="clear"></div>
  </div>
  <div class="crm-section email_Primary-section">
  	<div class="label">{$profileFields_7.email_Primary.label}</div>
  	<div class="content">{$profileFields_7.email_Primary.value}</div>
  	<div class="clear"></div>
  </div>
  <div class="crm-section website-section">
  	<div class="label">{$profileFields_7.website.label}</div>
  	<div class="content">{$profileFields_7.website.value}</div>
  	<div class="clear"></div>
  </div>

</div>
</td><td>
<div class="crm-summary-col-1">

  <div class="crm-section group-section">
  	<div class="label">{$profileFields_7.group.label}</div>
  	<div class="content">{$profileFields_7.group.value}</div>
  	<div class="clear"></div>
  </div>


  <div class="crm-section tag-section">
  	<div class="label">{$profileFields_7.tag.label}</div>
  	<div class="content">{$profileFields_7.tag.value}</div>
  	<div class="clear"></div>
  </div>


  <div class="crm-section gender-section">
  	<div class="label">{$profileFields_7.gender.label}</div>
  	<div class="content">{$profileFields_7.gender.value}</div>
  	<div class="clear"></div>
  </div>


  <div class="crm-section birth_date-section">
  	<div class="label">{$profileFields_7.birth_date.label}</div>
  	<div class="content">{$profileFields_7.birth_date.value}</div>
  	<div class="clear"></div>
  </div>
</div>
</td></tr>
</table> 
{/if} 
{* fields array is not empty *}
{/if}