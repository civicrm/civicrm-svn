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
// FIXME: should we have different permission sets for create/update/delete?

function _civicrm_api3_permissions($entity, $action)
{
    $entity = strtolower($entity);
    $action = strtolower($action);
    $permissions = array(
        'activity' => array(
            'create' => array(),
            'delete' => array('delete activities'), // FIXME: XML does not enforce ‘delete activities’
            'get'    => array('view all activities'), // FIXME: XML does not enforce ‘view all activities’
            'update' => array(),
        ),
        'activity_type' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'address' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'case' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'case_activity' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'constant' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'contact' => array(
            'create' => array('add contacts'), // FIXME: XML does not enforce ‘add contacts’
            'delete' => array('delete contacts'), // FIXME: XML does not enforce ‘add contacts’
            'get'    => array('view all contacts'), // FIXME: XML does not enforce ‘view all contacts’
            'update' => array('add contacts', 'edit all contacts'), // FIXME: XML does not enforce ‘edit all contacts’
        ),
        'contribution' => array(
            'create' => array('access CiviContribute'),
            'delete' => array('access CiviContribute'),
            'get'    => array('access CiviContribute'),
            'update' => array('access CiviContribute'),
        ),
        'custom_field' => array(
            'create' => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
            'delete' => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
            'get'    => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
            'update' => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
        ),
        'custom_group' => array(
            'create' => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
            'delete' => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
            'get'    => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
            'update' => array('access all custom data'), // FIXME: XML does not enforce ‘access all custom data’
        ),
        'domain' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'email' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'entity' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'entity_file' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'entity_tag' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'event' => array(
            'create' => array('access CiviEvent'),
            'get'    => array('access CiviEvent'),
            'delete' => array('access CiviEvent'),
            'update' => array('access CiviEvent'),
        ),
        'file' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'files_by_entity' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'group' => array(
            'create' => array('edit groups'),
            'delete' => array('edit groups'), // FIXME: XML does not enforce ‘edit groups’
            'get'    => array(),
            'update' => array('edit groups'), // FIXME: XML does not enforce ‘edit groups’
        ),
        'group_contact' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'group_nesting' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'group_organization' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'location' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'membership' => array(
            'create' => array(), // FIXME: XML does not enforce ‘edit memberships’
            'delete' => array(), // FIXME: XML does not enforce ‘edit memberships’
            'get'    => array(),
            'update' => array(),
        ),
        'membership_payment' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'membership_status' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'membership_type' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'note' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'note_tree' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'option_group' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'option_value' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'participant' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'participant_payment' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'phone' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'pledge' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'pledge_payment' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'relationship' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'relationship_type' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'survey' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'survey_respondant' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'tag' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'tag_entities' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'uf_field' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'uf_group' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'uf_join' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'uf_match' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
        'website' => array(
            'create' => array(),
            'delete' => array(),
            'get'    => array(),
            'update' => array(),
        ),
    );

    $requested = $permissions[$entity][$action];

    // always require ‘access CiviCRM’
    if (!isset($requested)) $requested = array();
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
