<?php
/**
 *
 * KSC5601 을 php iconv / mbstring 확장을 이용하여 처리하기 위한
 * KSC5601 Class
 *
 * @category   Charset
 * @package    KSC5601_ext
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: KSC5601_ext.php,v 1.3 2009-03-17 09:33:24 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**#@+
 * 내장 extension mode 지원 여부
 */
/*
 * 내장 iconv / mbstring 사용 모드 설정
 */
define ('EXTMODE',    true);
/**#@-*/


/**
 * UTF8 을 체크하기 위한 KCS5601::is_utf8 method 원형 API include
 */
require_once 'KSC5601/UTF8.php';

/**
 * KSC5601 과 UTF-8 간의 문자셋 변환 및 관리를 위한 기능 제공
 *
 * @category	Charset
 * @package		KSC5601_ext
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	2009 (c) JoungKyun.Kim
 * @license		BSD License
 * @version		Release:
 */
Class KSC5601
{
	private $obj;
	private $out_ksx1001 = false;

	function __construct () {
		$this->obj  = $GLOBALS['chk'];
	}

	/**
	 * KSX1001 범위 외의 한글을 처리
	 * @access	public
	 * @param	boolean	$flag
	 * 	<ol>
	 * 		<li>true : UTF8 decode 시에 KSX1001 범위외의 한글을 NCR 처리 한다.</li>
	 * 		<li>true : NCR encode 시에 KSX1001 범위의 한글만 NCR 처리 한다.</li>
	 * 		<li>false : 아무일도 하지 않는다.</li>
	 * 	</ol>
	 * @return	void
	 */
	function out_of_ksx1001 ($flag = false) {
		$this->out_ksx1001 = $flag;
	}

	/**
	 * 주어진 문자열이 utf8 인지 체크
	 * @param   string  $string     검사할 문자열
	 * @return  boolean utf8 일 경우 true 를 반환
	 * @access  public
	 */
	function is_utf8 ($string) {
		return KSC5601_UTF8::is_utf8 ($string);
	}

	/**
	 * UHC <-> UTF8 문자셋 변환
	 * @access	public
	 * @param	string	$string	변환할 문자열
	 * 		지정이 되지 않으면 기본으로 UTF8 로 문자셋을 변환함.
	 * 		UTF8 이 아닐 경우 UHC(CP949) 로 변환 함. KSC5601::out_of_ksx1001
	 * 		이 true 이 경우, 디코딩 시에 KSX1001 범위 밖의 한글을 NCR 처리
	 * 		함. @see KSC5601::out_of_ksx1001
	 * @param	string	$to     인코딩(UTF8)/디코딩(UHC) [기본: UTF8]
	 * @return  string
	 */
	function utf8 ($string, $to = UTF8) {
		if ( $to === UTF8 )
			$string = $this->ncr ($string, UHC);

		if ( preg_match ('/^utf[-]?8$/i', $to) ) {
			$to = UTF8;
			$from = UHC;
		} else {
			$to = UHC;
			$from = UTF8;
		}

		$r = $this->obj->extfunc ($from, $to, $string);

		if ( $to == UHC && $this->out_ksx1001 === true )
			$r = $this->ncr ($r);

		return $r;
	}

	/**
	 * UHC <-> UCS2 문자셋 변환
	 * @access	public
	 * @param	string	$string	변환할 문자열
	 * 		지정이 되지 않으면 기본으로 UCS2 hexical 로 문자셋을 변환함.
	 * 		UCS2 가 아닐 경우 UCS2 hexical 을 UHC(CP949) 로 변환 함.
	 * @param	string	$to     인코딩(UCS2)/디코딩(UHC) [기본: UCS2]
	 * @param	boolean $asc    true 경우, 모든 문자를 UCS2 hexical 로 변환
	 *                          false 의 경우 KSX1001 범위 외의 한글만 UCS hexical 로 변환
	 *                          디코드 시에는 사용하지 않음
	 *                          기본값 false
	 * @return  string
	 */
	function ucs2 ($string, $to = UCS2, $asc = false) {
		if ( preg_match ('/ucs[-]?2(be|le)?/i', $to) ) {
			/* to ucs2 */
			return $this->ucs2enc ($string, $asc);
		} else {
			/* to UHC */
			return $this->ucs2dec ($string);
		}
	}

	private function ucs2enc ($string, $asc = false) {
		$string = $this->obj->extfunc (UHC, UCS2, $string);
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) == 0 ) {
				/* ascii area */
				$r .= ( $asc === false ) ?
					$string[$i+1] :
					'U+' . KSC5601_Stream::chr2hex ($string[$i+1], false);
			} else {
				$r .= 'U+' .
					KSC5601_Stream::chr2hex ($string[$i], false) .
					KSC5601_Stream::chr2hex ($string[$i+1], false);
			}
			$i++;
		}

		return $r;
	}

	private function ucs2dec ($string) {
		$s = preg_replace ('/0x([a-z0-9]{2,4})/i', 'U+\\1', trim ($string));
		$r = preg_replace_callback ('/U\+([[:alnum:]]{2})([[:alnum:]]{2})?/',
				create_function ('$matches', "
					if ( \$matches[2] )
						\$r = chr (hexdec (\$matches[1])) . chr (hexdec (\$matches[2]));
					else
						\$r = chr (0) . chr (hexdec (\$matches[1]));

					if ( extension_loaded ('iconv') )
						return iconv (UCS2, UHC, \$r);
					else if ( extension_loaded ('mbstring') )
						return mb_convert_encoding (\$r, UHC, UCS2);
					return false;
				"),
				$s
			);

		return $r;
	}

	/**
	 * UHC <-> NCR (Numeric Code Reference) 문자셋 변환
	 * @access	public
	 * @param	string	$string	변환할 문자열
	 * 	<p>
	 * 		지정이 되지 않으면 기본으로 NCR code 로 문자셋을 변환함.
	 * 		NCR 이 아닐 경우 NCR code 를 UHC(CP949) 로 변환 함.
	 * 	</p>
	 * @param	string	$to     인코딩(NCR)/디코딩(UHC) [기본: NCR]
	 * @param	boolean $enc    true 경우, 모든 문자를 NCR code 로 변환
	 *                          false 의 경우 KSC5601::out_of_ksx1001 의 설정이 true 일 경우
	 *                          KSX1001 범위 밖의 한글만 NCR 로 변환하며, false 의 경우 UHC
	 *                          모든 영역을 NCR로 변환. 디코드 시에는 사용하지 않음
	 *                          기본값 false
	 * @return  string
	 */
	function ncr ($string, $to = NCR, $enc = false) {
		if ( $to == NCR ) {
			/* to ucs2 */
			return $this->ncr2enc ($string, $enc);
		} else {
			/* to UHC */
			return $this->ncr2dec ($string);
		}
	}

	private function ncr2enc ($string, $enc = false) {
		if ( $enc === false ) {
			$l = strlen ($string);

			for ( $i=0; $i<$l; $i++ ) {
				$c1 = ord ($string[$i]);
				if ( ! ($c1 & 0x80) ) {
					$r .= $string[$i];
					continue;
				}
				$i++;

				if ( $this->out_ksx1001 === true ) {
					if ( KSC5601_Stream::is_out_of_ksx1001 ($string[$i-1], $string[$i]) ) {
						$u = $this->obj->extfunc (UHC, UCS2, $string[$i-1] . $string[$i]);
						$r .= '&#x' .
							KSC5601_Stream::chr2hex ($u[0], false) .
							KSC5601_Stream::chr2hex ($u[1], false) . ';';
					} else
						$r .= $string[$i-1] . $string[$i];
				} else {
					$u = $this->obj->extfunc (UHC, UCS2, $string[$i-1] . $string[$i]);
					$r .= '&#x' .
						KSC5601_Stream::chr2hex ($u[0], false) .
						KSC5601_Stream::chr2hex ($u[1], false) . ';';
				}
			}
			return $r;
		}

		$string = $this->obj->extfunc (UHC, UCS2, $string);
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) == 0 ) {
				 $r .= '&#x' . KSC5601_Stream::chr2hex ($string[$i+1], false) . ';';
			} else {
				$r .= '&#x' .
					KSC5601_Stream::chr2hex ($string[$i], false) .
					KSC5601_Stream::chr2hex ($string[$i+1], false) . ';';
			}
			$i++;
		}

		return $r;
	}

	private function ncr2dec ($string) {
		$r = preg_replace_callback (
				'/&#([[:alnum:]]+);/',
				create_function ('$m', "
					\$m[1] = ( \$m[1][0] == 'x' ) ?  substr (\$m[1], 1) : dechex (\$m[1]);

					if ( strlen (\$m[1]) % 2 )
						\$m[1] = '0' . \$m[1];

					preg_match ('/^([[:alnum:]]{2})([[:alnum:]]{2})?$/', \$m[1], \$matches);

					\$n = chr (hexdec (\$matches[1]));
					if ( \$matches[2] ) {
						\$n .= chr (hexdec (\$matches[2]));

						if ( extension_loaded ('iconv') )
							return iconv ('ucs-2be', 'uhc', \$n);
						else if ( extension_loaded ('mbstring') )
							return mb_convert_encoding (\$n, 'uhc', 'ucs-2be');

						return false;
					}

					/* little endian */
					\$n .= chr (0);

					if ( extension_loaded ('iconv') )
						return iconv ('ucs-2', 'uhc', \$n);
					else if ( extension_loaded ('mbstring') )
						return mb_convert_encoding (\$n, 'uhc', 'ucs-2');
					return false;
				"),
				$string
			);

		return $r;
	}
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
