<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                  |
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
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */
require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/SelectValues.php';
require_once 'CRM/Contact/Form/Phone.php';
require_once 'CRM/Contact/Form/Email.php';
require_once 'CRM/Contact/Form/IM.php';
require_once 'CRM/Contact/Form/Address.php';

class CRM_Contact_Form_Location extends CRM_Core_Form
{
    /**
     * BLOCKS constant determines max number of Phone, Email and IM blocks to offer
     * within the Location section.
     *
     * @var int
     * @const
     */
    const BLOCKS = 3;

    static $_commPrefs = array( 'phone','email' ,'im');

    /**
     * function to build location block
     *
     * @param object $form the object of the form (QF Object)
     * @param int $maxLocationBlocks no of location blocks
     *
     * @param array $locationCompoments blocks to be displayed(Phone,Email,IM)
     *
     * @static 
     * @access public
     */
    static function &buildLocationBlock(&$form, $maxLocationBlocks, $locationCompoments = null) 
    {
        $location = array();
        
        for ($locationId = 1; $locationId <= $maxLocationBlocks; $locationId++) {
            $location[$locationId]['location_type_id'] =& $form->addElement('select'  , "location[$locationId][location_type_id]", null, CRM_Core_PseudoConstant::locationType());
            $location[$locationId]['is_primary']       =& $form->addElement('checkbox', "location[$locationId][is_primary]", ts('Primary location for this contact'),  ts('Primary location for this contact'), array('onchange' => "location_is_primary_onclick('" . $form->getName() . "', $locationId, $maxLocationBlocks);" ) );
            $location[$locationId]['name']    =& $form->addElement('text', "location[$locationId][name]",ts('Location Name'),CRM_Core_DAO::getAttribute('CRM_Core_DAO_Location', 'name'));

            CRM_Contact_Form_Address::buildAddressBlock($form, $location, $locationId);
            
            if ( ! $locationCompoments ) {
                CRM_Contact_Form_Phone::buildPhoneBlock($form, $location, $locationId, self::BLOCKS); 
                CRM_Contact_Form_Email::buildEmailBlock($form, $location, $locationId, self::BLOCKS); 
                CRM_Contact_Form_IM::buildIMBlock      ($form, $location, $locationId, self::BLOCKS); 
            } else {
                $blockCount = self::BLOCKS;
                foreach ( $locationCompoments as $key) {
                    eval('CRM_Contact_Form_' . $key . '::build' . $key . 'Block( $form ,$location , $locationId , $blockCount );');
                }
            }
            require_once 'CRM/Core/ShowHideBlocks.php';
            CRM_Core_ShowHideBlocks::linksForArray( $form, $locationId, $maxLocationBlocks, "location", '', '');
        }
        return $location;
    }

    
    /**
     * function to show/hide the location block
     *
     * @param CRM_Core_ShowHideBlocks $showHide          the showHide object
     * @param int                     $maxLocationBlocks no of location blocks
     * @param array                   $prefixBloack      array of block names
     * @param boolean                 $showHideLocation  do you want
     *                                                   location show hide links 
     *
     * @access public
     */
    function setShowHideDefaults( &$showHide, $maxLocationBlocks ,$prefixBlock = null, $showHideLocation=true) {
        for ($locationId = 1; $locationId <= $maxLocationBlocks; $locationId++) {
            if ( $showHideLocation ) {
                if ( $locationId == 1 ) {
                    $showHide->addShow( "id_location_{$locationId}" );
                    $showHide->addHide( "id_location_{$locationId}_show" );
                } else {
                    $showHide->addHide( "id_location_{$locationId}" );
                    if ( $locationId == 2 ) {
                        $showHide->addShow( "id_location_{$locationId}_show" );
                    } else {
                        $showHide->addHide( "id_location_{$locationId}_show" );
                    }
                }
            }
            
            if ( ! $prefixBlock ) {
                $prefixBlock = self::$_commPrefs ;
            }
            foreach ( $prefixBlock as $block ) {
                for ( $blockId = 1; $blockId <= self::BLOCKS; $blockId++ ) {
                    if ( $blockId != 1 ) {
                        $showHide->addHide( "id_location_{$locationId}_{$block}_{$blockId}");
                        if ( $blockId == 2 ) {
                            $showHide->addShow( "id_location_{$locationId}_{$block}_{$blockId}_show" );
                        } else {
                            $showHide->addHide( "id_location_{$locationId}_{$block}_{$blockId}_show" );
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Fix what blocks to show/hide based on the default values set
     *
     * @param CRM_Core_ShowHideBlocks $showHide the showHide object
     * @param array @defaults the array of default values
     *
     * @return void
     */
    function updateShowHide( &$showHide, &$values, $maxLocationBlocks, $prefixBlock = null ) {
        if ( empty( $values ) ) {
            return;
        }
        
        $locationKeys = array_keys( $values );
        
        foreach ( $locationKeys as $locationId ) {
            /*if( empty( $values[$locationId])){
                continue;
                }*/
            $location = $values[$locationId];
            
            $locationFlag = false;
            foreach($location as $locationKey=>$locationEntity) {
                if(is_array($locationEntity)) {
                    foreach($locationEntity as $entityKey=>$entity) {
                        if(is_array($entity)) {
                            foreach($entity as $subkey=>$subEntity) {
                                if($subEntity!='') {
                                    $locationFlag = true;
                                }
                            }
                        } else {
                            if($entity!='' and $entityKey!='country_id') {
                                $locationFlag = true;
                            }
                        }
                    }
                } 
            }
            if(!$locationFlag) {
                continue;
            }
            $showHide->addShow( "id_location_{$locationId}" );
            if ( $locationId != 1 ) {
                $showHide->addHide( "id_location_{$locationId}_show" );
            }
            if ( $locationId < $maxLocationBlocks ) {
                $nextLocationId = $locationId + 1;
                $showHide->addShow( "id_location_{$nextLocationId}_show" );
            }
            
            if ( ! $prefixBlock ) {
                $prefixBlock = self::$_commPrefs;
            }
            foreach ( $prefixBlock as $block ) {
                $tmpArray = CRM_Utils_Array::value( $block, $values[$locationId] );
                self::updateShowHideSubBlocks( $showHide, $block, "id_location_{$locationId}",
                                               $tmpArray );
            }
        }
        
    }

    /**
     * function to show/hide the block
     *
     * @param CRM_Core_ShowHideBlocks $showHide the showHide object
     *
     * @access public
     */
    function updateShowHideSubBlocks( &$showHide, $name, $prefix, &$values ) {
        if ( empty( $values ) ) {
            return;
        }
        $blockKeys = array_keys( $values );
        foreach ( $blockKeys as $blockId ) {
            
            /* if ( empty( $values[$blockId] ) ) {
                continue;
            }*/
            $blocks = $values[$blockId];
            $blockFlag = false;
            foreach($blocks as $block) {
                if($block!='') {
                    $blockFlag= true;
                }
            }

            if (!$blockFlag) {
                continue;
            }

            // blockId 1 is always shows
            if ( $blockId == 1 ) {
                continue;
            }

            $showHide->addShow( "{$prefix}_{$name}_{$blockId}" );
            $showHide->addHide( "{$prefix}_{$name}_{$blockId}_show" );
            if ( $blockId < self::BLOCKS ) {
                $nextBlockId = $blockId + 1;
                $showHide->addShow( "{$prefix}_{$name}_{$nextBlockId}_show" );
            }
        }
    }
}

?>
