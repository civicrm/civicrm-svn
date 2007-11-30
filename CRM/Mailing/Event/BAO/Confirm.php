<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'Mail/mime.php';

require_once 'CRM/Mailing/Event/DAO/Confirm.php';

class CRM_Mailing_Event_BAO_Confirm extends CRM_Mailing_Event_DAO_Confirm {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Confirm a pending subscription
     *
     * @param int $contact_id       The id of the contact
     * @param int $subscribe_id     The id of the subscription event
     * @param string $hash          The hash
     * @return boolean              True on success
     * @access public
     * @static
     */
    public static function confirm($contact_id, $subscribe_id, $hash) {
        $se =& CRM_Mailing_Event_BAO_Subscribe::verify($contact_id,
                                            $subscribe_id, $hash);
        
        if (! $se) {
            return false;
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        $ce =& new CRM_Mailing_Event_BAO_Confirm();
        $ce->event_subscribe_id = $se->id;
        $ce->time_stamp = date('YmdHis');
        $ce->save();
        
        CRM_Contact_BAO_GroupContact::updateGroupMembershipStatus(
                $contact_id, $se->group_id,'Email',$ce->id);
        
        $transaction->commit( );

        $config =& CRM_Core_Config::singleton();
        $domain =& CRM_Mailing_Event_BAO_Subscribe::getDomain($subscribe_id);
        
        list($display_name, $email) =
                CRM_Contact_BAO_Contact::getEmailDetails($se->contact_id);
                
        $group =& new CRM_Contact_DAO_Group();
        $group->id = $se->group_id;
        $group->find(true);
        
        require_once 'CRM/Mailing/BAO/Component.php';
        $component =& new CRM_Mailing_BAO_Component();
        $component->domain_id = $domain->id;
        $component->is_default = 1;
        $component->is_active = 1;
        $component->component_type = 'Welcome';

        $component->find(true);
        
        $headers = array(
            'Subject'   => $component->subject,
            'From'      => ts('"%1" <do-not-reply@%2>',
                            array(  1 => $domain->email_name,
                                    2 => $domain->email_domain)),
            'To'        => $email,
            'Reply-To'  => "do-not-reply@{$domain->email_domain}",
            'Return-Path'  => "do-not-reply@{$domain->email_domain}",
        );

        $html = $component->body_html;

        if ($component->body_text) {
            $text = $component->body_text;
        } else {
            $text = CRM_Utils_String::htmlToText($component->body_html);
        }

        require_once 'CRM/Mailing/BAO/Mailing.php';
        $bao =& new CRM_Mailing_BAO_Mailing();
        $bao->body_text = $text;
        $bao->body_html = $html;
        $tokens = $bao->getTokens();

        require_once 'CRM/Utils/Token.php';
        $html = CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['html'] );
        $html = CRM_Utils_Token::replaceWelcomeTokens($html, $group->title, true);

        $text = CRM_Utils_Token::replaceDomainTokens($text, $domain, false, $tokens['text'] );
        $text = CRM_Utils_Token::replaceWelcomeTokens($text, $group->title, false);

        $message =& new Mail_Mime("\n");
        $message->setHTMLBody($html);
        $message->setTxtBody($text);
        $b = $message->get();
        $h = $message->headers($headers);
        $mailer =& $config->getMailer();

        require_once 'CRM/Mailing/BAO/Mailing.php';
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,
                                array('CRM_Mailing_BAO_Mailing', 'catchSMTP'));
        $mailer->send($email, $h, $b);
        CRM_Core_Error::setCallback();
        
        return $group->title;
    }
}

?>
