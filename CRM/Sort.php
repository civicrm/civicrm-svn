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
 * Base class to provide generic sort functionality
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */


class CRM_Sort {

    /**
     * constants to determine what direction each variable
     * is to be sorted
     *
     * @var int
     */
    const
        ASCENDING  = 1,
        DESCENDING = 2,
        DONTCARE   = 4,

        /**
         * the name for the sort GET/POST param
         *
         * @var string
         */
        SORT_ID    = 'crmSID';



    /**
     * name of the sort function. Used to isolate session variables
     * @var string
     */
    protected $_name;

    /**
     * array of variables that influence the query
     *
     * @var array
     */
    protected $_vars;

    /**
     * the basename of the file being invoked. Note that most
     * of this stuff has been copied from the PEAR Pager Code.
     *
     * @var string
     */
    protected $_fileName;

    /**
     * the dirname of the file being invoked
     *
     * @var string
     */
    protected $_path;

    /**
     * the newly formulate url
     *
     * @var string
     */
    protected $_url;

    /**
     * what's the name of the sort variable in a REQUEST
     *
     * @var string
     */
    protected $_urlVar;

    /**
     * What variable are we currently sorting on
     *
     * @var string
     */
    protected $_currentSortID;

    /**
     * The output generated for the current form
     *
     * @var array
     */
    public $_response;

    /**
     * The constructor takes an assoc array
     *   key names of variable (which should be the same as the column name)
     *   value: ascending or descending
     *
     * @param mixed $vars - assoc array as described above
     *
     * @return void
     *
     * @access public
     *
     */
    function CRM_Sort( $vars, $defaultSortOrder = null ) {
        $this->_vars      = array( );
        $this->_order     = array( );
        $this->_response  = array();

        $count = 1;

        foreach ( $vars as $name => $value ) {
            $this->_order   [$count] = $name;

            $item = array( );
            $item['direction'] = $value;
            $item['order']     = $count;

            $this->_vars[$name] = $item;

            $count++;
        }
    
        $this->_currentSortID  = 1;

        $this->_urlVar   = CRM_Sort::SORT_ID;

        $this->_fileName = basename( $_SERVER['PHP_SELF'] );
        $this->_path     = str_replace( '\\' , '/' , dirname( $_SERVER['PHP_SELF'] ) );
        $this->_url      = $this->_path . '/' . $this->_fileName . $this->getQueryString( );


        $this->parseURLString( $defaultSortOrder );
        $this->initLinks( );
    }

    /**
     * Copied from Pager/Common.php
     */
    function getQueryString( ) {
        // Sort out query string to prevent messy urls
        $querystring = array();
        $qs = array();

        if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
            $qs = explode('&', str_replace( '&amp;', '&', $_SERVER['QUERY_STRING'] ) );
            for ($i = 0, $cnt = count($qs); $i < $cnt; $i++) {
                if ( strstr( $qs[$i], '=' ) !== false ) { // check first if exist a pair
                    list($name, $value) = explode( '=', $qs[$i] );
                    if ( $name != $this->_urlVar ) {
                        $qs[$name] = $value;
                    }
                    unset( $qs[$i] );
                }
            }
        }

        foreach ($qs as $name => $value) {
            if ( $name != 'reset' ) {
                $querystring[] = $name . '=' . $value;
            }
        }

        return '?' . implode( '&amp;', $querystring) . ( ! empty( $querystring ) ? '&amp;' : '') . $this->_urlVar . '=';
    }

    function getSingleClause( $current ) {
        $name = $this->_order[$current];
        $sql  = $name;
        if ( $this->_vars[$name]['direction'] == CRM_Sort::ASCENDING || 
             $this->_vars[$name]['direction'] == CRM_Sort::DONTCARE ) {
            $sql .= " asc,";
        } else {
            $sql .= " desc,";
        }
    
        return $sql;
    }

    function orderBy( ) {
        // get the current one first
        $sql = $this->getSingleClause( $this->_currentSortID );

        for ( $i = 1; $i <= count( $this->_order ); $i++ ) {
            if ( $i != $this->_currentSortID ) {
                $name = $this->_order[$i];
                $sql .= " $name asc,";
            }
        }
    
        return substr( $sql, 0, -1 );
    }

    function formURLString( ) {
        $url   = '';

        $name = $this->_order[$this->_currentSortID];

        $url  = $this->_currentSortID;
        $dir  = $this->_vars[$name]['direction'];
        if ( $dir == CRM_Sort::ASCENDING || $dir == CRM_SORT::DONTCARE ) {
            $url .= "_u";
        } else {
            $url .= "_d";
        }

        return $url;
    }
  
    function getDirection() {
        $name = $this->_order[$this->_currentSortID];
        $dir  = $this->_vars[$name]['direction'];
        return $dir;
    }
  
    function parseURLString( $defaultSortOrder ) {
        $url = $_GET[CRM_Sort::SORT_ID];

        if ( empty( $url ) ) {
            $url = $defaultSortOrder;
            if ( empty( $url ) ) {
                return;
            }
        }

        list( $current, $direction ) = explode( '_', $url );
      
        if ( $direction == 'u' ) {
            $direction = CRM_Sort::ASCENDING;
        } else if  ( $direction == 'd' ) {
            $direction = CRM_Sort::DESCENDING;
        } else {
            $direction = CRM_Sort::DONTCARE;
        }

        $this->_currentSortID = $current;
        $name = $this->_order[$this->_currentSortID];
        $this->_vars[$name]['direction'] = $direction;
    }

    function initLinks( ) {
        $this->_response = array( );

        $current = $this->_currentSortID;
        foreach ( $this->_vars as $name => $item ) {
            $this->_response[$name] = array();

            $prevDirection = $item['direction'];

            $newDirection = ( $prevDirection == CRM_Sort::DESCENDING || $prevDirection == CRM_Sort::DONTCARE ) ?
                CRM_Sort::ASCENDING : CRM_Sort::DESCENDING;

            $this->_currentSortID = $item['order'];
            $this->_vars[$name]['direction'] = $newDirection;

            $this->_response[$name]['link'] = $this->_url . $this->formURLString( );

            if ( $current == $item['order'] ) {
                $this->_response[ $name ]['direction' ] = ( $prevDirection == CRM_Sort::ASCENDING ) ? '^' : 'v';
            } else {
                $this->_response[ $name ]['direction' ] = '';
            }

            $this->_vars[$name]['direction'] = $prevDirection;
            $this->_currentSortID = $current;
        }

    }

    /**
     * getter for currentSortID
     *
     * @return int
     * @acccess public
     */
    function getCurrentSortID( ) {
        return $this->_currentSortID;
    }

}

?>
