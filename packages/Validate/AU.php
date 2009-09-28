<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Specific validation methods for data used in Australia
 *
 * PHP Versions 4 and 5
 *
 * This source file is subject to the New BSD license, That is bundled
 * with this package in the file LICENSE, and is available through
 * the world-wide-web at
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the new BSDlicense and are unable
 * to obtain it through the world-wide-web, please send a note to
 * pajoye@php.net so we can mail you a copy immediately.
 *
 * @category  Validate
 * @package   Validate_AU
 * @author    Daniel O'Connor <daniel.oconnor@gmail.com>
 * @author    Tho Nguyen <tho.nguyen@itexperts.com.au>
 * @author    Alex Hayes <ahayes@wcg.net.au>
 * @author    Byron Adams <byron.adams54@gmail.com>
 * @copyright 1997-2005 Daniel O'Connor
 * @copyright 2006 Alex Hayes
 * @copyright 2006 Byron Adams
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version   CVS: $Id: AU.php,v 1.18 2008/10/26 17:53:50 clockwerx Exp $
 * @link      http://pear.php.net/package/Validate_AU
 */

/**
 * Data validation class for Australia
 *
 * Contains code from Validate_AT, Validate_UK and Validate_NZ
 *
 * This class provides methods to validate:
 *  - Postal code
 *  - Phone number
 *  - Australian Business Number
 *  - Australian Company Number
 *  - Tax File Number
 *  - Australian Regional codes
 *
 * @category  Validate
 * @package   Validate_AU
 * @author    Daniel O'Connor <daniel.oconnor@gmail.com>
 * @author    Tho Nguyen <tho.nguyen@itexperts.com.au>
 * @author    Alex Hayes <ahayes@wcg.net.au>
 * @author    Byron Adams <byron.adams54@gmail.com>
 * @copyright 1997-2005 Daniel O'Connor
 * @copyright 2006 Alex Hayes
 * @copyright 2006 Byron Adams
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Validate_AU
 */

class Validate_AU
{

    /**
     * Validate Austrialian postal codes.
     *
     * @param string $postcode postcode to validate
     * @param bool   $strong   optional; strong checks against a list of postcodes
     * @param string $dataDir  optional; name of directory datafile is located in
     *
     * @access   public
     * @static   string  $postcodes
     * @return   bool    true if postcode is ok, false otherwise
     */
    function postalCode($postcode, $strong = false, $dataDir = null)
    {
        if ($strong) {
            static $postcodes;

            if (!isset($postcodes)) {
                if ($dataDir != null && (is_file($dataDir . '/AU_postcodes.txt'))) {
                    $file = $dataDir . '/AU_postcodes.txt';
                } else {
                    $file = 'packages/data/Validate_AU/data/AU_postcodes.txt';
                }
                $postcodes = array_map('trim', file($file));
            }

            return in_array((string)$postcode, $postcodes, true);
        }
        return preg_match('(^[0-9]{4}$)', $postcode);
    }

    /**
     * Validates Australian Regional Codes
     *
     * @param string $region regional code to validate
     *
     * @access    public
     * @static    array      $regions
     * @return    bool       Returns true on success, false otherwise
     */
    function region($region)
    {
        static $regions = array("ACT", "NSW", "NT", "QLD", "SA", "TAS", "VIC", "WA");
        return in_array(strtoupper($region), $regions);
    }

    /**
     * Validate a telephone number.
     *
     * Note that this function supports the following notations:
     *
     *     - Landline: 03 9999 9999
     *     - Mobile: 0400 000 000 (as above, but usually notated differently)
     *     - Indial: 131 812 / 1300 000 000 / 1800 000 000 / 1900 000 000
     *     - International: +61.3 9999 9999
     *
     * For International numbers, only +61 will be valid, as this is
     * Australia's dial code, and the format MUST be +61.3, where 3 represents
     * the state dial code, in this case, Victoria.
     *
     * Note: If the VALIDATE_AU_PHONENUMBER_STRICT flag is not supplied, then
     * all spaces, dashes and parenthesis are removed before validation. You
     * will have to strip these yourself if your data storage does not allow
     * these characters.
     *
     * @param string  $number  The telephone number
     * @param mixed[] $options A list of options
     *                          'strict'   => true - do not common characters
     *                          'national' => true - validate national numbers
     *                          'indial'   => true - 13, 1300, 1800, 1900
     *                                  numbers
     *                          'other'    => true - uncommon phone validations
     *                                  like premium sms, data and personal numbers
     *                          'international => true - international numbers
     *                                  for Australia (eg. +61.3 9999 9999)
     *
     * @static
     * @access    public
     * @return    bool
     *
     * @todo Check that $flags contains a valid flag.
     */
    function phoneNumber($number, $options = array('strict'        => false,
                                                   'national'      => true,
                                                   'indial'        => true,
                                                   'international' => true,
                                                   'other'         => true))
    {

        $preg = array();
        if (empty($options['strict'])) {
            $number = str_replace(array('(', ')', '-', ' '), '', $number);
        }

        if (!empty($options['national'])) {
             $preg[] = "(0[3478][0-9]{8})";
             $preg[] = "(02[3-9][0-9]{7})";
        }

        if (!empty($options['indial'])) {
            $preg[] = '(13[0-9]{4})';
            $preg[] = "(1[3|8|9]00[0-9]{6})";
        }

        if (!empty($options['international'])) {
             $preg[] = "(\+61\.[23478][0-9]{8})";
        }

        //Other numbers, like premium SMS
        if (!empty($options['other'])) {

            //Premium SMS
            $preg[] = "(19[0-9]{4,6})";

            //Universial Personal Phones
            $preg[] = "(0550[0-9]{6})"; //VOIP range (proposed)
            $preg[] = "(059[0-9]{7})";  //Enum testing numbers
            $preg[] = "(0500[0-9]{6})"; //"Find me anywhere"
                                        //(divert the number and
                                        // the caller pays the bill)



            //Data access providers
            $preg[] = "(0198[0-3][0-9]{5})";

        }

        if (!empty($preg)) {
            foreach ($preg as $pattern) {
                if (preg_match("/^" . $pattern . "$/", $number)) {
                    return true;
                }
            }
        }

        return false;

    }

    /**
     * Validate an Australian Company Number (ACN)
     *
     * The ACN is a nine digit number with the last digit
     * being a check digit calculated using a modified
     * modulus 10 calculation.
     *
     * @param string $acn ACN number to validate
     *
     * @access public
     * @return bool Returns true on success, false otherwise
     * @link   http://www.asic.gov.au/asic/asic_infoco.nsf/byheadline/Australian+Company+Number+(ACN)+Check+Digit
     */
    function acn($acn)
    {
        $weights = array(8, 7, 6, 5, 4, 3, 2, 1, 0);

        $acn    = preg_replace("/[^\d]/", "", $acn);
        $digits = str_split($acn);
        $sum    = 0;

        if (!ctype_digit($acn) || strlen($acn) != 9) {
            return false;
        }

        foreach ($digits as $key => $digit) {
            $sum += $digit * $weights[$key];
        }

        $remainder = $sum % 10;

        switch ($remainder) {
        case 0:
            $complement = 0 - $remainder;
            break;
        default:
            $complement = 10 - $remainder;
            break;
        }

        return ($digits[8] == $complement);
    }

    /**
     * Social Security Number.
     *
     * Australia does not have a social security number system,
     * the closest equivalent is a Tax File Number
     *
     * @param string $ssn ssn number to validate
     *
     * @access  public
     * @see     Validate_AU::tfn()
     * @return  bool    Returns true on success, false otherwise
     */
    function ssn($ssn)
    {
        return Validate_AU::tfn($ssn);
    }

    /**
     * Tax File Number (TFN)
     *
     * Australia does not have a social security number system,
     * the closest equivalent is a Tax File Number.
     *
     * @param string $tfn Tax File Number
     *
     * @access  public
     * @return  bool    Returns true on success, false otherwise
     * @link    http://en.wikipedia.org/wiki/Tax_File_Number
     */
    function tfn($tfn)
    {
        $weights = array(1, 4, 3, 7, 5, 8, 6, 9, 10);
        $length  = array("8", "9");

        $tfn = preg_replace("/[^\d]/", "", $tfn);
        $tfn = str_split($tfn);

        return Validate_AU::checkDigit($tfn, 11, $weights, $length);
    }

    /**
     * Australian Business Number (ABN).
     *
     * Validates an ABN using a modulus calculation
     *
     * @param string $abn ABN to validate
     *
     * @static
     * @access  public
     * @return  bool      true on success, otherwise false
     * @link    http://www.ato.gov.au/businesses/content.asp?doc=/content/13187.htm
     */
    function abn($abn)
    {
        $weights = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);
        $length  = array("11");

        $abn = preg_replace("/[^\d]/", "", $abn);
        $abn = str_split($abn);
        $abn[0]--;

        return Validate_AU::checkDigit($abn, 89, $weights, $length);
    }

    /**
     * Validate number against decimal checksum (check digit)
     *
     * A check digit is a form of redundancy check used
     * for error detection, the decimal equivalent of a
     * binary checksum. It consists of a single digit
     * computed from the other digits in the message.
     *
     * @param array $digits  Digits to check
     * @param int   $modulus Modulus
     * @param array $weights Array containing weighting
     * @param array $length  Length
     *
     * @access public
     * @return bool     true on success, otherwise false
     * @link   http://en.wikipedia.org/wiki/Check_digit
     */
    function checkDigit($digits, $modulus, $weights, $length)
    {
        $sum = 0;

        if (!in_array(count($digits), $length)) {
            return false;
        }

        foreach ($digits as $key => $digit) {
            $sum += $digit * $weights[$key];
        }

        return !($sum % $modulus);

    }
}
?>
