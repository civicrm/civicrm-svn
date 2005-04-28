<?php
/*
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

/**
 * A Page is basically data in a nice pretty format.
 *
 * Pages should not have any form actions / elements in them. If they
 * do, make sure you use CRM_Form and the related structures. You can
 * embed simple forms in Page and do your own form handling.
 *
 */
class CRM_Page {
    /**
     * The name of the page
     * @var string
     */
    protected $_name;

    /**
     * The title of the page used in any display
     * @var string
     */
    protected $_title;

    /**
     * A page can have multiple modes. (i.e. displays
     * a different set of data based on the input
     * @var int
     */
    protected $_mode;

    /**
     * cache the smarty template for efficiency reasons
     *
     * @var CRM_Core_Smarty
     */
    static protected $_template;

    /**
     * cache the session for efficiency reasons
     *
     * @var CRM_Core_Session
     */
    static protected $_session;

    /**
     * class constructor
     *
     * @param string $name  name of the page
     * @param string $title title of the page
     * @param int    $mode  mode of the page
     *
     * @return CRM_Page
     */
    function __construct( $name, $title = null, $mode = null ) {
        $this->_name  = $name;
        $this->_title = $title;
        $this->_mode  = $mode;
        
        // let the constructor initialize this, should happen only once
        if ( ! isset( self::$_template ) ) {
            self::$_template = CRM_Core_Smarty::singleton( );
            self::$_session  = CRM_Session::singleton( );
        }

        // if the request has a reset value, initialize the controller session
        if ( $_GET['reset'] ) {
            $this->reset( );
        }

    }

    /**
     * This function takes care of all the things common to all
     * pages. This typically involves assigning the appropriate
     * smarty variable :)
     *
     * @return string The content generated by running this page
     */
    function run( ) {
        self::$_template->assign( 'mode'   , $this->_mode );
        self::$_template->assign( 'tplFile', $this->getTemplateFileName() );

        $content = self::$_template->fetch( 'CRM/index.tpl' );
        return $content;
    }

    /**
     * Store the variable with the value in the form scope
     *
     * @param  string|array $name  name  of the variable or an assoc array of name/value pairs
     * @param  mixed        $value value of the variable if string
     *
     * @access public
     * @return void
     *
     */
    function set( $name, $value = null) {
        self::$_session->set( $name, $value, $this->_name );
    }

    /**
     * Get the variable from the form scope
     *
     * @param  string name  : name  of the variable
     *
     * @access public
     * @return mixed
     *
     */
    function get( $name ) {
        return self::$_session->get( $name, $this->_name );
    }

    /**
     * assign value to name in template
     *
     * @param array|string $name  name  of variable
     * @param mixed $value value of varaible
     *
     * @return void
     * @access public
     */
    function assign( $var, $value = null) {
        self::$_template->assign($var, $value);
    }

    /**
     * function to destroy all the session state of this page.
     *
     * @access public
     * @return void
     */
    function reset( ) {
        self::$_session->resetScope( $this->_name );
    }

    /**
     * Use the form name to create the tpl file name
     *
     * @return string
     * @access public
     */
    function getTemplateFileName() {
        $className    = get_class( $this );
        $templateName = str_replace( '_', DIRECTORY_SEPARATOR, $className ) . '.tpl';
        return $templateName;
    }
}

?>
