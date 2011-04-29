<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

function _civicrm_api3_permissions($entity, $action, &$params)
{
    $entity = strtolower($entity);
    $action = strtolower($action);
    $permissions = array(
        'activity' => array(
            'delete' => array('administer CiviCRM', 'delete activities'),
            'get'    => array('administer CiviCRM', 'view all activities'),
        ),
        'address' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
        'contact' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
        'contribution' => array(
            'create' => array('administer CiviCRM', 'access CiviContribute', 'edit contributions'),
            'delete' => array('administer CiviCRM', 'access CiviContribute', 'delete in CiviContribute'),
            'get'    => array('administer CiviCRM', 'access CiviContribute'),
            'update' => array('administer CiviCRM', 'access CiviContribute', 'edit contributions'),
        ),
        'custom_field' => array(
            'create' => array('administer CiviCRM', 'access all custom data'),
            'delete' => array('administer CiviCRM', 'access all custom data'),
            'get'    => array('administer CiviCRM', 'access all custom data'),
            'update' => array('administer CiviCRM', 'access all custom data'),
        ),
        'custom_group' => array(
            'create' => array('administer CiviCRM', 'access all custom data'),
            'delete' => array('administer CiviCRM', 'access all custom data'),
            'get'    => array('administer CiviCRM', 'access all custom data'),
            'update' => array('administer CiviCRM', 'access all custom data'),
        ),
        'email' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
        'event' => array(
            'create' => array('administer CiviCRM', 'access CiviEvent', 'edit all events'),
            'delete' => array('administer CiviCRM', 'access CiviEvent', 'delete in CiviEvent'),
            'get'    => array('administer CiviCRM', 'access CiviEvent', 'view event info'),
            'update' => array('administer CiviCRM', 'access CiviEvent', 'edit all events'),
        ),
        'file' => array(
            'create' => array('administer CiviCRM', 'access uploaded files'),
            'delete' => array('administer CiviCRM', 'access uploaded files'),
            'get'    => array('administer CiviCRM', 'access uploaded files'),
            'update' => array('administer CiviCRM', 'access uploaded files'),
        ),
        'files_by_entity' => array(
            'create' => array('administer CiviCRM', 'access uploaded files'),
            'delete' => array('administer CiviCRM', 'access uploaded files'),
            'get'    => array('administer CiviCRM', 'access uploaded files'),
            'update' => array('administer CiviCRM', 'access uploaded files'),
        ),
        'group' => array(
            'create' => array('administer CiviCRM', 'edit groups'),
            'delete' => array('administer CiviCRM', 'edit groups'),
            'update' => array('administer CiviCRM', 'edit groups'),
        ),
        'group_contact' => array(
            'create' => array('administer CiviCRM', 'edit groups'),
            'delete' => array('administer CiviCRM', 'edit groups'),
            'update' => array('administer CiviCRM', 'edit groups'),
        ),
        'group_nesting' => array(
            'create' => array('administer CiviCRM', 'edit groups'),
            'delete' => array('administer CiviCRM', 'edit groups'),
            'update' => array('administer CiviCRM', 'edit groups'),
        ),
        'group_organization' => array(
            'create' => array('administer CiviCRM', 'edit groups'),
            'delete' => array('administer CiviCRM', 'edit groups'),
            'update' => array('administer CiviCRM', 'edit groups'),
        ),
        'location' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
        'membership' => array(
            'create' => array('administer CiviCRM', 'access CiviMember', 'edit memberships'),
            'delete' => array('administer CiviCRM', 'access CiviMember', 'delete in CiviMember'),
            'get'    => array('administer CiviCRM', 'access CiviMember'),
            'update' => array('administer CiviCRM', 'access CiviMember', 'edit memberships'),
        ),
        'membership_payment' => array(
            'create' => array('administer CiviCRM', 'access CiviMember', 'edit memberships', 'access CiviContribute', 'edit contributions'),
            'delete' => array('administer CiviCRM', 'access CiviMember', 'delete in CiviMember', 'access CiviContribute', 'delete in CiviContribute'),
            'get'    => array('administer CiviCRM', 'access CiviMember', 'access CiviContribute'),
            'update' => array('administer CiviCRM', 'access CiviMember', 'edit memberships', 'access CiviContribute', 'edit contributions'),
        ),
        'membership_status' => array(
            'create' => array('administer CiviCRM', 'access CiviMember', 'edit memberships'),
            'delete' => array('administer CiviCRM', 'access CiviMember', 'delete in CiviMember'),
            'get'    => array('administer CiviCRM', 'access CiviMember'),
            'update' => array('administer CiviCRM', 'access CiviMember', 'edit memberships'),
        ),
        'membership_type' => array(
            'create' => array('administer CiviCRM', 'access CiviMember', 'edit memberships'),
            'delete' => array('administer CiviCRM', 'access CiviMember', 'delete in CiviMember'),
            'get'    => array('administer CiviCRM', 'access CiviMember'),
            'update' => array('administer CiviCRM', 'access CiviMember', 'edit memberships'),
        ),
        'note' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
        'participant' => array(
            'create' => array('administer CiviCRM', 'access CiviEvent', 'register for events'),
            'delete' => array('administer CiviCRM', 'access CiviEvent', 'edit event participants'),
            'get'    => array('administer CiviCRM', 'access CiviEvent', 'view event participants'),
            'update' => array('administer CiviCRM', 'access CiviEvent', 'edit event participants'),
        ),
        'participant_payment' => array(
            'create' => array('administer CiviCRM', 'access CiviEvent', 'register for events', 'access CiviContribute', 'edit contributions'),
            'delete' => array('administer CiviCRM', 'access CiviEvent', 'edit event participants', 'access CiviContribute', 'delete in CiviContribute'),
            'get'    => array('administer CiviCRM', 'access CiviEvent', 'view event participants', 'access CiviContribute'),
            'update' => array('administer CiviCRM', 'access CiviEvent', 'edit event participants', 'access CiviContribute', 'edit contributions'),
        ),
        'phone' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
        'pledge' => array(
            'create' => array('administer CiviCRM', 'access CiviPledge', 'edit pledges'),
            'delete' => array('administer CiviCRM', 'access CiviPledge', 'delete in CiviPledge'),
            'get'    => array('administer CiviCRM', 'access CiviPledge'),
            'update' => array('administer CiviCRM', 'access CiviPledge', 'edit pledges'),
        ),
        'pledge_payment' => array(
            'create' => array('administer CiviCRM', 'access CiviPledge', 'edit pledges', 'access CiviContribute', 'edit contributions'),
            'delete' => array('administer CiviCRM', 'access CiviPledge', 'delete in CiviPledge', 'access CiviContribute', 'delete in CiviContribute'),
            'get'    => array('administer CiviCRM', 'access CiviPledge', 'access CiviContribute'),
            'update' => array('administer CiviCRM', 'access CiviPledge', 'edit pledges', 'access CiviContribute', 'edit contributions'),
        ),
        'website' => array(
            'create' => array('administer CiviCRM', 'add contacts'),
            'delete' => array('administer CiviCRM', 'delete contacts'),
            'get'    => array('administer CiviCRM', 'view all contacts'),
            'update' => array('administer CiviCRM', 'edit all contacts'),
        ),
    );

    // let third parties modify the permissions
    require_once 'CRM/Utils/Hook.php';
    CRM_Utils_Hook::alterAPIPermissions($entity, $action, $params, $permissions);

    return isset($permissions[$entity][$action]) ? $permissions[$entity][$action] : array('administer CiviCRM');
}

# FIXME: not sure how to permission the following API 3 calls:
# contribution_transact (make online contributions)
# entity_tag_display
# group_contact_pending
# group_contact_update_status
# mailing_event_bounce
# mailing_event_click
# mailing_event_confirm
# mailing_event_forward
# mailing_event_open
# mailing_event_reply
# mailing_group_event_domain_unsubscribe
# mailing_group_event_resubscribe
# mailing_group_event_subscribe
# mailing_group_event_unsubscribe
# membership_status_calc
# survey_respondant_count
