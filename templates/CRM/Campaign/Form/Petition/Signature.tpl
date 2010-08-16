{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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


<div class="crm-block crm-form-block crm-petition-form-block">

{if !$contact_id}
(rem: Anonymous user only)
<div>{ts}Sign using your Facebook Account{/ts} <span style="background:blue;color:white;" title="it should be the button">F Connect</span><div>
<div id="nofb">{ts}Don't have a facebook account ? <a href="#signwithoutfb" id="signwithoutfb">Sign here</a>{/ts} </div>
{literal}
<script type="text/javascript">
    jQuery(document).ready(function($) 
    {
       $('.crm-group').hide();//not sure we need to hide the sign button
       $('#signwithoutfb').click( function(){$('.crm-group').slideDown();});
    });

</script>
{/literal}

{/if}
    <div class="crm-group">
    	{include file="CRM/Campaign/Form/Petition/Block.tpl" fields=$petitionContactProfile} 	
    </div>
    
    <div class="crm-group">
    	{include file="CRM/Campaign/Form/Petition/Block.tpl" fields=$petitionActivityProfile} 	
    </div>
	
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
</div>
