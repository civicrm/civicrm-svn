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

// FIXME: auto-generate from XML?
// FIXME: should we permission getfields calls?
// FIXME: should ‘access CiviCRM’ be mandatory for all calls?
// FIXME: should we have different permission sets for create/update/delete?

function _civicrm_api3_permissions($entity, $action)
{
    $permissions = array(
        'activity' => array(
            'create' => array('all' => array('access CiviCRM')),
            'delete' => array('all' => array('access CiviCRM', 'delete activities')), // FIXME: XML does not enforce ‘delete activities’
            'get'    => array('all' => array('access CiviCRM', 'view all activities')), // FIXME: XML does not enforce ‘view all activities’
            'update' => array('all' => array('access CiviCRM')),
        ),
        'activity_type' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'address' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'case' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'case_activity' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'constant' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'contact' => array(
            'create' => array('all' => array('access CiviCRM', 'add contacts')), // FIXME: XML does not enforce ‘add contacts’
            'delete' => array('all' => array('access CiviCRM', 'delete contacts')), // FIXME: XML does not enforce ‘add contacts’
            'get'    => array('all' => array('access CiviCRM', 'view all contacts')), // FIXME: XML does not enforce ‘view all contacts’
            'update' => array('all' => array('access CiviCRM', 'add contacts', 'edit all contacts')), // FIXME: XML does not enforce ‘edit all contacts’
        ),
        'contribution' => array(
            'create' => array('all' => array('access CiviContribute')),
            'delete' => array('all' => array('access CiviContribute')),
            'get'    => array('all' => array('access CiviContribute')),
            'update' => array('all' => array('access CiviContribute')),
        ),
        'custom_field' => array(
            'create' => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
            'delete' => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
            'get'    => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
            'update' => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
        ),
        'custom_group' => array(
            'create' => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
            'delete' => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
            'get'    => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
            'update' => array('all' => array('access all custom data')), // FIXME: XML does not enforce ‘access all custom data’
        ),
        'domain' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'email' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'entity' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'entity_file' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'entity_tag' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'event' => array(
            'create' => array('all' => array('access CiviEvent')),
            'get'    => array('all' => array('access CiviEvent')),
            'delete' => array('all' => array('access CiviEvent')),
            'update' => array('all' => array('access CiviEvent')),
        ),
        'file' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'files_by_entity' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'group' => array(
            'create' => array('all' => array('edit groups')),
            'delete' => array('all' => array('edit groups')), // FIXME: XML does not enforce ‘edit groups’
            'get'    => array('all' => array('access CiviCRM')),
            'update' => array('all' => array('edit groups')), // FIXME: XML does not enforce ‘edit groups’
        ),
        'group_contact' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'group_nesting' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'group_organization' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'location' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'membership' => array(
            'create' => array('all' => array('access CiviMember', 'edit memberships')), // FIXME: XML does not enforce ‘edit memberships’
            'delete' => array('all' => array('access CiviMember', 'edit memberships')), // FIXME: XML does not enforce ‘edit memberships’
            'get'    => array('all' => array('access CiviMember')),
            'update' => array('all' => array('access CiviMember', 'edit memberships')),
        ),
        'membership_payment' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'membership_status' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'membership_type' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'note' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'note_tree' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'option_group' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'option_value' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'participant' => array(
            'create' => array('all' => array('access CiviEvent')),
            'delete' => array('all' => array('access CiviEvent')),
            'get'    => array('all' => array('access CiviEvent', 'view event participants')),
            'update' => array('all' => array('access CiviEvent')),
        ),
        'participant_payment' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'phone' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'pledge' => array(
            'create' => array('all' => array('access CiviPledge')),
            'delete' => array('all' => array('access CiviPledge')),
            'get'    => array('all' => array('access CiviPledge')),
            'update' => array('all' => array('access CiviPledge')),
        ),
        'pledge_payment' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'relationship' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'relationship_type' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'survey' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'survey_respondant' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'tag' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'tag_entities' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'uf_field' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'uf_group' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'uf_join' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'uf_match' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
        'website' => array(
            'create' => array('all' => array()),
            'delete' => array('all' => array()),
            'get'    => array('all' => array()),
            'update' => array('all' => array()),
        ),
    );
    return $permissions[$entity][$action];
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
