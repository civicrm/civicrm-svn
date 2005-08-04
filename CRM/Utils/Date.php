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
 * Date utilties
 */
class CRM_Utils_Date {

    /**
     * format a date by padding it with leading '0'.
     *
     * @param array  $date ('Y', 'M', 'd')
     * @param string $separator the seperator to use when formatting the date
     * @return string - formatted string for date
     *
     * @static
     */
    static function format( $date, $separator = '' )
    {
        if ( ! $date || ! is_array( $date ) || ( ! $date['Y'] ) ) {
            return null;
        }

        if ( CRM_Utils_Array::value( 'M', $date ) ) {
            $date['M'] = (int ) $date['M'];
            $date['M'] = ($date['M'] < 10) ? '0' . $date['M'] : $date['M'];
        } else {
            $date['M'] = '00';
        }

        if ( CRM_Utils_Array::value( 'd', $date ) ) {
            $date['d'] = (int ) $date['d'];
            $date['d'] = ($date['d'] < 10) ? '0' . $date['d'] : $date['d'];
        } else {
            $date['d'] = '00';
        }

        $time = '';
        if (CRM_Utils_Array::value( 'H', $date ) || CRM_Utils_Array::value( 'h', $date ) ||
            CRM_Utils_Array::value( 'i', $date ) || CRM_Utils_Array::value( 's', $date )) {
            // we have time too.. 
            if (CRM_Utils_Array::value( 'h', $date )) {
                if ($date['A'] == 'PM') {
                    if ($date['h'] != 12 ) {
                        $date['h'] = $date['h'] + 12;
                    }
                }
                if (CRM_Utils_Array::value( 'A', $date ) == 'AM' && CRM_Utils_Array::value( 'h', $date ) == 12) {
                    $date['h'] = '00';
                }
                
                $date['h'] = (int ) $date['h'];
                $date['h'] = ($date['h'] < 10) ? '0' . $date['h'] : $date['h'];
            } else {
                $date['h'] = '00';
            }

            // in 24-hour format the hour is under the 'H' key
            if (CRM_Utils_Array::value('H', $date)) {
                $date['H'] = (int) $date['H'];
                $date['H'] = $date['H'] < 10 ? '0' . $date['H'] : $date['H'];
            } else {
                $date['H'] = '00';
            }

            if (CRM_Utils_Array::value( 'i', $date )) {
                $date['i'] = (int ) $date['i'];
                $date['i'] = ($date['i'] < 10) ? '0' . $date['i'] : $date['i'];
            } else {
                $date['i'] = '00';
            }
            if ($date['h'] == '00' and $date['H'] != '00') {
                $date['h'] = $date['H'];
            }

            if (CRM_Utils_Array::value( 's', $date )) {
                $date['s'] = (int ) $date['s'];
                $date['s'] = ($date['s'] < 10) ? '0' . $date['s'] : $date['s'];
            } else {
                $date['s'] = '00';
            }
            $time = $date['h'] . $seperator . $date['i'] . $seperator . $date['s'];
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
        $value['Y'] = $value['M'] = $value['d'] = null;

        if ( empty( $date ) ) {
            return $value;
        }

        list( $year, $mon, $day ) = explode( $separator, $date, 3 );

        $value = array( );

        if ( is_numeric( $year ) && $year > 0 ) {
            $value['Y'] = $year;
        }

        if ( is_numeric( $mon ) && $mon > 0 ) {
            $value['M'] = $mon;
        }

        if ( is_numeric( $day ) && $day > 0 ) {
            $value['d'] = $day;
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
                $abbrMonthNames[$i] = strftime('%b', mktime(0, 0, 0, $i));
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
                $fullMonthNames[$i] = strftime('%B', mktime(0, 0, 0, $i));
            }
        }
        return $fullMonthNames;
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
    static function customFormat($dateString, $format = '%B %E%f, %Y %I:%M %P')
    {
        // 1-based (January) month names arrays
        $abbrMonths = self::getAbbrMonthNames();
        $fullMonths = self::getFullMonthNames();

        if ($dateString and $format) {

            $year   = (int) substr($dateString,  0, 4);
            $month  = (int) substr($dateString,  5, 2);
            $day    = (int) substr($dateString,  8, 2);
            $hour24 = (int) substr($dateString, 11, 2);
            $minute = (int) substr($dateString, 14, 2);

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
                          '%I' => $hour12 > 9 ? $hour12 : '0' . $hour12,
                          '%k' => $hour24 > 9 ? $hour24 : ' ' . $hour24,
                          '%l' => $hour12 > 9 ? $hour12 : ' ' . $hour12,
                          '%m' => $month  > 9 ? $month  : '0' . $month,
                          '%M' => $minute > 9 ? $minute : '0' . $minute,
                          '%p' => strtolower($type),
                          '%P' => $type,
                          '%Y' => $year
                          );

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
     *
     * @return string  format string in PHP notation
     *
     * @static
     */
    static function posixToPhp($format)
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
        return strtr($format, $replacements);
    }

}

?>
