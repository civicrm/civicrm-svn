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

function _civicrm_api3_permissions($entity, $action)
{
    $entity = strtolower($entity);
    $action = strtolower($action);
    $permissions = array(
        'activity' => array(
            'create' => array(),
            'delete' => array('delete activities'),
            'get'    => array('view all activities'),
            'update' => array(),
        ),
        'address' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
        'contact' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
        'contribution' => array(
            'create' => array('access CiviContribute', 'edit contributions'),
            'delete' => array('access CiviContribute', 'delete in CiviContribute'),
            'get'    => array('access CiviContribute'),
            'update' => array('access CiviContribute', 'edit contributions'),
        ),
        'custom_field' => array(
            'create' => array('access all custom data'),
            'delete' => array('access all custom data'),
            'get'    => array('access all custom data'),
            'update' => array('access all custom data'),
        ),
        'custom_group' => array(
            'create' => array('access all custom data'),
            'delete' => array('access all custom data'),
            'get'    => array('access all custom data'),
            'update' => array('access all custom data'),
        ),
        'email' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
        'event' => array(
            'create' => array('access CiviEvent', 'edit all events'),
            'delete' => array('access CiviEvent', 'delete in CiviEvent'),
            'get'    => array('access CiviEvent', 'view event info'),
            'update' => array('access CiviEvent', 'edit all events'),
        ),
        'file' => array(
            'create' => array('access uploaded files'),
            'delete' => array('access uploaded files'),
            'get'    => array('access uploaded files'),
            'update' => array('access uploaded files'),
        ),
        'files_by_entity' => array(
            'create' => array('access uploaded files'),
            'delete' => array('access uploaded files'),
            'get'    => array('access uploaded files'),
            'update' => array('access uploaded files'),
        ),
        'group' => array(
            'create' => array('edit groups'),
            'delete' => array('edit groups'),
            'get'    => array(),
            'update' => array('edit groups'),
        ),
        'group_contact' => array(
            'create' => array('edit groups'),
            'delete' => array('edit groups'),
            'get'    => array(),
            'update' => array('edit groups'),
        ),
        'group_nesting' => array(
            'create' => array('edit groups'),
            'delete' => array('edit groups'),
            'get'    => array(),
            'update' => array('edit groups'),
        ),
        'group_organization' => array(
            'create' => array('edit groups'),
            'delete' => array('edit groups'),
            'get'    => array(),
            'update' => array('edit groups'),
        ),
        'location' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
        'membership' => array(
            'create' => array('access CiviMember', 'edit memberships'),
            'delete' => array('access CiviMember', 'delete in CiviMember'),
            'get'    => array('access CiviMember'),
            'update' => array('access CiviMember', 'edit memberships'),
        ),
        'membership_payment' => array(
            'create' => array('access CiviMember', 'edit memberships'),
            'delete' => array('access CiviMember', 'delete in CiviMember'),
            'get'    => array('access CiviMember'),
            'update' => array('access CiviMember', 'edit memberships'),
        ),
        'membership_status' => array(
            'create' => array('access CiviMember', 'edit memberships'),
            'delete' => array('access CiviMember', 'delete in CiviMember'),
            'get'    => array('access CiviMember'),
            'update' => array('access CiviMember', 'edit memberships'),
        ),
        'membership_type' => array(
            'create' => array('access CiviMember', 'edit memberships'),
            'delete' => array('access CiviMember', 'delete in CiviMember'),
            'get'    => array('access CiviMember'),
            'update' => array('access CiviMember', 'edit memberships'),
        ),
        'note' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
        'participant' => array(
            'create' => array('access CiviEvent', 'register for events'),
            'delete' => array('access CiviEvent', 'edit event participants'),
            'get'    => array('access CiviEvent', 'view event participants'),
            'update' => array('access CiviEvent', 'edit event participants'),
        ),
        'participant_payment' => array(
            'create' => array('access CiviEvent', 'register for events'),
            'delete' => array('access CiviEvent', 'edit event participants'),
            'get'    => array('access CiviEvent', 'view event participants'),
            'update' => array('access CiviEvent', 'edit event participants'),
        ),
        'phone' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
        'pledge' => array(
            'create' => array('access CiviPledge', 'edit pledges'),
            'delete' => array('access CiviPledge', 'delete in CiviPledge'),
            'get'    => array('access CiviPledge'),
            'update' => array('access CiviPledge', 'edit pledges'),
        ),
        'pledge_payment' => array(
            'create' => array('access CiviPledge', 'edit pledges'),
            'delete' => array('access CiviPledge', 'delete in CiviPledge'),
            'get'    => array('access CiviPledge'),
            'update' => array('access CiviPledge', 'edit pledges'),
        ),
        'website' => array(
            'create' => array('add contacts'),
            'delete' => array('delete contacts'),
            'get'    => array('view all contacts'),
            'update' => array('edit all contacts'),
        ),
    );

    $requested = isset($permissions[$entity][$action]) ? $permissions[$entity][$action] : array();

    // always require ‘access CiviCRM’
    $requested[] = 'access CiviCRM';

    return $requested;
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
