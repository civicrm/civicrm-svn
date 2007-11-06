<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.9                                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Mailing/DAO/TrackableURL.php';

class CRM_Mailing_BAO_TrackableURL extends CRM_Mailing_DAO_TrackableURL {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Given a url, mailing id and queue event id, find or construct a
     * trackable url and redirect url.
     *
     * @param string $url       The target url to track
     * @param int $mailing_id   The id of the mailing
     * @param int $queue_id     The queue event id (contact clicking through)
     * @return string $redirect The redirect/tracking url
     * @static
     */
    public static function getTrackerURL($url, $mailing_id, $queue_id) {

        static $urlCache = array();

        if (array_key_exists($url, $urlCache)) {
            return $urlCache[$url] . "&qid=$queue_id";
        }

        // hack for basic CRM-1014 and CRM-1151 compliance:
        // let's not replace possible image URLs and CiviMail ones
        if (preg_match('/\.(png|jpg|jpeg|gif)$/i', $url)
            or substr_count($url, 'civicrm/extern/')
            or substr_count($url, 'civicrm/mailing/')
            ) {
            // let's not cache these, so they don't get &qid= appended to them
            return $url;
        } else {
            
            $hrefExists = false;
            $config =& CRM_Core_Config::singleton( );
            
            $tracker =& new CRM_Mailing_BAO_TrackableURL();
            if (preg_match('/^href/i',$url)) {
                $url = preg_replace('/^href[ ]*=[ ]*[\'"](.*?)[\'"]$/','$1',$url);
                $hrefExists = true;
            }
            
            $tracker->url = $url;
            $tracker->mailing_id = $mailing_id;
            
            if (! $tracker->find(true)) {
                $tracker->save();
            }
            $id = $tracker->id;
            $tracker->free();
            
            $redirect = $config->userFrameworkResourceURL . "extern/url.php?u=$id";
            $urlCache[$url] = $redirect;
        }
        
        $returnUrl = "{$urlCache[$url]}&qid={$queue_id}";
        
        if ( $hrefExists ) {
            $returnUrl = "href='{$returnUrl}'";
        }
        
        return $returnUrl;
    }
    
    public static function scan_and_replace(&$msg, $mailing_id, $queue_id, $onlyHrefs = false) {
        if (! $mailing_id) {
            return;
        }

        $protos = '(https?|ftp)';
        $letters = '\w';
        $gunk = '/#~:.?+=&%@!\-';
        $punc = '.:?\-';
        $any = "{$letters}{$gunk}{$punc}";
        if ( $onlyHrefs ) {
            $pattern = "{\\b(href=([\"'])?($protos:[$any]+?(?=[$punc]*[^$any]|$))([\"'])?)}im";
        } else {
            $pattern = "{\\b($protos:[$any]+?(?=[$punc]*[^$any]|$))}eim";
        }
        
        $trackURL    = CRM_Mailing_BAO_TrackableURL::getTrackerURL('\\1', $mailing_id, $queue_id);
        $replacement = $onlyHrefs ? ("href=\"{$trackURL}\"") : ("\"{$trackURL}\"");
        
        $msg = preg_replace( $pattern, $replacement, $msg );
    }
}

?>
