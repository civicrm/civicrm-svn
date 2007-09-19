<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
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
 * Date utilties
 */
class CRM_Utils_Date {

    /**
     * format a date by padding it with leading '0'.
     *
     * @param array  $date ('Y', 'M', 'd')
     * @param string $separator   the seperator to use when formatting the date
     * @param string $invalidDate what to return if the date is invalid
     *
     * @return string - formatted string for date
     *
     * @static
     */
    static function format( $date, $separator = '', $invalidDate = 0 )
    {
        if ( is_numeric($date) && ( strlen($date) == 8 ) ) {
            return $date;
        }

        if ( ! is_array( $date ) ||
             CRM_Utils_System::isNull( $date ) ||
             empty( $date['Y'] ) ) {
            return $invalidDate;
        }

        $date['Y'] = (int ) $date['Y'];
        if ( $date['Y'] < 1000 || $date['Y'] > 2999 ) {
            return $invalidDate;
        }

            
        if ( CRM_Utils_Array::value( 'M', $date ) ) {
            $date['M'] = (int ) $date['M'];
            if ( $date['M'] < 1 || $date['M'] > 12 ) {
                return $invalidDate;
            }
        } else {
            $date['M'] = 1;
        }

        if ( CRM_Utils_Array::value( 'd', $date ) ) {
            $date['d'] = (int ) $date['d'];
        } else {
            $date['d'] = 1;
        }

        if ( ! checkdate( $date['M'], $date['d'], $date['Y'] ) ) {
            return $invalidDate;
        }

        $date['M'] = sprintf( '%02d', $date['M'] );
        $date['d'] = sprintf( '%02d', $date['d'] );

        $time = '';
        if (CRM_Utils_Array::value( 'H', $date ) != null ||
            CRM_Utils_Array::value( 'h', $date ) != null ||
            CRM_Utils_Array::value( 'i', $date ) != null ||
            CRM_Utils_Array::value( 's', $date ) != null) {
            // we have time too.. 
            if (CRM_Utils_Array::value( 'h', $date )) {
                if ($date['A'] == 'PM') {
                    if ($date['h'] != 12 ) {
                        $date['h'] = $date['h'] + 12;
                    }
                }
                if ( CRM_Utils_Array::value( 'A', $date ) == 'AM' &&
                     CRM_Utils_Array::value( 'h', $date ) == 12 ) {
                    $date['h'] = '00';
                }
                
                $date['h'] = (int ) $date['h'];
            } else {
                $date['h'] = 0;
            }

            // in 24-hour format the hour is under the 'H' key
            if (CRM_Utils_Array::value('H', $date)) {
                $date['H'] = (int) $date['H'];
            } else {
                $date['H'] = 0;
            }

            if (CRM_Utils_Array::value( 'i', $date )) {
                $date['i'] = (int ) $date['i'];
            } else {
                $date['i'] = 0;
            }

            if ($date['h'] == 0 && $date['H'] != 0) {
                $date['h'] = $date['H'];
            }

            if (CRM_Utils_Array::value( 's', $date )) {
                $date['s'] = (int ) $date['s'];
            } else {
                $date['s'] = 0;
            }

            $date['h'] = sprintf( '%02d', $date['h'] );
            $date['i'] = sprintf( '%02d', $date['i'] );
            $date['s'] = sprintf( '%02d', $date['s'] );

            if ( $separator ) {
                $time = '&nbsp;';
            }
            $time .= $date['h'] . $separator . $date['i'] . $separator . $date['s'];
        }

        return $date['Y'] . $separator . $date['M'] . $separator . $date['d'] . $time;
    }

    /**
     * given a string in mysql format, transform the string 
     * into qf format
     *
     * @param string $date a mysql type date string
     *
     * @return array       a qf formatted date array
     * @static
     */     
    static function &unformat( $date, $separator = '-' ) {
        $value = array( );
        if ( empty( $date ) ) {
            return $value;
        }

        $value['Y'] = $value['M'] = $value['d'] = null;

        if ( $separator != '' ) {
            list( $year, $mon, $day ) = explode( $separator, $date, 3 );
        } else {
            $year = substr( $date, 0, 4 );
            $mon  = substr( $date, 4, 2 );
            $day  = substr( $date, 6, 2 );
        } 
        
        if( strlen( $day ) > 2 ) {
            if( substr_compare( $day,':', 3 ) ) {
                $time = substr( $day, 3, 8 );
                $day  = substr( $day, 0, 2 );
                list( $hr, $min, $sec ) = explode( ':', $time, 3 );
            }
        }
        
        if ( is_numeric( $year ) && $year > 0 ) {
            $value['Y'] = $year;
        }

        if ( is_numeric( $mon ) && $mon > 0 ) {
            $value['M'] = $mon;
        }

        if ( is_numeric( $day ) && $day > 0 ) {
            $value['d'] = $day;
        }

        if ( isset( $hr ) && is_numeric( $hr ) && $hr >= 0 ) {
            $value['h'] = $hr;
            $value['H'] = $hr;
            if( $hr > 12 ) {
                $value['h'] -= 12;
                $value['H'] = $hr;
                $value['A'] = 'PM';
            } else if( $hr == 0 ) {
                $value['h'] = 12;
                $value['A'] = 'AM';
            } else {
                $value['A'] = 'AM';
            }
        }
        
        if ( isset( $min ) && is_numeric( $min ) && $min >= 0 ) {
            $value['i'] = $min;
        }
        return $value;
    }

    /**
     * return abbreviated weekday names according to the locale
     *
     * @return array  0-based array with abbreviated weekday names
     *
     * @static
     */
    static function &getAbbrWeekdayNames()
    {
        static $abbrWeekdayNames;
        if (!isset($abbrWeekdayNames)) {

            // set LC_TIME and build the arrays from locale-provided names
            // June 1st, 1970 was a Monday
            CRM_Core_I18n::setLcTime();
            for ($i = 0; $i < 7; $i++) {
                $abbrWeekdayNames[$i] = strftime('%a', mktime(0, 0, 0, 6, $i, 1970));
            }
        }
        return $abbrWeekdayNames;
    }

    /**
     * return full weekday names according to the locale
     *
     * @return array  0-based array with full weekday names
     *
     * @static
     */
    static function &getFullWeekdayNames()
    {
        static $fullWeekdayNames;
        if (!isset($fullWeekdayNames)) {

            // set LC_TIME and build the arrays from locale-provided names
            // June 1st, 1970 was a Monday
            CRM_Core_I18n::setLcTime();
            for ($i = 0; $i < 7; $i++) {
                $fullWeekdayNames[$i] = strftime('%A', mktime(0, 0, 0, 6, $i, 1970));
            }
        }
        return $fullWeekdayNames;
    }

    /**
     * return abbreviated month names according to the locale
     *
     * @return array  1-based array with abbreviated month names
     *
     * @static
     */
    static function &getAbbrMonthNames()
    {
        static $abbrMonthNames;
        if (!isset($abbrMonthNames)) {

            // set LC_TIME and build the arrays from locale-provided names
            CRM_Core_I18n::setLcTime();
            for ($i = 1; $i <= 12; $i++) {
                $abbrMonthNames[$i] = strftime('%b', mktime(0, 0, 0, $i, 10, 1970 ));
            }
        }
        return $abbrMonthNames;
    }

    /**
     * return full month names according to the locale
     *
     * @return array  1-based array with full month names
     *
     * @static
     */
    static function &getFullMonthNames()
    {
        static $fullMonthNames;
        if (!isset($fullMonthNames)) {

            // set LC_TIME and build the arrays from locale-provided names
            CRM_Core_I18n::setLcTime();
            for ($i = 1; $i <= 12; $i++) {
                $fullMonthNames[$i] = strftime('%B', mktime(0, 0, 0, $i, 10, 1970));
            }
        }
        return $fullMonthNames;
    }

    static function unixTime( $string ) {
        if ( empty( $string ) ) {
            return 0;
        }
        $v = self::unformat( $string );

        if ( empty( $v ) ) {
            return 0;
        }

        if ( $v['A'] == 'PM' ) {
            $v['h'] += 12;
        }

        return mktime( $v['h'], $v['i'], 59, $v['M'], $v['d'], $v['Y'] );
    }

    /**
     * create a date and time string in a provided format
     *
     * %b - abbreviated month name ('Jan'..'Dec')
     * %B - full month name ('January'..'December')
     * %d - day of the month as a decimal number, 0-padded ('01'..'31')
     * %e - day of the month as a decimal number, blank-padded (' 1'..'31')
     * %E - day of the month as a decimal number ('1'..'31')
     * %f - English ordinal suffix for the day of the month ('st', 'nd', 'rd', 'th')
     * %H - hour in 24-hour format, 0-padded ('00'..'23')
     * %I - hour in 12-hour format, 0-padded ('01'..'12')
     * %k - hour in 24-hour format, blank-padded (' 0'..'23')
     * %l - hour in 12-hour format, blank-padded (' 1'..'12')
     * %m - month as a decimal number, 0-padded ('01'..'12')
     * %M - minute, 0-padded ('00'..'60')
     * %p - lowercase ante/post meridiem ('am', 'pm')
     * %P - uppercase ante/post meridiem ('AM', 'PM')
     * %Y - year as a decimal number including the century ('2005')
     * 
     * @param string $date    date and time in 'YYYY-MM-DD hh:mm:ss' format
     * @param string $format  the output format
     *
     * @return string  the $format-formatted $date
     *
     * @static
     */
    static function customFormat($dateString, $format = null)
    {
        // 1-based (January) month names arrays
        $abbrMonths = self::getAbbrMonthNames();
        $fullMonths = self::getFullMonthNames();

        if (!$format) {
            $config =& CRM_Core_Config::singleton();
            if ( strpos($dateString, '-') ) {
                $month  = (int) substr($dateString,  5, 2);
                $day    = (int) substr($dateString,  8, 2);
            } else {
                $month  = (int) substr($dateString,  4, 2);
                $day    = (int) substr($dateString,  6, 2);
            }

            if (strlen($dateString) > 10) {
                $format = $config->dateformatDatetime;
            } elseif ($day > 0) {
                $format = $config->dateformatFull;
            } elseif ($month > 0) {
                $format = $config->dateformatPartial;
            } else {
                $format = $config->dateformatYear;
            }
        }

        if ($dateString) {
            if ( strpos($dateString, '-') ) {
                $year   = (int) substr($dateString,  0, 4);
                $month  = (int) substr($dateString,  5, 2);
                $day    = (int) substr($dateString,  8, 2);
                
                $hour24 = (int) substr($dateString, 11, 2);
                $minute = (int) substr($dateString, 14, 2);
            } else {
                $year   = (int) substr($dateString,  0, 4);
                $month  = (int) substr($dateString,  4, 2);
                $day    = (int) substr($dateString,  6, 2);
                
                $hour24 = (int) substr($dateString, 9, 2);
                $minute = (int) substr($dateString, 12, 2);
            }
            
            if     ($day % 10 == 1 and $day != 11) $suffix = 'st';
            elseif ($day % 10 == 2 and $day != 12) $suffix = 'nd';
            elseif ($day % 10 == 3 and $day != 13) $suffix = 'rd';
            else $suffix = 'th';
            
            if ($hour24 < 12) {
                if ($hour24 == 00) $hour12 = 12;
                else $hour12 = $hour24;
                $type = 'AM';
            } else {
                if ($hour24 == 12) $hour12 = 12;
                else $hour12 = $hour24 - 12;
                $type = 'PM';
            }
            
            $date = array(
                          '%b' => $abbrMonths[$month],
                          '%B' => $fullMonths[$month],
                          '%d' => $day > 9 ? $day : '0' . $day,
                          '%e' => $day > 9 ? $day : ' ' . $day,
                          '%E' => $day,
                          '%f' => $suffix,
                          '%H' => $hour24 > 9 ? $hour24 : '0' . $hour24,
                          '%h' => $hour12 > 9 ? $hour12 : '0' . $hour12,
                          '%I' => $hour12 > 9 ? $hour12 : '0' . $hour12,
                          '%k' => $hour24 > 9 ? $hour24 : ' ' . $hour24,
                          '%l' => $hour12 > 9 ? $hour12 : ' ' . $hour12,
                          '%m' => $month  > 9 ? $month  : '0' . $month,
                          '%M' => $minute > 9 ? $minute : '0' . $minute,
                          '%i' => $minute > 9 ? $minute : '0' . $minute,
                          '%p' => strtolower($type),
                          '%P' => $type,
                          '%A' => $type,
                          '%Y' => $year
                          );
            //CRM_Core_Error::debug('f',$format);
            //CRM_Core_Error::debug('d',$date);
            return strtr($format, $date);

        } else {
            return '';
        }

    }

    /**
     * converts the format string from POSIX notation to PHP notation
     *
     * example: converts '%Y-%m-%d' to 'Y-M-d'
     * note: the blank-padded sequences are converted to non-blank-padded ones
     *
     * @param string $format  format string in POSIX % notation
     * @param array  $filter  only include these elements for the date string
     *
     * @return string  format string in PHP notation
     *
     * @static
     */
    static function posixToPhp($format, $filter = null)
    {
        static $replacements = array(
            '%b' => 'M',
            '%B' => 'F',
            '%d' => 'd',
            '%e' => 'j',
            '%E' => 'j',
            '%f' => 'S',
            '%H' => 'H',
            '%I' => 'h',
            '%k' => 'G',
            '%l' => 'g',
            '%m' => 'm',
            '%M' => 'i',
            '%p' => 'a',
            '%P' => 'A',
            '%Y' => 'Y'
        );
        if ( $filter ) {
            $filteredReplacements = $replacements;
            foreach ( $replacements as $key => $value ) {
                if ( in_array( $value, $filter ) ) {
                    $filteredReplacements[$key] = $value;
                } else {
                    $filteredReplacements[$key] = null;
                }
            }
            return  strtr($format, $filteredReplacements);
        } else {
            return strtr($format, $replacements);
        }
    }

    /**
     * converts the date/datetime from MySQL format to ISO format
     *
     * @param string $mysql  date/datetime in MySQL format
     * @return string        date/datetime in ISO format
     * @static
     */
    static function mysqlToIso($mysql)
    {
        $year   = substr($mysql,  0, 4);
        $month  = substr($mysql,  4, 2);
        $day    = substr($mysql,  6, 2);
        $hour   = substr($mysql,  8, 2);
        $minute = substr($mysql, 10, 2);
        $second = substr($mysql, 12, 2);
        
        $iso = '';
        if ($year)             $iso .= "$year";
        if ($month) {
            $iso .= "-$month";
            if ( $day ) {
                $iso .= "-$day";
            }
        }

        if ($hour) {
            $iso .= " $hour";
            if ($minute) {
                $iso .= ":$minute";
                if ($second) {
                    $iso .= ":$second";
                }
            }
        }
        return $iso;
    }

    /**
     * converts the date/datetime from ISO format to MySQL format
     *
     * @param string $iso  date/datetime in ISO format
     * @return string      date/datetime in MySQL format
     * @static
     */
    static function isoToMysql($iso)
    {
        $dropArray = array('-' => '', ':' => '', ' ' => '');
        return strtr($iso, $dropArray);
    }

    /**
     * converts the any given date to default date format.
     *
     * @param array  $params     has given date-format
     * @param int    $dateType   type of date  
     * @param string $dateParam  index of params
     * @static
     */
    function convertToDefaultDate( &$params, $dateType, $dateParam ) {
        
        $now = getDate();
        $cen = substr($now['year'],  0, 2);
        
        if ($params[$dateParam]) {
            $value = $params[$dateParam];
        }
        
        switch( $dateType ) {
            
        case 1 :
            if ( ! preg_match('/^\d\d\d\d-?\d\d-?\d\d$/', $value) ) {
                return false;
            } else {
                return true;
            }
        case 2 :
            if ( ! preg_match('/^\d\d[-\/]\d\d[-\/]\d\d$/', $value) ) {
                return false;
            }
            break;
        case 4 :
            if ( ! preg_match('/^\d\d[-\/]\d\d[-\/]\d\d\d\d$/', $value ) ) {
                return false;
            }
            break;
        case 8 :
            if ( ! preg_match('/^[A-Za-z]*[ \t]?\d\d\,[ \t]?\d\d\d\d$/', $value ) ) {
                return false;
            }
            break;
        case 16 :
            if ( ! preg_match('/^\d\d-[A-Za-z]{3}-\d\d$/', $value )) {
                return false; 
            }
            break;
        }
        
        if ( $dateType == 2 || $dateType == 4) {
            $year   = (int) substr($value,  6, $dateType);
            $month  = (int) substr($value,  0, 2);
            $day    = (int) substr($value,  3, 2);
        }
        if ( $dateType == 8 ) {
            $dateArray = explode(' ',$value);
            $dateArray[1] = (int) substr($dateArray[1], 0, 2); //ignore comma(,) 
            
            $monthInt = 0;
            $fullMonths = self::getFullMonthNames();
            foreach ($fullMonths as $key => $val) {
                if ($dateArray[0] == $val) {
                    $monthInt = $key; 
                    break;
                }
            }
            if (!$monthInt) {
                $abbrMonths = self::getAbbrMonthNames();
                foreach ($abbrMonths as $key => $val) {
                    if ($dateArray[0] == $val) {
                        $monthInt = $key; 
                        break;
                    }
                }
            }
            $year   = (int) $dateArray[2];
            $day    = (int) $dateArray[1];
            $month  = (int) $monthInt;
        }
        if ( $dateType == 16 ) {
            $dateArray = explode('-',$value);
            
            $monthInt = 0;
            $fullMonths = self::getFullMonthNames();
            foreach ($fullMonths as $key => $val) {
                if ($dateArray[1] == $val) {
                    $monthInt = $key; 
                    break;
                }
            }
            if (!$monthInt) {
                $abbrMonths = self::getAbbrMonthNames();
                foreach ($abbrMonths as $key => $val) {
                    if ($dateArray[1] == $val) {
                        $monthInt = $key; 
                        break;
                    }
                }
            }
            $year   = (int) $dateArray[2];
            $day    = (int) $dateArray[0];
            $month  = (int) $monthInt;
        }
        
        $year  = ($year < 10) ? "0" . "$year"  : $year;
        $month = ($month < 10)? "0" . "$month" : $month;
        $day   = ($day < 10)  ? "0" . "$day"   : $day;
        $year  = ($year < 100)? $cen . "$year" : $year;
        
        if ($params[$dateParam]) {
            $params[$dateParam] = "$year$month$day";
        }
        return true;
    }

    static function isDate( &$date ) {
        if ( ! is_array( $date )                    ||
             CRM_Utils_System::isNull( $date )      ||
             ! CRM_Utils_Array::value( 'Y', $date ) ||
             ! CRM_Utils_Array::value( 'M', $date ) ||
             ! CRM_Utils_Array::value( 'd', $date ) ) {
            return false;
        }
        return true;
    }

    static function overdue( $date, $now = null ) {
        $mysqlDate = self::isoToMysql( $date );
        if ( ! $now ) {
            $now = date( 'YmdHis' );
        } else {
            $now = self::isoToMysql( $now );
        }

        return ( $mysqlDate >= $now ) ? false : true;
    }
    
    /**
     * Function to get customized today
     *
     * This function is used for getting customized today. To get
     * actuall today pass 'dayParams' as null. or else pass the day,
     * month, year values as array values
     * Example: $dayParams = array( 'day' => '25', 'month' => '10',
     *                              'year' => '2007' ); 
     * 
     * @param  Array  $dayParams   Array of the day, month, year
     *                             values.
     * @param  string $format      expected date format( default
     *                             format is 2007-12-21 )
     * 
     * @return string  Return the customized todays date (Y-m-d)
     * @static
     */
    static function getToday( $dayParams = null, $format = "Y-m-d" )
    {
        if ( is_null( $dayParams ) || empty( $dayParams ) ) {
            $today = date( $format );
        } else {
            $today = date( $format, mktime( 0, 0, 0, 
                                            $dayParams['month'], 
                                            $dayParams['day'], 
                                            $dayParams['year'] ) );
        }
        
        return $today;
    }

    static function getAllDefaultValues( &$defaults, $format = null, $time = null ) {
        if ( ! $format ) {
            // lets include EVERYTHING for now
            $format = 'a-A-d-h-H-i-g-G-j-M-S-Y';
        }
        // always include 'm' (see hack for QF below)
        $format .= '-m';

        if ( ! $time ) {
            $time = time( );
        }

        $val = date( $format, $time );
        $values = explode( '-', $val    );
        $keys   = explode( '-', $format );
        if ( count( $values ) != count( $keys ) ) {
            CRM_Core_Error::fatal( ts( 'Please contact CiviCRM support' ) );
        }
        for ( $i = 0; $i < count( $values ); $i++ ) {
            $defaults[$keys[$i]] = $values[$i];
        }
        // for some strange reason QF wants it as M, so we oblige for now
        $defaults['M'] = $defaults['m'];
    }
}

?>