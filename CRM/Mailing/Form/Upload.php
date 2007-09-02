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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

/**
 * This file is used to build the form configuring mailing details
 */
class CRM_Mailing_Form_Upload extends CRM_Core_Form 
{
    
    /**
     * This function sets the default values for the form.
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $mailingID = $this->get("mid");

        $session =& CRM_Core_Session::singleton();
        $session->set('skipTextFile', false);
        $session->set('skipHtmlFile', false);

        $defaults = array( );
        if ( $mailingID ) {
            require_once "CRM/Mailing/DAO/Mailing.php";
            $dao =&new  CRM_Mailing_DAO_Mailing();
            $dao->id = $mailingID; 
            $dao->find(true);
            $dao->storeValues($dao, $defaults);
            
            if ($defaults['body_text']) {
                $this->set('textFile', $defaults['body_text'] );
                $session->set('skipTextFile', true);
            }

            if (CRM_Utils_Array::value('body_html',$defaults)) {
                $this->set('htmlFile', $defaults['body_html'] );
                $session->set('skipHtmlFile', true);
            }
        }

        return $defaults;
    }

 
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $session =& CRM_Core_Session::singleton();

        $domain = new CRM_Core_DAO_Domain( );
        $domain->id = CRM_Core_Config::domainID( );
        $domain->find( true );

        $this->add('text', 'from_name', ts('FROM Name'));
        $this->add('text', 'from_email', ts('FROM Email'), NULL, true);

        $defaults['from_name' ] = $domain->email_name;
        $defaults['from_email'] = $domain->email_address;
        
        $this->add('checkbox', 'forward_replies', ts('Forward Replies?'));
        $defaults['forward_replies'] = true;
        
        $this->add('checkbox', 'url_tracking', ts('Track Click-throughs?'));
        $defaults['url_tracking'] = true;
        
        $this->add('checkbox', 'open_tracking', ts('Track Opens?'));
        $defaults['open_tracking'] = true;
        
        $this->add('checkbox', 'auto_responder', ts('Auto-respond to Replies?'));
        $defaults['auto_responder'] = false;
        
        $this->add('text', 'subject', ts('Mailing Subject'), 'size=30 maxlength=60', true);
        $defaults['subject'] = $this->get('name');
        
        $this->addElement( 'file', 'textFile', ts('Upload TEXT Message'), 'size=30 maxlength=60' );
        $this->setMaxFileSize( 1024 * 1024 );
        $this->addRule( 'textFile', ts('File size should be less than 1 MByte'), 'maxfilesize', 1024 * 1024 );
        $this->addRule( 'textFile', ts('File must be in UTF-8 encoding'), 'utf8File' );

        $this->addElement( 'file', 'htmlFile', ts('Upload HTML Message'), 'size=30 maxlength=60' );
        $this->setMaxFileSize( 1024 * 1024 );
        $this->addRule( 'htmlFile', ts('File size should be less than 1 MByte'), 'maxfilesize', 1024 * 1024 );
        $this->addRule( 'htmlFile', ts('File must be in UTF-8 encoding'), 'utf8File' );
        
        $this->add( 'select', 'header_id', ts( 'Mailing Header' ), array('' => ts('- none -')) + CRM_Mailing_PseudoConstant::component( 'Header' ) );
        $this->add( 'select', 'footer_id', ts( 'Mailing Footer' ), array('' => ts('- none -')) + CRM_Mailing_PseudoConstant::component( 'Footer' ) );
        $this->add( 'select', 'reply_id', ts( 'Auto-responder' ), CRM_Mailing_PseudoConstant::component( 'Reply' ), true );
        $this->add( 'select', 'unsubscribe_id', ts( 'Unsubscribe Message' ), CRM_Mailing_PseudoConstant::component( 'Unsubscribe' ), true );
        $this->add( 'select', 'optout_id', ts( 'Opt-out Message' ), CRM_Mailing_PseudoConstant::component( 'OptOut' ), true );
        
        $this->addFormRule(array('CRM_Mailing_Form_Upload', 'dataRule'));

        $this->addButtons( array(
                                 array ( 'type'      => 'back',
                                         'name'      => ts('<< Previous') ),
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Next >>'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        $this->setDefaults($defaults);
    }

    public function postProcess() 
    {
        $params = $ids       = array( );
        $uploadParams        = array ( 
                                      'header_id', 'footer_id', 'reply_id', 'unsubscribe_id', 'optout_id', 
                                      'textFile', 'htmlFile', 'subject', 'from_name', 'from_email'
                                      );
        
        $uploadParamsBoolean = array( 
                                     'is_template', 'forward_replies', 'url_tracking',
                                     'open_tracking', 'auto_responder'
                                     );
        
        $fileType            = array( 'textFile', 'htmlFile' );
        
        foreach ( $uploadParams as $key ) {
            $params[$key] = $this->controller->exportvalue($this->_name, $key);
            $this->set($key, $this->controller->exportvalue($this->_name, $key));
        }
        
        foreach ( $fileType as $key ) {
            if( file_get_contents($this->controller->exportvalue($this->_name, $key) ) ) { 
                $params['body_'. substr($key,0,4 )] = file_get_contents($this->controller->exportvalue($this->_name, $key) );
            } else {
                $params['body_'. substr($key,0,4 )] = 'NULL';
            }
            $this->set($key, $this->controller->exportvalue($this->_name, $key));
        }
        
        foreach ( $uploadParamsBoolean as $key ) {
            if ( $this->controller->exportvalue($this->_name, $key) ) {
                $params[$key] = true;
            } else {
                $params[$key] = false;
            }
            $this->set($key, $this->controller->exportvalue($this->_name, $key));
        }
        
        foreach ( array( 'name', 'groups', 'mailings' ) as $key ) {
            $params[$key] = $this->get($key);
        }
        
        $session =& CRM_Core_Session::singleton();
        $params['domain_id']  = $session->get('domainID');
        $params['contact_id'] = $session->get('userID');
        
        if ( $this->controller->exportvalue($this->_name, 'textFile') ) {
            $this->set('textFile', $this->controller->exportvalue($this->_name, 'textFile') );
        }
        
        if ($this->controller->exportvalue($this->_name, 'htmlFile')) {
            $this->set('htmlFile', $this->controller->exportvalue($this->_name, 'htmlFile'));
        }

        $params['test'] = true;
        /* Build the mailing object */
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $mailing = CRM_Mailing_BAO_Mailing::create($params, $ids);
        $this->set('mailing_id', $mailing->id);
        
        $job =& new CRM_Mailing_BAO_Job();
        $job->mailing_id = $mailing->id;
        if ($job->find(true)) {
            $this->set('job_id',$job->id);
        } 
        
    }
    
    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    static function dataRule(&$params, &$files, &$options) 
    {
        if (CRM_Utils_Array::value('_qf_Import_refresh', $_POST)) {
            return true;
        }
        $errors = array();

        require_once 'CRM/Core/BAO/Domain.php';
        
        $domain =& CRM_Core_BAO_Domain::getCurrentDomain();
        $mailing = null;

        $session =& CRM_Core_Session::singleton();
        $values = array('contact_id' => $session->get('userID'));
        require_once 'api/Contact.php';
        $contact =& crm_fetch_contact( $values );
        
        $verp = array_flip(array(  'optOut', 'reply', 'unsubscribe', 'owner'));
        foreach($verp as $key => $value) {
            $verp[$key]++;
        }
        
        $urls = array_flip(array( 'forward', 'optOutUrl', 'unsubscribeUrl') );
        foreach($urls as $key => $value) {
            $urls[$key]++;
        }
        
        require_once 'CRM/Mailing/BAO/Component.php';
        
        // set $header and $footer
        foreach (array('header', 'footer') as $part) {
            $$part = array( );
            if ($params["{$part}_id"]) {
	        //echo "found<p>";
                $component =& new CRM_Mailing_BAO_Component();
                $component->id = $params["{$part}_id"];
                $component->find(true);
                ${$part}['textFile'] = $component->body_text;
                ${$part}['htmlFile'] = $component->body_html;
                $component->free();
            } else {
                ${$part}['htmlFile'] = ${$part}['textFile'] = '';
            }
        }

        require_once 'CRM/Utils/Token.php';

        $skipTextFile = $session->get('skipTextFile');
        $skipHtmlFile = $session->get('skipHtmlFile');

        if ( ( ! isset( $files['textFile'] ) || ! file_exists( $files['textFile']['tmp_name'] ) ) &&
             ( ! isset( $files['htmlFile'] ) || ! file_exists( $files['htmlFile']['tmp_name'] ) ) ) {
            if ( ! ( $skipTextFile || $skipHtmlFile ) ) {
                $errors['textFile'] = ts('Please provide either a Text or HTML formatted message - or both.');
            }
        }

        foreach (array('textFile', 'htmlFile') as $file) {
            if (!file_exists(CRM_Utils_Array::value('tmp_name',$files[$file]))) {
                continue;
            }
            $str = file_get_contents($files[$file]['tmp_name']);
            $name = $files[$file]['name'];
            
            /* append header/footer */
            $str = $header[$file] . $str . $footer[$file];

            $dataErrors = array();
            
            /* First look for missing tokens */
            $err = CRM_Utils_Token::requiredTokens($str);
            if ($err !== true) {
                foreach ($err as $token => $desc) {
                    $dataErrors[]   = '<li>' 
                                    . ts('This message is missing a required token - {%1}: %2',
                                         array(1 => $token, 2 => $desc))
                                    . '</li>';
                }
            }
            
            /* Do a full token replacement on a dummy verp, the current contact
             * and domain. */
            
            // here we make a dummy mailing object so that we
            // can retrieve the tokens that we need to replace
            // so that we do get an invalid token error
            // this is qute hacky and I hope that there might
            // be a suggestion from someone on how to
            // make it a bit more elegant
            
            require_once 'CRM/Mailing/BAO/Mailing.php';
            $dummy_mail = new CRM_Mailing_BAO_Mailing();
            $dummy_mail->body_text = $str;
            $tokens = $dummy_mail->getTokens();

            $str = CRM_Utils_Token::replaceDomainTokens($str, $domain, null, $tokens['body_text']);
            $str = CRM_Utils_Token::replaceMailingTokens($str, $mailing, null, $tokens['body_text']);
            $str = CRM_Utils_Token::replaceActionTokens($str, $verp, $urls, null, $tokens['body_text']);
            $str = CRM_Utils_Token::replaceContactTokens($str, $contact, null, $tokens['body_text']);

            $unmatched = CRM_Utils_Token::unmatchedTokens($str);
            if (! empty($unmatched)) {
                foreach ($unmatched as $token) {
                    $dataErrors[]   = '<li>'
                                    . ts('Invalid token code')
                                    .' {'.$token.'}</li>';
                }
            }
            if (! empty($dataErrors)) {
                $errors[$file] = 
                ts('The following errors were detected in %1:', array(1 => $name)) . ' <ul>' . implode('', $dataErrors) . '</ul><br /><a href="http://wiki.civicrm.org/confluence//x/nC" target="_blank">' . ts('More information on required tokens...') . '</a>';
            }
        }
        return empty($errors) ? true : $errors;
    }

    /**
     * Display Name of the form
     *
     * @access public
     * @return string
     */
    public function getTitle( ) 
    {
        return ts( 'Upload Message' );
    }
}

?>
