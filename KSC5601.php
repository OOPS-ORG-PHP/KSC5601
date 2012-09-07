<?php
/**
 * Project: KSC5601 :: convert character set between KSC5601 and UTF8
 * File:    KSC5601.php
 *
 * PHP version 5
 *
 * Copyright (c) 2009, JoungKyun.Kim <http://oops.org>
 *
 * LICENSE: BSD License
 *
 * KSC5601 pear package support to convert character set between UHC and UTF8
 * or between UHC and UCS2 or between UHC(or CP949) and NCR (Numeric character
 * reference) code. Also, Converting between UHC and NCR is enabled to print
 * unrecognized character that is out of KSX1001 range.
 *
 * @category   Charset
 * @package    KSC5601
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    BSD License
 * @version    CVS: $Id$
 * @link       http://pear.oops.org/package/KSC5601
 * @since      File available since Release 0.1
 * @filesource
 */

/**
 * import KSC5601_Common class that checked support or unsupport PHP iconv or
 * mbstring extensions.
 */
require_once 'KSC5601/Common.php';

// {{{ constant
/**#@+
 * @access public
 */
/**
 * Local charset string
 * @name LOC
 */
define ('LOC',    'loc');
/**
 * UTF8 charset string
 * @name UTF8
 */
define ('UTF8',   'utf8');
/**
 * EUC-KR charset string
 * @name EUC-KR
 */
define ('EUC-KR', 'euc-kr');
/**
 * CP949 Alias
 * @name UHC
 */
define ('UHC',    'cp949');
/**
 * CP949 charset string
 * @name CP949
 */
define ('CP949',  'cp949');
/**
 * UCS2 big endian charset string
 * @name UCS2
 */
define ('UCS2',   'ucs-2be');
/**
 * Numeric Code Reference string
 * @name NCR
 */
define ('NCR',    'ncr');
/**#@-*/
//}}}

/**
 * Main Class that support to convert character betwwen KSC5601 and UTF-8
 * @package KSC5601
 */
Class KSC5601
{
	// {{{ properties
	/**#@+
	 * @access private
	 */
	/**
	 * KSC5601_common object
	 * @var object
	 */
	private $chk;
	/*
	 * internal KSC5601 API object
	 * @var object
	 */
	private $obj;
	/**#@-*/
	// }}}

	// {{{ constructor
	/**
	 * Support iconv or mbstring extension, use KSC5601_ext internal class, or not
	 * support use KSC5601_pure internal class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct () {
		$this->chk = new KSC5601_Common;

		if ( $this->chk->is_extfunc () !== true ) {
			/**
			 * KSC5601_ext class method use iconv or mbstring extension
			 */
			require_once 'KSC5601/KSC5601_ext.php';
			$this->obj = new KSC5601_ext ($this->chk);
		} else {
			/**
			 * KSC5601_pure class method don't use iconv and mbstring extensions.
			 * This class is construct with pure php code and character set code
			 * tables.
			 */
			require_once 'KSC5601/KSC5601_pure.php';
			$this->obj = new KSC5601_pure;
		}
	}
	// }}}

	// {{{ function out_of_ksc1001 ($flag = false)
	/**
	 * Set whether convert hangul that is out of KSX1001 range. This method changes
	 * private $out_ksc1001 variable.
	 *
	 * @access  public
	 * @return  boolean Return 
	 * @param   boolean (optional) Defaults to false
	 *  <ol>
	 *      <li>true : When decode UTF-8, convert to NCR from hangul character that is out of KSX1001 range.</li>
	 *      <li>true : When encode NCR from UHC(CP949), convert to NCR with only hangul that is out of KSX1001 range.</li>
	 *      <li>false : No action</li>
	 *  </ol>
	 */
	function out_of_ksc1001 ($flag = false) {
		return $this->obj->out_of_ksx1001 ($flag);
	}
	// }}}

	// {{{ function is_utf8 ($string, $ascii_only_check)
	/**
	 * Check given string wheter utf8 or not.
	 *
	 * @access  public
	 * @return  boolean Given string is utf8, return true.
	 * @param   string  Given strings
	 * @param   boolean Check whether is ascii only or not
	 */
	function is_utf8 ($string, $ascii_only_check = false) {
		return $this->obj->is_utf8 ($string, $ascii_only_check);
	}
	// }}}

	// {{{ function is_ksc5601 ($string)
	/**
	 * Check given string wheter ksc5601 oj not.
	 *
	 * @access  public
	 * @return  boolean Given string is ksc5601, return true.
	 * @param   string  Given strings
	 */
	function is_ksc5601 ($string, $ksx1001 = false) {
		if ( strlen ($string) != 2 )
			return false;

		$c1 = ord ($string[0]);
		$c2 = ord ($string[1]);

		if ( ! ($c1 & 0x80) )
			return false;

		if ( $ksx1001 === true ) {
			if ( ($c1 > 0x80 && $c1 < 0xa2 && $c2 > 0x40 && $c2 < 0xff) ||
				 ($c1 > 0xa0 && $c1 < 0xc7 && $c2 > 0x40 && $c2 < 0xa1) ) {
				if ( $c2 < 0x41 || $c2 < 0x61 )
					return false;
				if ( $c2 > 0x5a && $c2 < 0x61 )
					return false;
				if ( $c2 > 0x7a && $c2 < 0x81 )
					return false;
			}
		} else {
			if ( ($c1 > 0x80 && $c1 < 0xa2 && $c2 > 0x40 && $c2 < 0xff) ||
				 ($c1 > 0xa0 && $c1 < 0xc7 && $c2 > 0x40 && $c2 < 0xa1) )
				return false;
		}

		return true;
	}
	// }}}

	// {{{ function is_ksx1001 ($string)
	/**
	 * Check given string wheter ksx1001 oj not.
	 *
	 * @access  public
	 * @return  boolean Given string is ksx1001, return true.
	 * @param   string  Given strings
	 */
	function is_ksx1001 ($string) {
		return self::is_ksc5601 ($string, true);
	}
	// }}}

	// {{{ function utf8 ($string, $to = UTF8)
	/**
	 * Convert between UHC and UTF-8
	 *
	 * @access  public
	 * @return  string
	 * @param   string  Given string.
	 * @param   string  (optional) Defaults to UTF8. Value is UTF8 or UHC constant.
	 *                  This parameter is not set or set with UTF8 constant, convert
	 *                  given string to UTF-8.
	 *
	 *                  Set to UHC constant, conert to uhc from utf-8. If intenal
	 *                  $out_ksx1001 variable is set true that means call
	 *                  KSC5601::out_of_ksx1001(true), convert to NCR hangul
	 *                  that is out of KSX1001 range.
	 * @see KSC5601::out_of_ksx1001()
	 */
	function utf8 ($string, $to = UTF8) {
		return $this->obj->utf8 ($string, $to);
	}
	// }}}

	// {{{ function ucs2 ($string, $to = UCS2, $asc = false)
	/**
	 * Convert between UHC and UCS2
	 *
	 * @access  public
	 * @return  string
	 * @param   string  Given string
	 * @param   string  (optional) Detauls to UCS2. Value is UCS2 or UHC constants.
	 *                  Set UCS2 constant, convert UHC to UCS2 hexical (for example, U+B620).
	 *                  Set UHC constant, convert UCS2 hexical to UHC.
	 * @param   boolean (optional) Defaults to false. This parameter is used only UHC -> UCS2 mode.
	 *                  Set true, convert all characters to UCS2 hexical. Set false, only convert
	 *                  hangul that is out of KSX1001 range to UCS hexical.
	 */
	function ucs2 ($string, $to = UCS2, $asc = false) {
		return $this->obj->ucs2 ($string, $to, $asc);
	}
	// }}}

	// {{{ function ncr ($string, $to = NCR, $enc = false)
	/**
	 * Convert between UHC and NCR (Numeric Code Reference)
	 *
	 * @access  public
	 * @return  string
	 * @param   string  Given string
	 * @param   string  (optional) Defaults to NCR constant. Value is NCR or UHC constants.
	 *                  Set NCR constant, convert UHC(CP949) to NCR code. Set UHC constant,
	 *                  convert NCR code to UHC(cp949).
	 * @param   boolean (optional) Defaults to false. This parameter is used only UHC -> NCR mode.
	 *                  Set false, only convert hangul that is out of KSX1001 range to NCR
	 *                  when internal $out_ksx1001 variable set true that meas called
	 *                  KSC5601::out_of_ksx1001(true).
	 *
	 *                  Set true, convert all character to NCR code.
	 */
	function ncr ($string, $to = NCR, $enc = false) {
		return $this->obj->ncr ($string, $to, $enc);
	}
	// }}}

	// {{{ function make_reverse_table ()
	/**
	 * Print php code for KSC5601 reverse table
	 * This method is used only developer for KSC5601 pure code.
	 *
	 * @access public
	 * @return void
	 * @param  void
	 */
	function make_reverse_table () {
		if ( $this->chk->is_extfunc () === false ) {
			$this->obj->make_reverse_table ();
		}
	}
	// }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
?>
