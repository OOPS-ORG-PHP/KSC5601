<?php
/**
 * Project: KSC5601 :: convert character set between KSC5601 and UTF8<br>
 * File:    KSC5601.php
 *
 * KSC5601 pear 패키지는 한글과 관련된 변환 및 체크에 대한 method를 제공한다.
 *
 * UHC와 UTF8 또는 UHC와 UCS2, UHC(또는 CP949)와 NCR (Numeric character reference)
 * 코드간의 변환을 제공하며, 또한 UHC와 NCR간의 변환은 KSX1001 범위 밖의 인식되지
 * 못하는 문자를 출력 가능하게 한다.
 *
 * 그 외에 utf-8 여부 체크와 ksc5601 여부 체크가 가능하며, 다국어 처리를 위한
 * substr을 제공한다.
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
 *
 * KSC5601 pear 패키지는 한글과 관련된 변환 및 체크에 대한 method를
 * 제공한다.
 *
 * UHC와 UTF8 또는 UHC와 UCS2, UHC(또는 CP949)와 NCR (Numeric character
 * reference) 코드간의 변환을 제공하며, 또한 UHC와 NCR간의 변환은 KSX1001
 * 범위 밖의 인식되지 못하는 문자를 출력 가능하게 한다.
 *
 * 그 외에 utf-8 여부 체크와 ksc5601 여부 체크가 가능하며, 다국어 처리를
 * 위한 substr을 제공한다.
 *
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
	 * iconv 또는 mbstring 확장이 지원되면, 내부적으로 KSC5601_ext class
	 * 사용하며, 지원되지 않으면, KSC5601_pure class를 사용한다.
	 *
	 * 성능상으로는 iconv 또는 mbstring이 지원되는 것이 좋다.
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

	// {{{ (boolean) KSC5601:: out_of_ksx1001 ($flag = false)
	/**
	 * KSX1001 범위 밖의 한글을 변환할 것인지 여부를 설정한다. 이 menotd는
	 * private $out_ksx1001 변수값을 변경한다.
	 *
	 * @access  public
	 * @return  boolean Return 
	 * @param   boolean (optional) 기본값 false
	 *  <ol>
	 *      <li>true : UTF-8 디코드시, KSX1001 범위 밖의 한글 문자를 NCR로
	 *                 변환한다.
	 *      <li>true : UHC(CP949)에서 NCR로 변환시, KSX1001 범위 밖의 한글
	 *                 문자만 NCR로 변환한다.
	 *      <li>false : 아무 액션을 하지 않는다.</li>
	 *  </ol>
	 */
	function out_of_ksx1001 ($flag = false) {
		return $this->obj->out_of_ksx1001 ($flag);
	}
	// }}}

	// {{{ (boolean) KSC5601:: is_utf8 ($string, $ascii_only_check)
	/**
	 * 주어진 문자열이 utf8인지 아닌지를 검사한다.
	 *
	 * @access  public
	 * @return  boolean utf-8 문자열 또는 ascii로만 구성이 된 문자열이이면
	 *                  true를 반환한다.
	 * @param   string  검사할 문자열
	 * @param   boolean true로 설정시, 문자열이 ascii로만 구성되어 있으면
	 *                  false를 반환한다.
	 */
	function is_utf8 ($string, $ascii_only_check = false) {
		return $this->obj->is_utf8 ($string, $ascii_only_check);
	}
	// }}}

	// {{{ (boolean) KSC5601:: is_ksc5601 ($string)
	/**
	 * 주어진 2byte 문자가 ksc5601의 범위에 있는지 확인한다.
	 *
	 * 주의할 것은 문자열을 지정했을 경우 처음 2byte만 체크한다.
	 *
	 * @access  public
	 * @return  boolean ksc5601의 범위 안에 있을 경우 true 반환
	 * @param   string  2byte 문자
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

	// {{{ (boolaen) KSC5601:: is_ksx1001 ($string)
	/**
	 * 주어진 2byte 문자가 ksx1001의 범위에 있는지 확인한다.
	 *
	 * 주의할 것은 문자열을 지정했을 경우 처음 2byte만 체크한다.
	 *
	 * @access  public
	 * @return  boolean ksx1001의 범위 안에 있을 경우 true 반환
	 * @param   string  2byte 문자
	 */
	function is_ksx1001 ($string) {
		return self::is_ksc5601 ($string, true);
	}
	// }}}

	// {{{ (string|false) KSC5601:: substr ($str, $start, $len)
	/**
	 * 지정된 시작지점에서 지정될 길이만큼의 문자열을 반환한다.
	 *
	 * EUC-KR과 UTF-8을 모두 지원하며, UTF-8 CJK 문자열의 경우 3byte 문자는
	 * 길이를 2byte로 계산하여 반환한다. (2byte utf-8은 지원하지 않는다.)
	 *
	 * UTF-8 문자열 처리의 경우, CJK(Chinese, Japanese, Korean) 모두 처리
	 * 가능 하며 non UTF-8의 경우 EUC-KR과 EUC-JP에 사용 가능하다.
	 *
	 * 이 외의 동작은 PHP core의 {@link http://php.net/manual/en/function.substr.php substr()}
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

	// {{{ (string) KSC5601:: utf8 ($string, $to = UTF8)
	/**
	 * Convert between UHC and UTF-8
	 * UHC(CP949)와 UTF-8 간의 변환을 제공한다.
	 *
	 * @access  public
	 * @return  string
	 * @param   string  변환할 원본 문자열
	 * @param   string  (optional) 기본값 UTF8. 사용할 수 있는 값으로 UTF8 또는
	 *                  UHC 상수를 사용할 수 있다.
	 *
	 *                  이 인자를 설정하지 않거나 또는 UTF8 상수로 설정을 하면,
	 *                  원본 문자열을 UTF-8로 변환한다.
	 *
	 *                  UHC 상수로 설정하면, UTF-8에서 UHC로 변환한다. 내부적으로
	 *                  KSC5601::out_of_ksx1001 (true) 코드에 의하여 private
	 *                  $out_ksx1001 변수가 true로 설정이 되면, KSX1001 범위
	 *                  밖의 문자에 대해서는 NCR로 변환을 한다.
	 * @see KSC5601::out_of_ksx1001()
	 */
	function utf8 ($string, $to = UTF8) {
		return $this->obj->utf8 ($string, $to);
	}
	// }}}

	// {{{ (string) ucs2 ($string, $to = UCS2, $asc = false)
	/**
	 * UHC와 UCS2간의 변환을 제공한다.
	 *
	 * @access  public
	 * @return  string
	 * @param   string  원본 문자열
	 * @param   string  (optional) 기본값 UCS2. 사용할 수 있는 값으로 UCS2 또는
	 *                  UHC 상수를 사용할 수 있따.
	 *
	 *                  UCS2 상수로 설정을 하면, UHC를 UCS2 16진수(예를 들면
	 *                  U+B620)로 변환을 한다.
	 *
	 *                  UHC로 설정을 하면, UC2 16진수 문자를 UHC로 변환한다.
	 * @param   boolean (optional) 기본값 false. 이 파라미터는 오직 두번째
	 *                  파라미터가 UCS2일 경우에만 작동한다.
	 *
	 *                  false로 설정이 되면, KSX1001 범위 밖의 한글만 UCS2
	 *                  16진수 값으로 변환한다.
	 */
	function ucs2 ($string, $to = UCS2, $asc = false) {
		return $this->obj->ucs2 ($string, $to, $asc);
	}
	// }}}

	// {{{ (string) ncr ($string, $to = NCR, $enc = false)
	/**
	 * UHC와 NCR(Numeric Code Reference)간의 변환을 제공한다.
	 *
	 * @access  public
	 * @return  string
	 * @param   string  원본 문자열
	 * @param   string  (optional) 기본값 NCR. 사용할 수 있는 값으로 NCR 또는
	 *                  UHC 상수를 사용할 수 있다.
	 *
	 *                  NCR 상수로 설정이 되면, UHC(CP949)를 NCR 코드로
	 *                  변환한다.
	 *
	 *                  UHC 상수로 설정이 되면, NCR 코드를 UHC(CP949)로
	 *                  변환한다.
	 * @param   boolean (optional) 기본값 false. 이 파라미터는 두번째 파라미터가
	 *                  NCR일 경우에만 작동한다.
	 *
	 *                  false로 설정되면, KSC5601::out_of_ksx1001(true)가 호출이
	 *                  되어 내부적으로 private $out_ksx1001 변수의 값이 true로
	 *                  설정이 되었을 경우, KX1001 범위 밖의 한글만 NCR로
	 *                  변환한다.
	 *
	 *                  true로 설정이 되면 모든 문자를 NCR 코드로 변환한다.
	 */
	function ncr ($string, $to = NCR, $enc = false) {
		return $this->obj->ncr ($string, $to, $enc);
	}
	// }}}

	// {{{ function make_reverse_table ()
	/**
	 * KSC5601의 역변환 테이블을 PHP code로 출력한다.
	 * 이 method는 KSC5601 pure code 개발을 위해서만 필요하다.
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
