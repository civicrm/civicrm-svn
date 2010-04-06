<?php
/** 
 * Project:     CiviCRM: Constituent Relationship Management for NP's
 * File:        civicrm_user.inc 
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 */ 

/**
 * Drupal module include file.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */


/**
 * User hooks for civicrm module
 *
 * Note that we ignore the edit field and do not follow the drupal protocol
 * of extracting values from the edit field. We extract the fields directly
 * from $_POST. This is because the underlying form package that we use
 * (HTML_QuickForm) does the form value extraction and validation.
 *
 * @abstractparam array  $edit     The array of form values submitted by the user.
 * @param object $user     The user object on which the operation is being performed.
 * @param object $category The active category of user information being edited.
 *
 * @return mixed           depends on the operation being performed
 */

/**
 * Implementation of hook_user_login (D7)
 */
function civicrm_user_login( &$edit, $user)
{
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    require_once 'CRM/Core/BAO/UFMatch.php';
    return CRM_Core_BAO_UFMatch::synchronize( $user, false, 'Drupal',
                                              civicrm_get_ctype( 'Individual' ) );  
}

/**
 * Implementation of hook_user_insert
 */
function civicrm_user_insert( &$edit, &$user, $category = NULL ) 
{
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    $config = CRM_Core_Config::singleton( );
    if ( $config->inCiviCRM ) {
        return;
    }

    // did civicrm generate this page, or is it via a user hook?
    if ( civicrm_on_user_page( ) ) {
        civicrm_register_data( $edit, $user, $category, false );
    } else {
        require_once 'CRM/Core/BAO/UFMatch.php';
        CRM_Core_BAO_UFMatch::synchronize( $user, false, 'Drupal',
                                           civicrm_get_ctype( 'Individual' ) );
    }
    return;
}

/**
 * Implementation of hook_user_insert
 */
function civicrm_user_update( &$edit, &$user, $category ) 
{
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    // this always comes in via user hook, so udpate email only if it has changed
    if ( isset( $edit['mail'] ) &&
         trim( $edit['mail'] ) != trim( $user->mail ) ) {
        require_once 'CRM/Core/BAO/UFMatch.php';
        $contactID = CRM_Core_BAO_UFMatch::getContactId( $user->uid );

        // cant find the contactID, so lets skip
        if ( ! $contactID ) {
            return;
        }
        CRM_Core_BAO_UFMatch::updateContactEmail( $contactID,
                                                  trim( $edit['mail'] ) );
    }
}

/**
 * Implementation of hook_user_insert
 */
function civicrm_user_delete( &$edit, &$user, $category ) 
{
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    require_once 'CRM/Core/BAO/UFMatch.php';
    CRM_Core_BAO_UFMatch::deleteUser( $user->uid );
}


/**
 * Implementation of hook_user_insert
 */
function civicrm_user_categories() 
{
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    global $user;
    return civicrm_categories( $user );
}


/**
 * Implementation of hook_user_view
 *
 * @todo I suspect that some of the stuff done in the old form_alter handler
 *       should live here instead under D7
 */
function civicrm_user_view( $user, $view_mode ){
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    return civicrm_view_data( $user );
}


/**
 * The remaining functions are manipulations via the Forms API, which
 * have supplanted the old hook_user ops.
 */

/**
 * bottleneck for D7 specific form_alter handling
 */
function civicrm_form_alter_d7( &$form, $formValues, $formID )
{
  switch ($formID) {
    case 'user_register_form':
      $config = CRM_Core_Config::singleton( );
      if ( $config->inCiviCRM ) {
          return;
      }
      $output = array();
      if ( empty( $_POST ) ) {
          $output = civicrm_register_data($edit, $user, $category, true, false );
      } else {
          $output = civicrm_register_data( $edit, $user, $category, false, true );
      }
      if (!empty($output)) {
        $form = array_merge($form, $output);
        
      }
      break;
    case 'user_profile_form':
      $inCategory = true;
      if ( $category ) {
          $inCategory = false;
          $categories = civicrm_categories( $account );
          foreach ( $categories as $cat ) {
              if ( $category == $cat['name'] ) {
                  $inCategory = true;
                  break;
              }
          }
      }
      
      // only return a form to drupal my account page
      $output = array();
      if ( $inCategory      &&
           arg(0) == 'user' &&
           arg(2) == 'edit' &&
           arg(3) ) {
          $output = civicrm_form_data($edit, $user, $category, true );
          if (!empty($output))
            $form = array_merge($form, $output);
      }
      break;
    
    default:
      break;    
  }
  
}


/**
 * Validation functions so CiviCRM can get at these items
 */

function civicrm_validate_user_forms( $form, &$form_state )
{
    if ( ! civicrm_initialize( ) ) {
        return;
    }
    $edit = array(); //we ignore this
    $category = FALSE; //this as well
    global $user;
    civicrm_validate_data($edit, $user, $category);
}

