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

require_once 'CRM/Contact/Form/StateCountry.php';

/**
 * This class is used to build address block
 */
class CRM_Contact_Form_Address
{
    /**
     * build form for address input fields 
     *
     * @param object $form - CRM_Core_Form (or subclass)
     * @param array reference $location - location array
     * @param int $locationId - location id whose block needs to be built.
     * @return none
     *
     * @access public
     * @static
     */
    static function buildAddressBlock(&$form, &$location, $locationId)
    {
        require_once 'CRM/Core/BAO/Preferences.php';
        $addressOptions = CRM_Core_BAO_Preferences::valueOptions( 'address_options', true, null, true );

        $config =& CRM_Core_Config::singleton( );
        $attributes = CRM_Core_DAO::getAttribute('CRM_Core_DAO_Address');
        
        $elements = array( 
                          'street_address'         => array( ts('Street Address')    ,  $attributes['street_address'], null ),
                          'supplemental_address_1' => array( ts('Addt\'l Address 1') ,  $attributes['supplemental_address_1'], null ),
                          'supplemental_address_2' => array( ts('Addt\'l Address 2') ,  $attributes['supplemental_address_2'], null ),
                          'city'                   => array( ts('City')              ,  $attributes['city'] , null ),
                          'postal_code'            => array( ts('Zip / Postal Code') ,  $attributes['postal_code'], null ),
                          'postal_code_suffix'     => array( ts('Postal Code Suffix'),  array( 'size' => 4, 'maxlength' => 12 ), null ),
                          'county_id'              => array( ts('County')            ,  $attributes['county_id'], 'county' ),
                          'state_province_id'      => array( ts('State / Province')  ,  $attributes['state_province_id'],null ),
                          'country_id'             => array( ts('Country')           ,  $attributes['country_id'], null ), 
                          'geo_code_1'             => array( ts('Latitude') ,  array( 'size' => 4, 'maxlength' => 8 ), null ),
                          'geo_code_2'             => array( ts('Longitude'),  array( 'size' => 4, 'maxlength' => 8 ), null )
                          ); 
        
        foreach ( $elements as $name => $v ) {
            list( $title, $attributes, $select ) = $v;
            
            if ( ! $addressOptions[$title] ) {
                continue;
            }
            
            if ( ! $attributes ) {
                $attributes = $attributes[$name];
            }
            
            //build normal select if country is not present in address block
            if ( $name == 'state_province_id' && ! $addressOptions[ $elements['country_id'][0] ] ) {
                $select = 'stateProvince';
            }
            
            if ( ! $select ) {
                if ( $name == 'country_id' || $name == 'state_province_id' ) {
                    $onValueChanged = null;
                    $dataUrl        = null;

                    if ( $name == 'country_id') {
                        $dataUrl =  CRM_Utils_System::url( "civicrm/ajax/country", "s=%{searchString}", true, null, false );

                        //when only country is enable, don't call function to build state province
                        if ( $addressOptions[ $elements['state_province_id'][0] ] ) {
                            $onValueChanged = "getStateProvince{$locationId}( this, {$locationId} )";
                        }
                    } else {
                        $stateUrl = CRM_Utils_System::url( "civicrm/ajax/state","s=%{searchString}", true, null, false );
                        $form->assign( 'stateURL', $stateUrl );
                    }
                    
                    $attributes = array( 'dojoType'       => 'ComboBox',
                                         'mode'           => 'remote',
                                         'style'          => 'width: 230px;',
                                         'value'          => '',
                                         'dataUrl'        => $dataUrl,
                                         '_onBlurInput'   => $onValueChanged,
                                         'onValueChanged' => $onValueChanged,
                                         'id'             => 'location_'.$locationId.'_address_'.$name );
                }

                $location[$locationId]['address'][$name] =
                    $form->addElement( 'text',
                                       "location[$locationId][address][$name]",
                                       $title,
                                       $attributes );
            } else {
                $location[$locationId]['address'][$name] =
                    $form->addElement( 'select',
                                       "location[$locationId][address][$name]",
                                       $title,
                                       array('' => ts('- select -')) + CRM_Core_PseudoConstant::$select( ) );
            }
        }
    }
    
    /**
     * check for correct state / country mapping.
     *
     * @param array reference $fields - submitted form values.
     * @param array reference $errors - if any errors found add to this array. please.
     * @return true if no errors
     *         array of errors if any present.
     *
     * @access public
     * @static
     */
    static function formRule(&$fields, &$errors)
    {
        // check for state/county match if not report error to user.
        for ($i=1; $i<=CRM_Contact_Form_Location::BLOCKS; $i++) {
            if ( ! CRM_Utils_Array::value( $i, $fields['location'] ) &&
                 ! CRM_Utils_Array::value( 'address', $fields['location'][$i] ) ) {
                continue;
            }

            //state country validation
            $countryId = $stateProvinceId = null;
            if ( CRM_Utils_Array::value( 'country_id', $fields['location'][$i]['address'] ) ) {
                $countries = CRM_Core_PseudoConstant::country( );
                
                $countryExists = null;
                $countryExists = array_key_exists( CRM_Utils_Array::value( 'country_id',
                                                                           $fields['location'][$i]['address'] ), $countries );
                if ( $countryExists ) {
                    $countryId =  CRM_Utils_Array::value( 'country_id', $fields['location'][$i]['address'] );
                } else {
                    $errors["location[$i][address][country_id]"] = "Enter the valid Country name.";
                }
            }

            if ( CRM_Utils_Array::value( 'state_province_id', $fields['location'][$i]['address'] ) ) {
                $stateProvinces  = CRM_Core_PseudoConstant::stateProvince( false, false );

                $stateProvinceExists = null;
                $stateProvinceExists = array_key_exists( CRM_Utils_Array::value( 'state_province_id',
                                                                             $fields['location'][$i]['address'] ) , $stateProvinces );
                if ( $stateProvinceExists ) {
                    $stateProvinceId = CRM_Utils_Array::value( 'state_province_id', $fields['location'][$i]['address'] );
                } else {
                    $errors["location[$i][address][state_province_id]"] = "Please select a valid State/Province name.";
                }
            }

            $countyId = CRM_Utils_Array::value( 'county_id', $fields['location'][$i]['address'] );
            
            if ( $stateProvinceId && $countryId ) {
                $stateProvinceDAO =& new CRM_Core_DAO_StateProvince();
                $stateProvinceDAO->id = $stateProvinceId;
                $stateProvinceDAO->find(true);
                if ($stateProvinceDAO->country_id != $countryId) {
                    // countries mismatch hence display error
                    $stateProvinces = CRM_Core_PseudoConstant::stateProvince();
                    $countries =& CRM_Core_PseudoConstant::country();
                    $errors["location[$i][address][state_province_id]"] = "State/Province " . $stateProvinces[$stateProvinceId] . " is not part of ". $countries[$countryId] . ". It belongs to " . $countries[$stateProvinceDAO->country_id] . "." ;
                }
            }

            //state county validation
            if ( $stateProvinceId && $countyId ) {
                $countyDAO =& new CRM_Core_DAO_County();
                $countyDAO->id = $countyId;
                $countyDAO->find(true);
                
                if ($countyDAO->state_province_id != $stateProvinceId) {
                    $counties =& CRM_Core_PseudoConstant::county();
                    $errors["location[$i][address][county_id]"] = "County " . $counties[$countyId] . " is not part of ". $stateProvinces[$stateProvinceId] . ". It belongs to " . $stateProvinces[$countyDAO->state_province_id] . "." ;
                }
            }
        }             
    }
}

?>
