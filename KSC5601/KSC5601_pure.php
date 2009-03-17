<?php
/**
 *
 * KSC5601 를 pure php code 로 처리하기 위한 KSC5601 class
 *
 * @category    Charset
 * @package     KSC5601_pure
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   2009 (c) JoungKyun.Kim
 * @license     BSD License
 * @version     $Id: KSC5601_pure.php,v 1.3 2009-03-17 09:33:24 oops Exp $
 * @link        ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**#@+
 * 내장 extension mode 지원 여부
 */
/*
 * 내장 iconv / mbstring 사용 모드 설정
 */
define ('EXTMODE',    false);
/**#@-*/

require_once 'KSC5601/UTF8.php';

/**
 * KSC5601 과 UTF-8 간의 문자셋 변환 및 관리를 위한 기능 제공
 *
 * @category    Charset
 * @package     KSC5601_pure
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   2009 (c) JoungKyun.Kim
 * @license     BSD License
 * @version     Release:
 */
Class KSC5601
{
	private $obj;
	private $out_ksx1001 = false;

	function __construct () {
		$this->obj = new KSC5601_UTF8;

		if ( $GLOBALS['table_ksc5601'] )
			$obj->ksc = $GLOBALS['table_ksc5601'];
		if ( $GLOBALS['table_ksc5601_hanja'] )
			$obj->hanja = $GLOBALS['table_ksc5601_hanja'];
		if ( $GLOBALS['table_ksc5601_rev'] )
			$obj->revs = $GLOBALS['table_ksc5601_rev'];
	}

    /**
	 * KSX1001 범위 외의 한글을 처리
	 * @access  public
	 * @param   boolean $flag
	 *  <ol>
	 *      <li>true : UTF8 decode 시에 KSX1001 범위외의 한글을 NCR 처리 한다.</li>
	 *      <li>true : NCR encode 시에 KSX1001 범위의 한글만 NCR 처리 한다.</li>
	 *      <li>false : 아무일도 하지 않는다.</li>
	 *  </ol>
	 * @return  void
	 */
	function out_of_ksx1001 ($flag = false) {
		$this->obj->out_ksx1001 = $flag;
	}

    /**
	 * 주어진 문자열이 utf8 인지 체크
	 * @param   string  $string     검사할 문자열
	 * @return  boolean utf8 일 경우 true 를 반환
	 * @access  public
	 */
	function is_utf8 ($string) {
		return $this->obj->is_utf8 ($string);
	}

    /**
	 * UHC <-> UTF8 문자셋 변환
	 * @access  public
	 * @param   string  $string 변환할 문자열
	 *      지정이 되지 않으면 기본으로 UTF8 로 문자셋을 변환함.
	 *      UTF8 이 아닐 경우 UHC(CP949) 로 변환 함. KSC5601::out_of_ksx1001
	 *      이 true 이 경우, 디코딩 시에 KSX1001 범위 밖의 한글을 NCR 처리
	 *      함. @see KSC5601::out_of_ksx1001
	 * @param   string  $to     인코딩(UTF8)/디코딩(UHC) [기본: UTF8]
	 * @return  string
	 */
	function utf8 ($string, $to = UTF8) {
		if ( $to === UTF8 )
			return $this->obj->utf8enc ($string);

		return $this->obj->utf8dec ($string);
	}

    /**
	 * UHC <-> UCS2 문자셋 변환
	 * @access  public
	 * @param   string  $string 변환할 문자열
	 *      지정이 되지 않으면 기본으로 UCS2 hexical 로 문자셋을 변환함.
	 *      UCS2 가 아닐 경우 UCS2 hexical 을 UHC(CP949) 로 변환 함.
	 * @param   string  $to     인코딩(UCS2)/디코딩(UHC) [기본: UCS2]
	 * @param   boolean $asc    true 경우, 모든 문자를 UCS2 hexical 로 변환
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
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) & 0x80 ) {
				$r .= 'U+' . strtoupper (dechex ($this->obj->ksc2ucs ($string[$i], $string[$i+1])));
				$i++;
			} else {
				# $asc == true, don't convert ascii code to NCR code
				$r .= ( $asc === false ) ? $string[$i] : 'U+' . $this->obj->chr2hex ($string[$i], false);
			}
		}

		return $r;
	}

	private function ucs2dec ($string) {
		$s = preg_replace ('/0x([a-z0-9]{2,4})/i', 'U+\\1', trim ($string));

		$l = strlen ($s);

		for ( $i=0; $i<$l; $i++ ) {
			if ( $s[$i] == 'U' && $s[$i + 1] == '+' ) {
				$i += 2;
				$c = '';
				while ( $s[$i] != 'U' && $i < $l ) {
					$c .= $s[$i++];

					if ( strlen ($c) == 4 )
						break;
				}
				$i--;

				if ( strlen ($c) == 4 )
					$r .= $this->obj->ucs2ksc ($c);
				else
					$r .= chr (hexdec ($c));
			} else
				$r .= $s[$i];
		}

		return $r;
	}

    /**
	 * UHC <-> NCR (Numeric Code Reference) 문자셋 변환
	 * @access  public
	 * @param   string  $string 변환할 문자열
	 *      지정이 되지 않으면 기본으로 NCR code 로 문자셋을 변환함.
	 *      NCR 이 아닐 경우 NCR code 를 UHC(CP949) 로 변환 함.
	 * @param   string  $to     인코딩(NCR)/디코딩(UHC) [기본: NCR]
	 * @param   boolean $enc    true 경우, 모든 문자를 NCR code 로 변환
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
		$l = strlen ($string);

		if ( $enc === true ) {
			for ( $i=0; $i<$l; $i++ ) {
				if ( ord ($string[$i]) & 0x80 ) {
					$hex = dechex ($this->obj->ksc2ucs ($string[$i], $string[$i+1]));
					$hex = 'x' . strtoupper ($hex);
					$r .= '&#' . $hex . ';';
					$i++;
				} else {
					# $enc == true, don't convert ascii code to NCR code
					$hex = 'x' . $this->obj->chr2hex ($string[$i], false);
					$r .= '&#' . $hex . ';';
				}
			}

			return $r;
		}

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) & 0x80 ) {
				$i++;
				if ( $this->obj->out_ksx1001 === true ) {
				 	if ( $this->obj->is_out_of_ksx1001 ($string[$i-1], $string[$i]) ) {
						$hex = dechex ($this->obj->ksc2ucs ($string[$i-1], $string[$i]));
						$hex = 'x' . strtoupper ($hex);
						$r .= '&#' . $hex . ';';
					} else
						$r .= $string[$i-1] . $string[$i];
				} else {
					$hex = dechex ($this->obj->ksc2ucs ($string[$i-1], $string[$i]));
					$hex = 'x' . strtoupper ($hex);
					$r .= '&#' . $hex . ';';
				}
			} else
				$r .= $string[$i];
		}

		return $r;
	}

	private function ncr2dec ($str) {
		$l = strlen ($str);

		for ( $i=0; $i<$l; $i++ ) {
			if ( $str[$i] == '&' && $str[$i + 1] == '#' ) {
				if ( $str[$i + 3] == ';' ) {
					$c = $str[$i + 2];
					$i += 3;
				} else if ( $str[$i + 4] == ';' ) {
					$c = $str[$i + 2] . $str[$i + 3];
					$i += 4;
				} else if ( $str[$i + 5] == ';' ) {
					$c = $str[$i + 2] . $str[$i + 3] . $str[$i + 4];
					$i += 5;
				} else if ( $str[$i + 6] == ';' ) {
					$c = $str[$i + 2] . $str[$i + 3] . $str[$i + 4] . $str[$i + 5];
					$i += 6;
				} else if ( $str[$i + 7] == ';' ) {
					$c = $str[$i + 2] . $str[$i + 3] . $str[$i + 4] . $str[$i + 5] . $str[$i + 6];
					$i += 7;
				} else {
					$r .= $str[$i];
					continue;
				}

				if ( $c[0] == 'x' )
					$c = substr ($c, 1);
				else
					$c = dechex ($c);

				if ( strlen ($c) == 4 ) {
					$org_ksx1001 = $this->obj->out_ksx1001;
					$this->obj->out_ksx1001 = false;

					$r .= $this->obj->ucs2ksc ($c);

					$this->obj->out_ksx1001 = $org_ksx1001;
				} else
					$r .= chr (hexdec ($c));
			} else
				$r .= $str[$i];
		}

		return $r;
	}

	/**
	 * KSC5601 reverse table 생성을 위한 method
	 *
	 * @access public
	 * @return string
	 */
	function make_reverse_table () {
		$this->obj->mk_revTable ();
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
