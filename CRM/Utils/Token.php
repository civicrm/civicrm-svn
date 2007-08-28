<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * Class to abstract token replacement 
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */
 
class CRM_Utils_Token {
    
    static $_requiredTokens = null;

    static $_tokens = array(
        'action'        => array( 
                            'donate', 
                            'forward', 
                            'optOut',
                            'optOutUrl',
                            'reply', 
                            'unsubscribe',
                            'unsubscribeUrl'
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
    public static function requiredTokens(&$str) {
        if (self::$_requiredTokens == null) {
            self::$_requiredTokens = array(    
                'domain.address'    => ts("Displays your organization's postal address."),
                'action.optOut'     => ts("Creates a link for recipients to opt out of receiving emails from your organization."), 
                'action.unsubscribe'    => ts("Creates a link for recipients to unsubscribe from the group(s) to which this mailing is being sent."),
            );
        }

        $missing = array();
        foreach (self::$_requiredTokens as $token => $description) {
            if (! preg_match('/(^|[^\{])'.preg_quote('{' . $token . '}').'/', $str)) {
                $missing[$token] = $description;
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
    public static function token_match($type, $var, &$str) {
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
    public static function &token_replace($type, $var, $value, &$str) {
        $token  = preg_quote('{' . "$type.$var") 
                . '(\|([^\}]+?))?' . preg_quote('}');
        if (! $value) {
            $value = '$3';
        }
        $str = preg_replace("/([^\{])?$token/", "\${1}$value", $str);
        return $str;
    }
    
    /**
     * get the regex for token replacement
     *
     * @param string $key       a string indicating the the type of token to be used in the expression
     * @return string           regular expression sutiable for using in preg_replace
     * @access private
     * @static
     */

    private static function tokenRegex($token_type){
      return '/(?<!\{|\\\\)\{'.$token_type.'\.(\w+)\}(?!\})/e';
    }

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
    
    public static function &replaceDomainTokens($str, &$domain, $html = false, $knownTokens = null) {
        $key = 'domain';
        if(!$knownTokens || !$knownTokens[$key]) return $str;
        $str = preg_replace(self::tokenRegex($key),'self::getDomainTokenReplacement(\\1,$domain,$html)',$str);
        return $str;
    }

    private static function getDomainTokenReplacement($token, &$domain, $html = false){
      if ($token == 'address') {
          require_once 'CRM/Utils/Address.php';
          $loc =& $domain->getLocationValues();
          $value = null;
          /* Construct the address token */
          if ( CRM_Utils_Array::value( 'address', $loc ) ) {
              $value = CRM_Utils_Address::format($loc['address']);
              if ($html) $value = str_replace("\n", '<br />', $value);
          }
      }
      
      else if ( $token == 'name') {
        $value = $domain->name;
      }
     
      else if($token == 'phone' || $token == 'email'){
        /* Construct the phone and email tokens */
        $value = null;
        if ( CRM_Utils_Array::value( $token, $loc ) ) {
          foreach ($loc[$token] as $index => $entity) {
            if ($entity->is_primary) {
              $value = $entity->$token;
              break;
            }
          }
        }
      }
      return $value;      
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
     public static function &replaceMailingTokens($str, &$mailing, $html = false, $knownTokens = null) {
        $key = 'mailing';
        if(!$knownTokens || !$knownTokens[$key]) return $str;
        $str = preg_replace(self::tokenRegex($key),'self::getMailingTokenReplacement(\\1,$mailing)',$str);
        return $str;
     }

     private static function getMailingTokenReplacement($token, &$mailing) {
      $value = '';
      if ($token == 'name') {
          $value = $mailing ? $mailing->name : 'Mailing Name';
      }
      else if ($token == 'group') {
          $groups = $mailing  ? $mailing->getGroupNames() : array('Mailing Groups');
          $value = implode(', ', $groups);
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
    public static function &replaceActionTokens($str, &$addresses, &$urls, $html = false, $knownTokens = null) {
        $key = 'action';
        // here we intersect with the list of pre-configured valid tokens
        // so that we remove anything we do not recognize
        // I hope to move this step out of here soon and
        // then we will just iterate on a list of tokens that are passed to us
        if(!$knownTokens || !$knownTokens[$key]) return $str;

        $str = preg_replace(self::tokenRegex($key),'self::getActionTokenReplacement(\\1,$addresses,$urls)',$str);
        return $str;
    }
    private static function getActionTokenReplacement($token, &$addresses, &$urls, $html = false) {

        /* If the token is an email action, use it.  Otherwise, find the
         * appropriate URL */
        if (($value = CRM_Utils_Array::value($token, $addresses)) == null) {
            if (($value = CRM_Utils_Array::value($token, $urls)) == null)
            {
                continue;
            } 
        } else {
            if ($html) {
                $value = "mailto:$value";
            }
        }
        return $value;
    }


    /**
     * Replace all the contact-level tokens in $str with information from
     * $contact.
     *
     * @param string $str         The string with tokens to be replaced
     * @param array $contact      Associative array of contact properties
     * @param boolean $html       Replace tokens with HTML or plain text
     * @param boolean $html       Replace tokens with HTML or plain text
     * @param array $knownTokens  A list of tokens that are known to exist in the email body
     * @return string             The processed string
     * @access public
     * @static
     */
    public static function &replaceContactTokens($str, &$contact, $html = false, $knownTokens = null) {
        $key = 'contact';
        if (self::$_tokens[$key] == null) {
            /* This should come from UF */
            self::$_tokens[$key] =
                array_merge( array_keys(CRM_Contact_BAO_Contact::importableFields( ) ),
                             array( 'display_name', 'checksum', 'contact_id' ) );
        }

        $cv = null;
 
        // here we intersect with the list of pre-configured valid tokens
        // so that we remove anything we do not recognize
        // I hope to move this step out of here soon and
        // then we will just iterate on a list of tokens that are passed to us
        if(!$knownTokens || !$knownTokens[$key]) return $str;

        $str = preg_replace(self::tokenRegex($key),'self::getContactTokenReplacement(\\1, $contact)',$str);
        return $str;
    }
    
    private function getContactTokenReplacement($token,&$contact){

        if ($token == '') {
            continue;
        }

        /* Construct value from $token and $contact */
        $value = null;

        if ($cfID = CRM_Core_BAO_CustomField::getKeyID($token)) {
            // only generate cv if we need it
            if ( $cv === null ) {
                $cv =& CRM_Core_BAO_CustomValue::getContactValues($contact['contact_id']);
            }
            foreach ($cv as $customValue) {
                if ($customValue['custom_field_id'] == $cfID) {
                    $value = CRM_Core_BAO_CustomOption::getOptionLabel($cfID, $customValue['value']);
                    break;
                }
            }
        } else if ( $token == 'checksum' ) {
            $cs = CRM_Contact_BAO_Contact::generateChecksum( $contact['contact_id'] );
            $value = "cs={$cs}";
        } else {
            $value = CRM_Contact_BAO_Contact::retrieveValue($contact, $token);
        }

        return $value;
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
            if (! empty($groups)) {
                $config =& CRM_Core_Config::singleton();
                $base = CRM_Utils_System::baseURL();

                // FIXME: an ugly hack for CRM-2035, to be dropped once CRM-1799 is implemented
                require_once 'CRM/Contact/DAO/Group.php';
                $dao =& new CRM_Contact_DAO_Group();
                $dao->domain_id = $config->domainID();
                $dao->find();
                while ($dao->fetch()) {
                    if (substr($dao->visibility, 0, 6) == 'Public') {
                        $visibleGroups[] = $dao->id;
                    }
                }
                
                if ($html) {
                    $value = '<ul>';
                    foreach ($groups as $gid => $name) {
                        $verpAddress = implode( $config->verpSeparator,
                                                array( 'subscribe',
                                                       $domain->id,
                                                       $gid ) ) . "@{$domain->email_domain}";
                        $resub = '';
                        if (in_array($gid, $visibleGroups)) {
                            $resub = "(<a href=\"mailto:$verpAddress\">" . ts("re-subscribe") . "</a>)";
                        }
                        $value .= "<li>$name $resub</li>\n";
                    }
                    $value .= '</ul>';
                } else {
                    $value = "\n";
                    foreach ($groups as $gid => $name) {
                        $verpAddress = implode( $config->verpSeparator, 
                                                array( 'subscribe',
                                                       $domain->id,
                                                       $gid ) ) . "@{$domain->email_domain}";
                        $resub = '';
                        if (in_array($gid, $visibleGroups)) {
                            $resub = ts("(re-subscribe: %1)", array( 1 => "$verpAddress"));
                        }
                        $value .= "\t* $name $resub\n";
                    }
                    $value .= "\n";
                }
                self::token_replace('unsubscribe', 'group', $value, $str);
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
    public static function &replaceSubscribeTokens($str, $group, $html) {
        if (self::token_match('subscribe', 'group', $str)) {
            self::token_replace('subscribe', 'group', $group, $str);
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
    public static function &replaceWelcomeTokens($str, $group, $html) {
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
    public static function &unmatchedTokens(&$str) {
        preg_match_all('/[^\{]\{(\w+\.?\w+)\}/', $str, $match);
        return $match[1];
    }
}

?>
