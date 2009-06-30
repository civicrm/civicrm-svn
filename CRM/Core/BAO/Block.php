<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 * add static functions to include some common functionality
 * used across location sub object BAO classes
 *
 */

class CRM_Core_BAO_Block 
{
    /**
     * Fields that are required for a valid block
     */
    static $requiredBlockFields = array ( 'email'  => array( 'email' ),
                                          'phone'  => array( 'phone' ),
                                          'im'     => array( 'name' ),
                                          'openid' => array( 'openid' )
                                          );

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param Object $block         typically a Phone|Email|IM|OpenID object
     * @param string $blockName     name of the above object
     * @param array  $params        input parameters to find object
     * @param array  $values        output values of the object
     *
     * @return array of $block objects.
     * @access public
     * @static
     */
    static function &getValues( $blockName, $params )  
    {
        eval ('$block = & new CRM_Core_BAO_' . $blockName .'( );');
        $blocks = array( );
        if ( ! isset( $params['entity_table'] ) ) {
            $block->contact_id = $params['contact_id'];
            if ( ! $block->contact_id ) {
                CRM_Core_Error::fatal( );
            }
            $blocks = self::retrieveBlock( $block, $blockName );
        } else {
            $blockIds = self::getBlockIds( $blockName, null, $params );
            if ( empty($blockIds)) {
                return $blocks;
            }
            $count = 1;
            foreach( $blockIds[1] as $blockId ) {
                eval ('$block = & new CRM_Core_BAO_' . $blockName .'( );');
                $block->id = $blockId['id'];
                $getBlocks = self::retrieveBlock( $block, $blockName );
                $blocks[$block->location_type_id][$count] =  $getBlocks[$block->location_type_id][1];
                $count++;
            }
        }
        
        return $blocks;
    }
    
    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param Object $block         typically a Phone|Email|IM|OpenID object
     * @param string $blockName     name of the above object
     * @param array  $values        output values of the object
     *
     * @return array of $block objects.
     * @access public
     * @static
     */
    static function retrieveBlock( &$block, $blockName ) 
    {
        // we first get the primary location due to the order by clause
        $block->orderBy( 'is_primary desc, location_type_id desc, id asc' );
        $block->find( );
        
        $locationTypes = array( );
        $blocks =array( );
        $count = 1;
        while ( $block->fetch( ) ) {
            $values = array( );
            CRM_Core_DAO::storeValues( $block, $values );
            //logic to check when we should increment counter
            if ( !empty( $locationTypes ) ) {
                if ( array_key_exists ( $block->location_type_id, $locationTypes ) ) {
                    $count = $locationTypes[$block->location_type_id];
                    $count++;
                    $locationTypes[$block->location_type_id] = $count;
                } else {
                    $locationTypes[$block->location_type_id]  = 1;
                    $count = 1;
                }
            } else {
                $locationTypes[$block->location_type_id]  = 1;
                $count = 1;
            }
            
            $blocks[$block->location_type_id][$count] = $values;
            if ( $block->is_primary ) {
                $blocks[$block->location_type_id]['is_primary'] = 1;    
            }
        }

        return $blocks ;
    }
    
   
    /**
     * check if the current block object has any valid data
     *
     * @param array  $blockFields   array of fields that are of interest for this object
     * @param array  $params        associated array of submitted fields
     *
     * @return boolean              true if the block has data, otherwise false
     * @access public
     * @static
     */
    static function dataExists( $blockFields, &$params ) 
    {
        foreach ( $blockFields as $field ) {
            if ( CRM_Utils_System::isNull( $params[$field] ) ) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * check if the current block exits
     *
     * @param string  $blockName   bloack name
     * @param array   $params      associated array of submitted fields
     *
     * @return boolean             true if the block exits, otherwise false
     * @access public
     * @static
     */
    static function blockExists( $blockName, &$params ) 
    {
        // return if no data present
        if ( ! array_key_exists( $blockName, $params ) ) {
	        return false;
        }

        return true;
    }

    /**
     * Function to get all block ids for a contact
     *
     * @param string $blockName block name
     * @param int    $contactId contact id
     *
     * @return array $contactBlockIds formatted array of block ids
     *
     * @access public
     * @static
     */
    static function getBlockIds ( $blockName, $contactId = null, $entityElements = null )
    {
        $allBlocks = array( );
        $name = ucfirst( $blockName );
        if ( $contactId ) {
            eval ( '$allBlocks = CRM_Core_BAO_' . $name . '::all' . $name . 's( $contactId );');
        } else if ( !empty($entityElements) && $blockName != 'openid' ) {
            eval ( '$allBlocks = CRM_Core_BAO_' . $name . '::allEntity' . $name . 's( $entityElements );');
        }
        
        return $allBlocks;
    }

    /**
     * takes an associative array and creates a block
     *
     * @param string $blockName      block name
     * @param array  $params         (reference ) an assoc array of name/value pairs
     * @param array  $requiredFields fields that's are required in a block
     *
     * @return object       CRM_Core_BAO_Block object on success, null otherwise
     * @access public
     * @static
     */
    static function create( $blockName, &$params, $entity = null ) 
    {
        if ( !self::blockExists( $blockName, $params ) ) {
            return null;
        }
        
        $name      = ucfirst( $blockName );
        $contactId = null;
        $isPrimary = $isBilling   = true;
        $entityElements = $blocks = array( );
        
        if ( $entity ) {
            $entityElements = array( 'entity_table' => $params['entity_table'],
                                     'entity_id'    => $params['entity_id']);  
        } else {
            $contactId = $params['contact_id'];
        }
        
        //get existsing block ids.
        $blockIds  = self::getBlockIds( $blockName, $contactId, $entityElements );
        
        foreach ( $params[$blockName] as  $count => $value ) {
            if ( !is_array( $value ) ) continue;
            $contactFields = array( 'contact_id'       => $contactId,
                                    'location_type_id' => $value['location_type_id'] );
            
            //check for update 
            if ( is_array( $blockIds ) && !empty( $blockIds ) ) {
                foreach ( $blockIds as $blockId => $blockValue ) {
                    if ( $blockValue['locationTypeId'] == $value['location_type_id'] ) {
                        $value['id'] = $blockValue['id'];
                    }
                }
            }
            
            $dataExits = self::dataExists( self::$requiredBlockFields[$blockName], $value );
            
            if ( CRM_Utils_Array::value( 'id', $value ) && !$dataExits ) {
                //delete the existing record
                self::blockDelete( $name, array( 'id' => $value['id'] ) ); 
                continue;
            } else if ( !$dataExits ) {
                continue;
            }
            
            if ( $isPrimary && $value['is_primary'] ) {
                $contactFields['is_primary'] = $value['is_primary'];
                $isPrimary = false;
            } else {
                $contactFields['is_primary'] = false;
            }
            
            if ( $isBilling && $value['is_billing'] ) {
                $contactFields['is_billing'] = $value['is_billing'];
                $isBilling = false;
            } else {
                $contactFields['is_billing'] = false;
            }
            
            $blockFields = array_merge( $value, $contactFields );
            eval ( '$blocks[] = CRM_Core_BAO_' . $name . '::add( $blockFields );' );
        }
        
        return $blocks;
    }
    
    /**
     * Function to delete block
     *
     * @param  string $blockName       block name
     * @param  int    $params          associates array
     *
     * @return void
     * @static
     */
    static function blockDelete ( $blockName, $params ) 
    {
        eval ( '$block =& new CRM_Core_DAO_' . $blockName . '( );' );

        $block->copyValues( $params );

        $block->delete();
    }
    

}


