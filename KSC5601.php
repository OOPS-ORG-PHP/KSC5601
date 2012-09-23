<?php
/**
 * Project: KSC5601 :: convert character set between KSC5601 and UTF8
 * File:    KSC5601.php
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
 * @version    $Id$
 * @link       http://pear.oops.org/package/KSC5601
 * @since      File available since Release 0.1
 * @filesource
 * @example    pear_KSC5601/test/test.php Sample codes of KSC5601 class
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

	// {{{ function out_of_ksx1001 ($flag = false)
	/**
	 * Set whether convert hangul that is out of KSX1001 range. This method changes
	 * private $out_ksx1001 variable.
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
	function out_of_ksx1001 ($flag = false) {
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
			} else
				return false;
		} else {
			if ( ! (($c1 > 0x80 && $c1 < 0xa2 && $c2 > 0x40 && $c2 < 0xff) ||
				 ($c1 > 0xa0 && $c1 < 0xc7 && $c2 > 0x40 && $c2 < 0xa1)) )
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

	// {{{ (string|false) substr ($str, $start, $len)
	/**
	 * 지정된 시작지점에서 지정될 길이만큼의 문자열을 반환한다.
	 *
	 * EUC-KR과 UTF-8을 모두 지원하며, UTF-8 CJK 문자열의 경우 3byte 문자는
	 * 길이를 * 2byte로 계산하여 반환한다. (2byte utf-8은 지원하지 않는다.)
	 *
	 * UTF-8 문자열 처리의 경우, CJK(Chinese, Japanese, Korean) 모두 처리
	 * 가능 하며 non UTF-8의 경우 EUC-KR과 EUC-JP에 사용 가능하다.
	 *
	 * 이 외의 동작은 PHP core의 {@link php.net/manual/en/function.substr.php substr}
	 * 함수와 동일하게 동작한다.
	 *
	 * @access public
	 * @return string|false
	 * @param  string  원본 문자열
	 * @param  integer 시작 지점. 0부터 시작한다.
	 * @param  integer 반환할 문자열 길이
	 */
	function substr ($str, $start, $len) {
		if ( $len === 0 ) return false;

		if ( ! self::is_utf8 ($str) ) {
			$slen = strlen ($str);
			if ( $start < 0 )
				if ( ($start = $slen + $start) < 0 )
					return false;

			if ( $start > 0 ) {
				if ( ord ($str[$start]) > 128 )
					if ( ! self::is_ksc5601 (substr ($str, $start, 2)) )
						$start--;
			}

			if ( ($str = substr ($str, $start, $len)) === false )
				return false;


			return preg_replace ('/(([\x80-\xFE].)*)[\x80-\xFE]?$/', '\\1', $str);
		}

		//
		// Hangul Jamo                           0x1100 - 0x11ff
		// Hangul Compatibility Jamo             0x3130 - 0x318f
		// Hangul Syllables (한글)               0xac00 - 0xd7af
		//
		// Hiragana                              0x30a0 - 0x30ff
		// Katakana                              0x3100 - 0x312f
		// Katakana Phonetic Extensions          0x31f0 - 0x31ff
		// 
		// CJK Radicals Supplement               0x2e80 - 0x2eff
		// CJK Symbols and Punctuation           0x3000 - 0x303f
		// * Enclosed CJK Letters and Months     0x3200 - 0x32ff
		// * CJK Compatibility                   0x3300 - 0x33ff
		// CJK Unified Ideographs Extension A    0x3400 - 0x4dbf
		// * CJK Unified Ideographs (한자)       0x4e00 - 0x9fff
		// CJK Compatibility Ideographs          0xf900 - 0xfaff
		// CJK Compatibility Forms               0xfe30 - 0xfe4f
		// CJK Unified Ideographs Extension B    0x20000 - 0x2a6df
		//
		$pattern = '\x{1100}-\x{11ff}}\x{3130}-\x{318f}\x{ac00}-\x{d7af}'; // Hnagul
		$pattern .= '\x{30a0}-\x{30f0}\x{3100}-\x{312f}\x{31f0}-\x{31ff}'; // Japanese
		$pattern .= '\x{3200}-\x{32ff}\x{3300}-\x{33ff}\x{3400}-\x{4dbf}'; // Hanja
		$pattern .= '\x{4e00}-\x{9fff}\x{f900}-\x{faff}\x{20000}-\x{2a6df}'; // Hanja
		preg_match_all ("/[{$pattern}]|./u", $str, $matches_all);
		$matches = $matches_all[0];

		// 3byte 문자를 2byte로 계산해서 문자열 길이를 구함
		for ( $i=0; $i<count ($matches); $i++ )
			$slen += (strlen ($matches[$i]) > 1) ? 2 : 1;

		// $start가 음수일 경우 양수로 변환
		if ( $start < 0 )
			if ( ($start = $slen + $start) < 0 )
				return false;

		if ( $start >= $slen )
			return false;

		// 반환할 길이가 문자열 길이보다 길면 문자열 길이로 조정
		if ( $len > $slen )
			$len = $slen;

		// len이 음수일 경우 양수로 변환
		if ( $len < 0 )
			if ( ($len = $slen + $len) < 0 )
				return false;
		
		if ( $start > 0 ) {
			if ( $start + $len > $slen )
				$len = $slen - $start;

			$no = count ($matches);
			for ( $i=0; $i<$no; $i++ ) {
				$buf = array_shift ($matches);
				$blen = strlen ($buf);
				$count += ($blen > 1) ? 2 : 1;
				if ( $count > $start ) {
					array_unshift ($matches, $buf);
					break;
				}
				$slen -= ($blen > 1) ? 2 : 1;
			}
		} else {
			for ( $i=0; $i<count ($matches); $i++ )
				$slen += (strlen ($matches[$i]) > 1) ? 2 : 1;
				
			if ( $slen <= $len )
				return $str;
		}

		$count = 0;
		foreach ( $matches as $v ) {
			$count += (strlen ($v) > 2) ? 2 : 1;
			if ( $count > $len )
				break;
			$r .= $v;
		}

		return $r;
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
