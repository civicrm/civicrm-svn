<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
 * A PHP cron script to format all the addresses in the database. Currently
 * it only does geocoding if the geocode values are not set. At a later
 * stage we will also handle USPS address cleanup and other formatting
 * issues
 *
 */

define( 'THROTTLE_REQUESTS', 0 );

function run( ) {
    session_start( );                               

    require_once '../civicrm.config.php'; 
    require_once 'CRM/Core/Config.php'; 
    
    $config =& CRM_Core_Config::singleton(); 
    
    require_once 'Console/Getopt.php';
    $shortOptions = "n:p:s:e:k:g:parse";
    $longOptions  = array( 'name=', 'pass=', 'key=', 'start=', 'end=', 'geocoding=', 'parse=' );
    
    $getopt  = new Console_Getopt( );
    $args = $getopt->readPHPArgv( );
    
    array_shift( $args );
    list( $valid, $dontCare ) = $getopt->getopt2( $args, $shortOptions, $longOptions );
    
    $vars = array(
                  'start'     => 's',
                  'end'       => 'e',
                  'name'      => 'n',
                  'pass'      => 'p',
                  'key'       => 'k',
                  'geocoding' => 'g',
                  'parse'     => 'parse' );
    
    foreach ( $vars as $var => $short ) {
        $$var = null;
        foreach ( $valid as $v ) {
            if ( $v[0] == $short || $v[0] == "--$var" ) {
                $$var = $v[1];
                break;
            }
        }
        if ( ! $$var ) {
            $$var = CRM_Utils_Array::value( $var, $_REQUEST );
        }
        $_REQUEST[$var] = $$var;
    }
    
    // this does not return on failure
    // require_once 'CRM/Utils/System.php';
    CRM_Utils_System::authenticateScript( true, $name, $pass );
    
    // do check for geocoding.
    $processGeocode = false;
    if ( empty( $config->geocodeMethod ) ) {
        if ( $geocoding == 'true' ) {
            echo ts( 'Error: You need to set a mapping provider under Global Settings' );
            exit( ); 
        }
    } else {
        $processGeocode = true;
        // user might want to over-ride.
        if ( $geocoding == 'false' ) {
            $processGeocode = false;
        }
    }
    
    // do check for parse street address.
    require_once 'CRM/Core/BAO/Preferences.php';
    $parseAddress = CRM_Utils_Array::value( 'street_address_parsing',
                                            CRM_Core_BAO_Preferences::valueOptions( 'address_options' ), false );
    $parseStreetAddress = false;
    if ( !$parseAddress ) {
        if ( $parse == 'true' ) {
            echo ts( 'Error: You need to set a Street Address Parsing under Global Settings >> Address Settings.' );
            exit( );
        }
    } else {
        $parseStreetAddress = true;
        // user might want to over-ride.
        if ( $parse == 'false' ) {
            $parseStreetAddress = false;
        }
    }
    
    // don't process.
    if ( !$parseStreetAddress && !$processGeocode ) {
        echo ts( 'Error: Both Geocode mapping as well as Street Address Parsing are disabled.' );
        exit( );
    }
    
    $config->userFramework      = 'Soap'; 
    $config->userFrameworkClass = 'CRM_Utils_System_Soap'; 
    $config->userHookClass      = 'CRM_Utils_Hook_Soap';
    
    // we have an exclusive lock - run the mail queue
    processContacts( $config, $processGeocode, $parseStreetAddress, $start, $end );
}


function processContacts( &$config, $processGeocode, $parseStreetAddress, $start = null, $end = null ) 
{
    $contactClause = array( );
    if ( $start ) {
        $contactClause[] = "c.id >= $start";
    }
    if ( $end ) {
        $contactClause[] = "c.id <= $end";
    }
    if ( ! empty( $contactClause ) ) {
        $contactClause = ' AND ' . implode( ' AND ', $contactClause );
    } else {
        $contactClause = null;
    }
    
    $query = "
SELECT     c.id,
           a.id as address_id,
           a.street_address,
           a.city,
           a.postal_code,
           s.name as state,
           o.name as country
FROM       civicrm_contact  c
INNER JOIN civicrm_address        a ON a.contact_id = c.id
INNER JOIN civicrm_country        o ON a.country_id = o.id
LEFT  JOIN civicrm_state_province s ON a.state_province_id = s.id
WHERE      c.id           = a.contact_id
  AND      ( a.geo_code_1 is null OR a.geo_code_1 = 0 )
  AND      ( a.geo_code_2 is null OR a.geo_code_2 = 0 )
  AND      a.country_id is not null
  $contactClause
ORDER BY a.id
";
    
    $totalGeocoded = $totalAddresses = $totalAddressParsed = 0;
    
    $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
    
    if ( $processGeocode ) {
        require_once( str_replace('_', DIRECTORY_SEPARATOR, $config->geocodeMethod ) . '.php' );
    }
    
    require_once 'CRM/Core/DAO/Address.php';
    require_once 'CRM/Core/BAO/Address.php';
    
    while ( $dao->fetch( ) ) {
        $totalAddresses++;
        $params = array( 'street_address'    => $dao->street_address,
                         'postal_code'       => $dao->postal_code,
                         'city'              => $dao->city,
                         'state_province'    => $dao->state,
                         'country'           => $dao->country );
        
        $addressParams = array( );
        
        // process geocode.
        if ( $processGeocode ) {
            // loop through the address removing more information
            // so we can get some geocode for a partial address
            // i.e. city -> state -> country
            
            $maxTries = 5;
            do {
                if ( defined( 'THROTTLE_REQUESTS' ) &&
                     THROTTLE_REQUESTS ) {
                    usleep( 50000 );
                }
                
                eval( $config->geocodeMethod . '::format( $params, true );' );
                array_shift( $params );
                $maxTries--;
            } while ( ( ! isset( $params['geo_code_1'] ) ) &&
                      ( $maxTries > 1 ) );
            
            if ( isset( $params['geo_code_1'] ) ) {
                $totalGeocoded++;
                $addressParams['geo_code_1'] = $params['geo_code_1'];
                $addressParams['geo_code_2'] = $params['geo_code_2'];
            }
        }
        
        // parse street address
        if ( $parseStreetAddress ) {
            $parsedFields = CRM_Core_BAO_Address::parseStreetAddress( $dao->street_address );
            $success = true;
            foreach ( $parsedFields as $parseVal ) {
                if ( empty( $parseVal ) ) {
                    $success = false;
                    break;
                }
            }
            
            // do check for all elements.
            if ( $success ) {
                $totalAddressParsed++;
                $addressParams = array_merge( $addressParams, $parsedFields ); 
            }
        }
        
        // finally update address object.
        if ( !empty( $addressParams ) ) {
            $address = new CRM_Core_DAO_Address( );
            $address->id = $dao->address_id;
            $address->copyValues( $parsedFields );
            $address->save( );
            $address->free( );
        }
    }
    
    echo ts( "Addresses Evaluated: $totalAddresses\n" );
    if ( $processGeocode ) {
        echo ts( "Addresses Geocoded : $totalGeocoded\n" );
    }
    if ( $parseStreetAddress ) {
        echo ts( "Street Address Parsed : $totalAddressParsed\n" );
    }
    
    return;
}

run( );


