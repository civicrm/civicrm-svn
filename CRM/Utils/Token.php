<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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
 * $Id: $
 *
 */

/**
 * Class to abstract token replacement 
 */
class CRM_Utils_Token 
{
    static $_requiredTokens = null;
    
    static $_tokens = array( 'action'        => array( 
                                                      'forward', 
                                                      'optOut',
                                                      'optOutUrl',
                                                      'reply', 
                                                      'unsubscribe',
                                                      'unsubscribeUrl',
                                                      'resubscribe',
                                                      'resubscribeUrl',
                                                      'subscribeUrl'
                                                      ),
                             'mailing'       => array(
                             						  'id',
                                                      'name',
                                                      'group',
                                                      'subject',
                                                      'viewUrl',
                                                      'editUrl',
                                                      'scheduleUrl',
                                                      'approvalStatus',
                                                      'approvalNote',
                                                      'approveUrl',
                                                      'creator',
                                                      'creatorEmail'
                                                      ),
                             'user'          => array(
                                                      // we extract the stuff after the role / permission and return the
                                                      // civicrm email addresses of all users with that role / permission
                                                      // useful with rules integration
                                                      'permission:',
                                                      'role:',
                                                      ),
                             'contact'       => null,  // populate this dynamically
                             'domain'        => array( 
                                                      'name', 
                                                      'phone', 
                                                      'address', 
                                                      'email'
                                                      ),
                             'subscribe'     => array(
                                                      'group'
                                                      ),
                             'unsubscribe'   => array(
                                                      'group'
                                                      ),
                             'resubscribe'   => array(
                                                      'group'
                                                      ),
                             'welcome'       => array(
                                                      'group'
                                                      ),
                             );
    
    /**
     * Check a string (mailing body) for required tokens.
     *
     * @param string $str           The message
     * @return true|array           true if all required tokens are found,
     *                              else an array of the missing tokens
     * @access public
     * @static
     */
    public static function requiredTokens(&$str) 
    {
        if (self::$_requiredTokens == null) {
            self::$_requiredTokens = array (    
                                            'domain.address' => ts("Domain address - displays your organization's postal address."),
                                            'action.optOutUrl or action.subscribeUrl'  =>
                                            array(
                                                  'action.optOut'      => ts("'Opt out via email' - displays an email address for recipients to opt out of receiving emails from your organization."), 
                                                  'action.optOutUrl'   => ts("'Opt out via web page' - creates a link for recipients to click if they want to opt out of receiving emails from your organization. Alternatively, you can include the 'Opt out via email' token."),
                                                  'action.unsubscribe' => ts("'Unsubscribe via email' - displays an email address for recipients to unsubscribe from the specific mailing list used to send this message."),
                                                  'action.unsubscribe' => ts("'Unsubscribe via web page' - creates a link for recipients to unsubscribe from the specific mailing list used to send this message. Alternatively, you can include the 'Unsubscribe via email' token or one of the Opt-out tokens."),
                                                  ),
                                            );
        }

        $missing = array( );
        foreach (self::$_requiredTokens as $token => $value) {
            if ( ! is_array( $value ) ) {
                if (! preg_match('/(^|[^\{])'.preg_quote('{' . $token . '}').'/', $str ) ) {
                    $missing[$token] = $value;
                }
            } else {
                $present = false;
                $desc    = null;
                foreach ( $value as $t => $d ) {
                    $desc = $d;
                    if ( preg_match('/(^|[^\{])'.preg_quote('{' . $t . '}').'/', $str ) ) {
                        $present = true;
                    }
                }
                if ( ! $present ) {
                    $missing[$token] = $desc;
                }
            }
        }

        if (empty($missing)) {
            return true;
        }
        return $missing;
    }
    
    /**
     * Wrapper for token matching
     *
     * @param string $type      The token type (domain,mailing,contact,action)
     * @param string $var       The token variable
     * @param string $str       The string to search
     * @return boolean          Was there a match
     * @access public
     * @static
     */
    public static function token_match($type, $var, &$str) 
    {
        $token  = preg_quote('{' . "$type.$var") 
            . '(\|.+?)?' . preg_quote('}');
        return preg_match("/(^|[^\{])$token/", $str);
    }
    
    /**
     * Wrapper for token replacing
     *
     * @param string $type      The token type
     * @param string $var       The token variable
     * @param string $value     The value to substitute for the token
     * @param string (reference) $str       The string to replace in
     * @return string           The processed string
     * @access public
     * @static
     */
    public static function &token_replace($type, $var, $value, &$str, $escapeSmarty = false) 
    {
        $token  = preg_quote('{' . "$type.$var") 
            . '(\|([^\}]+?))?' . preg_quote('}');
        if ( !$value ) {
            $value = '$3';
        }
        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }
        $str = preg_replace("/([^\{])?$token/", "\${1}$value", $str);
        return $str;
    }
    
    /**
     * get< the regex for token replacement
     *
     * @param string $key       a string indicating the the type of token to be used in the expression
     * @return string           regular expression sutiable for using in preg_replace
     * @access private
     * @static
     */
    private static function tokenRegex($token_type)
    {
        return '/(?<!\{|\\\\)\{'.$token_type.'\.([\w]+(\-[\w\s]+)?)\}(?!\})/e';
    }

    /**
     * escape the string so a malicious user cannot inject smarty code into the template
     *
     * @param string $string    a string that needs to be escaped from smarty parsing
     * @return string           the escaped string
     * @access private
     * @static
     */
    private static function tokenEscapeSmarty($string)
    {
        // need to use negative look-behind, as both str_replace() and preg_replace() are sequential
        return preg_replace(array('/{/', '/(?<!{ldelim)}/'), array('{ldelim}', '{rdelim}'), $string);
    }

    /**
    /**
     * Replace all the domain-level tokens in $str
     *
     * @param string $str       The string with tokens to be replaced
     * @param object $domain    The domain BAO
     * @param boolean $html     Replace tokens with HTML or plain text
     * @return string           The processed string
     * @access public
     * @static
     */
    public static function &replaceDomainTokens($str, &$domain, $html = false, $knownTokens = null, $escapeSmarty = false) 
    {
        $key = 'domain';
        if ( ! $knownTokens ||
             ! CRM_Utils_Array::value( $key, $knownTokens ) ) {
            return $str;
        }

        $str = preg_replace(self::tokenRegex($key),'self::getDomainTokenReplacement(\'\\1\',$domain,$html)',$str);
        return $str;
    }

    public static function getDomainTokenReplacement($token, &$domain, $html = false, $escapeSmarty = false)
    {
        // check if the token we were passed is valid
        // we have to do this because this function is
        // called only when we find a token in the string

        $loc =& $domain->getLocationValues();

        if ( !in_array($token, self::$_tokens['domain']) ) {
            $value = "{domain.$token}";
        } else if ($token == 'address') {
            static $addressCache = array();
            
            $cache_key = $html ? 'address-html' : 'address-text';
            if ( array_key_exists($cache_key, $addressCache) ) {
                return $addressCache[$cache_key];
            }
            
            require_once 'CRM/Utils/Address.php';
            $value = null;
            /* Construct the address token */
            if ( CRM_Utils_Array::value( $token, $loc ) ) {
                if ( $html ) { 
                    $value = $loc[$token][1]['display'];
                   $value = str_replace("\n", '<br />', $value);
                } else {
                    $value = $loc[$token][1]['display_text'];
                }
                $addressCache[$cache_key] = $value;
            }
        } else if ( $token == 'name' || $token == 'id' ) {
            $value = $domain->$token;
        } else if($token == 'phone' || $token == 'email'){
            /* Construct the phone and email tokens */
            $value = null;
            if ( CRM_Utils_Array::value( $token, $loc ) ) {
                foreach ($loc[$token] as $index => $entity) {
                    $value = $entity[$token];
                    break;
                }
            }
        }

        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }

        return $value;      
    }
    
    /**
     * Replace all the org-level tokens in $str
     *
     * @param string $str       The string with tokens to be replaced
     * @param object $org       Associative array of org properties
     * @param boolean $html     Replace tokens with HTML or plain text
     * @return string           The processed string
     * @access public
     * @static
     */
    public static function &replaceOrgTokens($str, &$org, $html = false, $escapeSmarty = false) {
        self::$_tokens['org'] =
            array_merge( array_keys( CRM_Contact_BAO_Contact::importableFields( 'Organization' ) ),
                         array( 'address', 'display_name', 'checksum', 'contact_id' ) );

        $cv = null;
        foreach (self::$_tokens['org'] as $token) {
            // print "Getting token value for $token<br/><br/>";
            if ($token == '') {
                continue;
            }

            /* If the string doesn't contain this token, skip it. */
            if (! self::token_match('org', $token, $str)) {
                continue;
            }

            /* Construct value from $token and $contact */
            $value = null;
            
            if ($cfID = CRM_Core_BAO_CustomField::getKeyID($token)) {
                // only generate cv if we need it
                if ( $cv === null ) {
                    $cv = CRM_Core_BAO_CustomValue::getContactValues($org['contact_id']);
                }
                foreach ($cv as $cvFieldID => $value ) {
                    if ($cvFieldID == $cfID) {
                        $value = CRM_Core_BAO_CustomOption::getOptionLabel($cfID, $value );
                        break;
                    }
                }
            } else if ( $token == 'checksum' ) {
                require_once 'CRM/Contact/BAO/Contact/Utils.php';
                $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum( $org['contact_id'] );
                $value = "cs={$cs}";
            } else if ( $token == 'address' ) {
                /* Build the location values array */
                $loc = array( );
                $loc['display_name'] = CRM_Utils_Array::retrieveValueRecursive( $org, 'display_name' );
                $loc['street_address'] = CRM_Utils_Array::retrieveValueRecursive( $org, 'street_address' );
                $loc['city'] = CRM_Utils_Array::retrieveValueRecursive( $org, 'city' );
                $loc['state_province'] = CRM_Utils_Array::retrieveValueRecursive( $org, 'state_province' );
                $loc['postal_code'] = CRM_Utils_Array::retrieveValueRecursive( $org, 'postal_code' );
                
                /* Construct the address token */
                $value = CRM_Utils_Address::format( $loc );
                if ( $html ) {
                    $value = str_replace( "\n", '<br />', $value );
                }
            } else {
                $value = CRM_Utils_Array::retrieveValueRecursive( $org, $token );
            }
            
            self::token_replace('org', $token, $value, $str, $escapeSmarty);
        }

        return $str;
    }

    /**
     * Replace all mailing tokens in $str
     *
     * @param string $str       The string with tokens to be replaced
     * @param object $mailing   The mailing BAO, or null for validation
     * @param boolean $html     Replace tokens with HTML or plain text
     * @return string           The processed sstring
     * @access public
     * @static
     */
    public static function &replaceMailingTokens($str, &$mailing, $html = false, $knownTokens = null, $escapeSmarty = false) 
    {
        $key = 'mailing';
        if ( ! $knownTokens ||
             ! isset( $knownTokens[$key] ) ) {
            return $str;
        }
        
        $str = preg_replace( self::tokenRegex($key),
                             'self::getMailingTokenReplacement(\'\\1\',$mailing,$escapeSmarty)', $str );
        return $str;
    }

    public static function getMailingTokenReplacement($token, &$mailing, $escapeSmarty = false) 
    {
        $value = '';
        switch ( $token ) {
        // CRM-7663
        case 'id':
        	$value = $mailing ? $mailing->id : 'undefined';
        	break;
        case 'name':
            $value = $mailing ? $mailing->name : 'Mailing Name';
            break;

        case 'group':
            $groups = $mailing  ? $mailing->getGroupNames() : array('Mailing Groups');
            $value = implode(', ', $groups);
            break;

        case 'subject':
            $value = $mailing->subject;
            break;

        case 'viewUrl':
            $value = CRM_Utils_System::url( 'civicrm/mailing/view',
                                            "reset=1&id={$mailing->id}",
                                            true, null, false, true );
            break;

        case 'editUrl':
            $value = CRM_Utils_System::url( 'civicrm/mailing/send',
                                            "reset=1&mid={$mailing->id}&continue=true",
                                            true, null, false, true );
            break;
            
        case 'scheduleUrl':
            $value = CRM_Utils_System::url( 'civicrm/mailing/schedule',
                                            "reset=1&mid={$mailing->id}",
                                            true, null, false, true );
            break;
            
        case 'html':
            require_once 'CRM/Mailing/Page/View.php';
            $page = new CRM_Mailing_Page_View( );
            $value = $page->run( $mailing->id, null, false );
            break;
            
        case 'approvalStatus':
            require_once 'CRM/Mailing/PseudoConstant.php';
            $mailApprovalStatus = CRM_Mailing_PseudoConstant::approvalStatus( );
            $value = $mailApprovalStatus[$mailing->approval_status_id];
            break;
    
        case 'approvalNote':
            $value = $mailing->approval_note;
            break;

        case 'approveUrl':
            $value = CRM_Utils_System::url( 'civicrm/mailing/approve',
                                            "reset=1&mid={$mailing->id}",
                                            true, null, false, true );
            break;
            
        case 'creator':
            $value = CRM_Contact_BAO_Contact::displayName( $mailing->created_id );
            break;

        case 'creatorEmail':
            $value = CRM_Contact_BAO_Contact::getPrimaryEmail( $mailing->created_id );
            break;
            
        default:
            $value = "{mailing.$token}";
            break;
        }
     
        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }     
        return $value;
    }

    /**
     * Replace all action tokens in $str
     *
     * @param string $str         The string with tokens to be replaced
     * @param array $addresses    Assoc. array of VERP event addresses
     * @param array $urls         Assoc. array of action URLs
     * @param boolean $html       Replace tokens with HTML or plain text
     * @param array $knownTokens  A list of tokens that are known to exist in the email body
     * @return string             The processed string
     * @access public
     * @static
     */
    public static function &replaceActionTokens($str, &$addresses, &$urls, $html = false, $knownTokens = null, $escapeSmarty = false) 
    {
        $key = 'action';
        // here we intersect with the list of pre-configured valid tokens
        // so that we remove anything we do not recognize
        // I hope to move this step out of here soon and
        // then we will just iterate on a list of tokens that are passed to us
        if ( ! $knownTokens || ! CRM_Utils_Array::value( $key,$knownTokens ) ) {
            return $str;
        }
        
        $str = preg_replace( self::tokenRegex($key),
                             'self::getActionTokenReplacement(\'\\1\',$addresses,$urls,$escapeSmarty)',
                             $str);
        return $str;
    }
    
    public static function getActionTokenReplacement($token, &$addresses, &$urls, $html = false, $escapeSmarty = false) 
    {
        /* If the token is an email action, use it.  Otherwise, find the
         * appropriate URL */
        if ( !in_array( $token, self::$_tokens['action']) ) {
            $value = "{action.$token}";
        } else {
            $value = CRM_Utils_Array::value($token, $addresses);

            if ( $value == null ) {
                $value = CRM_Utils_Array::value($token, $urls);
            }
            
            if ( $value && $html ) {
                //fix for CRM-2318
                if ( (substr( $token, -3 ) != 'Url') && ($token != 'forward') ) {
                    $value = "mailto:$value";
                } 
            } else if ($value && !$html) {
                $value = str_replace('&amp;', '&', $value);
            }

        }

        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }
        return $value;
    }


    /**
     * Replace all the contact-level tokens in $str with information from
     * $contact.
     *
     * @param string  $str               The string with tokens to be replaced
     * @param array   $contact           Associative array of contact properties
     * @param boolean $html              Replace tokens with HTML or plain text
     * @param array   $knownTokens       A list of tokens that are known to exist in the email body
     * @param boolean $returnBlankToken  return unevaluated token if value is null
     * @return string                    The processed string
     * @access public
     * @static
     */
    public static function &replaceContactTokens($str, &$contact, $html = false, $knownTokens = null,
                                                 $returnBlankToken = false, $escapeSmarty = false ) {
        $key = 'contact';
        if (self::$_tokens[$key] == null) {
            /* This should come from UF */
            self::$_tokens[$key] =
                array_merge( array_keys(CRM_Contact_BAO_Contact::exportableFields('All') ),
                             array( 'checksum', 'contact_id' ) );
        }
        
        // here we intersect with the list of pre-configured valid tokens
        // so that we remove anything we do not recognize
        // I hope to move this step out of here soon and
        // then we will just iterate on a list of tokens that are passed to us
        if ( !$knownTokens || ! CRM_Utils_Array::value( $key, $knownTokens ) ) return $str;

        $str = preg_replace(self::tokenRegex($key),
                            'self::getContactTokenReplacement(\'\\1\', $contact, $html, $returnBlankToken, $escapeSmarty)',
                            $str);
       
        $str = preg_replace( '/\\\\|\{(\s*)?\}/', ' ', $str );
        return $str;
    }
    
    public function getContactTokenReplacement($token, &$contact, $html = false,
                                               $returnBlankToken = false, $escapeSmarty = false )
    {
        if (self::$_tokens['contact'] == null) {
            /* This should come from UF */
            self::$_tokens['contact'] =
                array_merge( array_keys(CRM_Contact_BAO_Contact::exportableFields( 'All' ) ),
                             array( 'checksum', 'contact_id' ) );
        }
        
        /* Construct value from $token and $contact */
        $value = null;
        
        // check if the token we were passed is valid
        // we have to do this because this function is
        // called only when we find a token in the string
            
        if (!in_array($token,self::$_tokens['contact'])) {
            $value = "{contact.$token}";
        } else if ( $token == 'checksum' ) {
            require_once 'CRM/Contact/BAO/Contact/Utils.php';
            $hash = CRM_Utils_Array::value( 'hash', $contact );
            if ( $hash ) {
                $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum( $contact['contact_id'],
                                                                       null,
                                                                       null,
                                                                       $hash );
                $value = "cs={$cs}";
            } else if ( $contact['contact_id'] ) {
                $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum( $contact['contact_id'] );
                $value = "cs={$cs}";
            }
        } else {
            $value = CRM_Utils_Array::retrieveValueRecursive($contact, $token);
        }

        if (!$html) {
            $value = str_replace('&amp;', '&', $value);
        }
        
        // if null then return actual token
        if ( $returnBlankToken && !$value ) {
            $value = "{contact.$token}";
        }
        
        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }

        return $value;
    }

    /**
     * Replace all the hook tokens in $str with information from
     * $contact.
     *
     * @param string $str         The string with tokens to be replaced
     * @param array $contact      Associative array of contact properties (including hook token values)
     * @param boolean $html       Replace tokens with HTML or plain text
     * @return string             The processed string
     * @access public
     * @static
     */
    public static function &replaceHookTokens($str, &$contact, &$categories, $html = false, $escapeSmarty = false ) {

        foreach ( $categories as $key ) {
            $str = preg_replace(self::tokenRegex($key),
                                'self::getHookTokenReplacement(\'\\1\', $contact, $key, $html, $escapeSmarty)',
                                $str);
        }
        return $str;
    }
    
    public function getHookTokenReplacement( $token, &$contact, $category, $html = false, $escapeSmarty = false )
    {
        $value = CRM_Utils_Array::value( "{$category}.{$token}", $contact );

        if ( $value &&
             ! $html ) {
            $value = str_replace('&amp;', '&', $value);
        }
        
        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }
        
        return $value;
    }

    /**
     *  unescapeTokens removes any characters that caused the replacement routines to skip token replacement
     *  for example {{token}}  or \{token}  will result in {token} in the final email
     *
     *  this routine will remove the extra backslashes and braces
     *
     *  @param $str ref to the string that will be scanned and modified
     *  @return void  this function works directly on the string that is passed
     *  @access public
     *  @static
     */
     public static function unescapeTokens(&$str)
     {
         $str = preg_replace('/\\\\|\{(\{\w+\.\w+\})\}/','\\1',$str);
     }
     
    /**
     * Replace unsubscribe tokens
     *
     * @param string $str           the string with tokens to be replaced
     * @param object $domain        The domain BAO
     * @param array $groups         The groups (if any) being unsubscribed
     * @param boolean $html         Replace tokens with html or plain text
     * @param int $contact_id       The contact ID
     * @param string hash           The security hash of the unsub event
     * @return string               The processed string
     * @access public
     * @static
     */
     public static function &replaceUnsubscribeTokens($str, &$domain, &$groups, $html,
                                                      $contact_id, $hash) 
     {
        if (self::token_match('unsubscribe', 'group', $str)) {
            if ( !empty($groups) ) {
                $config = CRM_Core_Config::singleton();
                $base = CRM_Utils_System::baseURL();

                // FIXME: an ugly hack for CRM-2035, to be dropped once CRM-1799 is implemented
                require_once 'CRM/Contact/DAO/Group.php';
                $dao = new CRM_Contact_DAO_Group();
                $dao->find();
                while ($dao->fetch()) {
                    if (substr($dao->visibility, 0, 6) == 'Public') {
                        $visibleGroups[] = $dao->id;
                    }
                }
                $value = implode(', ', $groups);
                self::token_replace('unsubscribe', 'group', $value, $str);
            }
        }
        return $str;
    }

    /**
     * Replace resubscribe tokens
     *
     * @param string $str           the string with tokens to be replaced
     * @param object $domain        The domain BAO
     * @param array $groups         The groups (if any) being resubscribed
     * @param boolean $html         Replace tokens with html or plain text
     * @param int $contact_id       The contact ID
     * @param string hash           The security hash of the resub event
     * @return string               The processed string
     * @access public
     * @static
     */
    public static function &replaceResubscribeTokens($str, &$domain, &$groups, $html,
                                                     $contact_id, $hash) 
    {
        if (self::token_match('resubscribe', 'group', $str)) {
            if (! empty($groups)) {
                $value = implode(', ', $groups);
                self::token_replace('resubscribe', 'group', $value, $str);
            }
        }
        return $str;
    }

    /**
     * Replace subscription-confirmation-request tokens
     * 
     * @param string $str           The string with tokens to be replaced
     * @param string $group         The name of the group being subscribed
     * @param boolean $html         Replace tokens with html or plain text
     * @return string               The processed string
     * @access public
     * @static
     */
    public static function &replaceSubscribeTokens($str, $group, $url, $html) 
    {
        if (self::token_match('subscribe', 'group', $str)) {
            self::token_replace('subscribe', 'group', $group, $str);
        }
        if (self::token_match('subscribe', 'url', $str)) {
            self::token_replace('subscribe', 'url', $url, $str);
        }
        return $str;
    }

    /**
     * Replace subscription-invitation tokens
     * 
     * @param string $str           The string with tokens to be replaced
     * @return string               The processed string
     * @access public
     * @static
     */
    public static function &replaceSubscribeInviteTokens($str) 
    {
        if (preg_match('/\{action\.subscribeUrl\}/', $str )) {
            $url   = CRM_Utils_System::url( 'civicrm/mailing/subscribe',
                                            'reset=1',
                                            true, null, true, true );
            $str = preg_replace('/\{action\.subscribeUrl\}/', $url, $str );
        }

        if ( preg_match('/\{action\.subscribeUrl.\d+\}/', $str, $matches) ) {
            foreach ( $matches as $key => $value ) {
                $gid = substr($value, 21, -1);
                $url = CRM_Utils_System::url( 'civicrm/mailing/subscribe',
                                              "reset=1&gid={$gid}",
                                              true, null, true, true );
                $url = str_replace('&amp;', '&', $url);
                $str = preg_replace('/'.preg_quote($value).'/', $url, $str );
            }
        }

        if ( preg_match('/\{action\.subscribe.\d+\}/', $str, $matches) ) {
            foreach ( $matches as $key => $value ) {
                $gid = substr($value, 18, -1);
                $config = CRM_Core_Config::singleton();
                require_once 'CRM/Core/BAO/MailSettings.php';
                $domain    = CRM_Core_BAO_MailSettings::defaultDomain();
                $localpart = CRM_Core_BAO_MailSettings::defaultLocalpart();
                // we add the 0.0000000000000000 part to make this match the other email patterns (with action, two ids and a hash)
                $str = preg_replace('/'.preg_quote($value).'/',"mailto:{$localpart}s.{$gid}.0.0000000000000000@$domain", $str);
            }
        }
        return $str;
    }
    
    /**
     * Replace welcome/confirmation tokens
     * 
     * @param string $str           The string with tokens to be replaced
     * @param string $group         The name of the group being subscribed
     * @param boolean $html         Replace tokens with html or plain text
     * @return string               The processed string
     * @access public
     * @static
     */
    public static function &replaceWelcomeTokens($str, $group, $html) 
    {
        if (self::token_match('welcome', 'group', $str)) {
            self::token_replace('welcome', 'group', $group, $str);
        }
        return $str;
    }

    /**
     * Find unprocessed tokens (call this last)
     *
     * @param string $str       The string to search
     * @return array            Array of tokens that weren't replaced
     * @access public
     * @static
     */
    public static function &unmatchedTokens(&$str) 
    {
        //preg_match_all('/[^\{\\\\]\{(\w+\.\w+)\}[^\}]/', $str, $match);
        preg_match_all('/\{(\w+\.\w+)\}/', $str, $match);
        return $match[1];
    }

    /**
     * Find and replace tokens for each component
     *
     * @param string $str       The string to search
     * @param array   $contact  Associative array of contact properties
     * @param array $components A list of tokens that are known to exist in the email body
     * @return string           The processed string
     * @access public
     * @static
     */
    public static function &replaceComponentTokens( &$str, $contact, $components, $escapeSmarty = false , $returnEmptyToken = true )
    {
        if ( !is_array($components) || empty($contact) ) {
            return $str;
        }
        
        foreach ( $components as $name => $tokens ) {
            if ( !is_array($tokens) || empty($tokens) ) {
                continue;
            }
            
            foreach ( $tokens as $token ) {
                if ( self::token_match( $name, $token, $str ) && isset( $contact[$name.'.'.$token] ) ) {
                    self::token_replace( $name, $token, $contact[$name.'.'.$token], $str, $escapeSmarty );    
                } elseif( !$returnEmptyToken ){
                    //replacing empty token 
                    self::token_replace( $name, $token, "", $str, $escapeSmarty );
                }
            }
        }  
        return $str;
    }

    /**
     * Get array of string tokens
     *     
     * @param  $string the input string to parse for tokens
     *
     * @return $tokens array of tokens mentioned in field
     * @access public
     * @static
     */
    function getTokens( $string ) 
    {
        $matches = array( );
        $tokens  = array( );
        preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                        $string,
                        $matches,
                        PREG_PATTERN_ORDER);
        
        if ( $matches[1] ) {
            foreach ( $matches[1] as $token ) {
                list($type,$name) = preg_split( '/\./', $token, 2 );
                if ( $name && $type ) {
                    if ( ! isset( $tokens[$type] ) ) {
                        $tokens[$type] = array( );
                    }
                    $tokens[$type][] = $name;
                }
            }
        }
        return $tokens;
    }

    /**
     * gives required details of contacts in an indexed array format so we
     * can iterate in a nice loop and do token evaluation
     *
     * @param  array   $contactIds       of conatcts
     * @param  array   $returnProperties of required properties
     * @param  boolean $skipOnHold       don't return on_hold contact info also.
     * @param  boolean $skipDeceased     don't return deceased contact info.
     * @param  array   $extraParams      extra params
     * @param  array   $tokens           the list of tokens we've extracted from the content
     *
     * @return array
     * @access public
     * @static
     */
    function getTokenDetails($contactIDs,
                             $returnProperties = null,
                             $skipOnHold = true,
                             $skipDeceased = true,
                             $extraParams = null,
                             $tokens = array( ),
                             $className = null ) 
    {
        if ( empty( $contactIDs ) ) {
            // putting a fatal here so we can track if/when this happens
            CRM_Core_Error::fatal( );
        }

        $params = array( );
        require_once 'CRM/Core/Form.php';
        foreach ( $contactIDs  as $key => $contactID ) {
            $params[] = array( CRM_Core_Form::CB_PREFIX . $contactID,
                               '=', 1, 0, 0);
        }
        
        // fix for CRM-2613
        if ( $skipDeceased ) {
            $params[] = array( 'is_deceased', '=', 0, 0, 0 );
        }
        
        //fix for CRM-3798
        if ( $skipOnHold ) {
            $params[] = array( 'on_hold', '=', 0, 0, 0 );
        }
        
        if ( $extraParams ) {
            $params = array_merge( $params, $extraParams );
        }
            
        // if return properties are not passed then get all return properties
        if ( empty( $returnProperties ) ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $fields = array_merge( array_keys(CRM_Contact_BAO_Contact::exportableFields( ) ),
                                   array( 'display_name', 'checksum', 'contact_id'));
            foreach( $fields as $key => $val) {
                $returnProperties[$val] = 1;
            }
        }

        $custom = array( );
        require_once 'CRM/Core/BAO/CustomField.php'; 
        foreach ( $returnProperties as $name => $dontCare ) {
            $cfID = CRM_Core_BAO_CustomField::getKeyID( $name );
            if ( $cfID ) {
                $custom[] = $cfID;
            }
        }
                
        //get the total number of contacts to fetch from database.
        $numberofContacts = count( $contactIDs );


        require_once 'CRM/Contact/BAO/Query.php';
        $query   = new CRM_Contact_BAO_Query( $params, $returnProperties );

        $details = $query->apiQuery( $params, $returnProperties, NULL, NULL, 0, $numberofContacts );

        $contactDetails =& $details[0];
                
        foreach ( $contactIDs as $key => $contactID ) {
            if ( array_key_exists( $contactID, $contactDetails ) ) {
                if ( CRM_Utils_Array::value( 'preferred_communication_method', $returnProperties ) == 1 
                     && array_key_exists( 'preferred_communication_method', $contactDetails[$contactID] ) ) {
                    require_once 'CRM/Core/PseudoConstant.php';
                    $pcm = CRM_Core_PseudoConstant::pcm();
                    
                    // communication Prefferance
                    require_once 'CRM/Core/BAO/CustomOption.php';
                    $contactPcm = explode(CRM_Core_DAO::VALUE_SEPARATOR,
                                          $contactDetails[$contactID]['preferred_communication_method']);
                    $result = array( );
                    foreach ( $contactPcm as $key => $val) {
                        if ($val) {
                            $result[$val] = $pcm[$val];
                        } 
                    }
                    $contactDetails[$contactID]['preferred_communication_method'] = implode( ', ', $result );
                }
                
                foreach ( $custom as $cfID ) {
                    if ( isset ( $contactDetails[$contactID]["custom_{$cfID}"] ) ) {
                        $contactDetails[$contactID]["custom_{$cfID}"] = 
                            CRM_Core_BAO_CustomField::getDisplayValue( $contactDetails[$contactID]["custom_{$cfID}"],
                                                                       $cfID, $details[1] );
                    }
                }
                
                //special case for greeting replacement
                foreach ( array( 'email_greeting', 'postal_greeting', 'addressee' ) as $val ) {
                    if ( CRM_Utils_Array::value( $val, $contactDetails[$contactID] ) ) {
                        $contactDetails[$contactID][$val] = $contactDetails[$contactID]["{$val}_display"];
                    }
                }
            }
        }

        // also call a hook and get token details
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::tokenValues( $details[0],
                                     $contactIDs,
                                     null,
                                     $tokens,
                                     $className );
        return $details; 
    }

    /**
     * replace greeting tokens exists in message/subject
     *     
     * @access public
     */
    function replaceGreetingTokens( &$tokenString, $contactDetails = null, $contactId = null, $className = null ) 
    {
        require_once 'CRM/Utils/Token.php';

        if ( !$contactDetails && !$contactId ) {
            return;    
        }
        
        // check if there are any tokens
        $greetingTokens = CRM_Utils_Token::getTokens( $tokenString );
                                        
        if ( !empty($greetingTokens) ) {
            // first use the existing contact object for token replacement
            if ( !empty( $contactDetails ) ) {
                $tokenString = CRM_Utils_Token::replaceContactTokens( $tokenString, $contactDetails, true , $greetingTokens, true );
            }
            
            // check if there are any unevaluated tokens
            $greetingTokens = CRM_Utils_Token::getTokens( $tokenString );
            
            // $greetingTokens not empty, means there are few tokens which are not evaluated, like custom data etc
            // so retrieve it from database 
            if ( !empty( $greetingTokens ) ) {
                $greetingsReturnProperties = array_flip( CRM_Utils_Array::value( 'contact', $greetingTokens ) );        
                $greetingsReturnProperties = array_fill_keys( array_keys( $greetingsReturnProperties ), 1 );
                $contactParams             = array( 'contact_id' => $contactId );

                $greetingDetails           = CRM_Utils_Token::getTokenDetails($contactParams,
                                                                              $greetingsReturnProperties,
                                                                              false, false, null,
                                                                              $greetingTokens,
                                                                              $className );
                
                // again replace tokens
                $tokenString               = CRM_Utils_Token::replaceContactTokens( $tokenString,
                                                                                    $greetingDetails,
                                                                                    true ,
                                                                                    $greetingTokens);
            }
        }
    }

    static function flattenTokens( &$tokens ) {
        $flattenTokens = array( );

        foreach ( array( 'html', 'text', 'subject' ) as $prop ) {
            if ( ! isset( $tokens[$prop] ) ) {
                continue;
            }
            foreach ( $tokens[$prop] as $type => $names ) {
                if ( ! isset( $flattenTokens[$type] ) ) {
                    $flattenTokens[$type] = array( );
                }
                foreach ( $names as $name ) {
                    $flattenTokens[$type][$name] = 1;
                }
            }
        }

        return $flattenTokens;
    }

    /**
     * Replace all user tokens in $str
     *
     * @param string $str       The string with tokens to be replaced
     *
     * @return string           The processed string
     * @access public
     * @static
     */
    public static function &replaceUserTokens($str, $knownTokens = null, $escapeSmarty = false) 
    {
        $key = 'user';
        if ( ! $knownTokens ||
             ! isset( $knownTokens[$key] ) ) {
            return $str;
        }
        
        $str = preg_replace( self::tokenRegex($key),
                             'self::getUserTokenReplacement(\'\\1\',$escapeSmarty)', $str );
        return $str;
    }

    public static function getUserTokenReplacement($token, $escapeSmarty = false) 
    {
        $value = '';

        list( $objectName, $objectValue ) = explode( '-', $token, 2 );

        switch ( $objectName ) {

        case 'permission':
            require_once 'CRM/Core/Permission.php';
            $value = CRM_Core_Permission::permissionEmails( $objectValue );
            break;

        case 'role':
            require_once 'CRM/Core/Permission.php';
            $value = CRM_Core_Permission::roleEmails( $objectValue );
            break;

        }
     
        if ( $escapeSmarty ) {
            $value = self::tokenEscapeSmarty( $value );
        }     

        return $value;
    }

    function getPermissionEmails( $permissionName ) {
        
    }

    function getRoleEmails( $roleName ) {
    }
    
}



