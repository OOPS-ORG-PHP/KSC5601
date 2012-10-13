<?php
/**
 *
 * 문자셋 변환을 위한 high level API
 *
 * PHP 로 문자셋을 변경하기 위하여 binary 와 numeric 을 편한하게
 * 넘나들 수 있도록 제공하는 high level API
 *
 * @category   Charset
 * @package    KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: Stream.php,v 1.4 2009-03-16 16:48:53 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**
 *
 * 문자셋 변환을 위한 high level API
 * PHP 로 문자셋을 변경하기 위하여 binary 와 numeric 을 편한하게
 * 넘나들 수 있도록 제공하는 high level API
 *
 * @category   Charset
 * @package    KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    Release:
 */
class KSC5601_Stream
{
	/**
	 * binary character 를 hex 값으로 반환
	 *
	 * @access public
	 * @param string $c 1byte 문자
	 * @param string $prefix [optional: default true]
	 *     true 일 경우 반환 값 앞에 0x 를 붙여 준다.
	 * @param boolena dec [optional: default false]
	 *     true 로 설정 되면, 10진수 값으로 반환한다.
	 * @return string 16진수 문자열 또는 10진수 문자열
	 */
	function chr2hex ($c, $prefix = true, $dec = false) {
		$prefix = $prefix ? '0x' : '';
		if ( $dec === true )
			$r = ord ($c);
		else {
			$r = strtoupper (dechex (ord ($c)));
			/* big endian */
			if ( strlen ($r) < 2 )
				$r = '0' . $r;
		}
		return $prefix . $r;
	}

	/**
	 * Hexical 문자를 1byte binary 로 변환
	 *
	 * @access public
	 * @param string $c 1byte 문자
	 * @return binary 1byte binary 문자
	 */
	function hex2chr ($c) {
		return chr (hexdec ($c));
	}

	/**
	 * binary 문자 1byte를 십진수로 변환
	 *
	 * @access public
	 * @param string $c 1byte binary 문자
	 * @return 10진수 문자열
	 */
	function chr2dec ($c) {
		return ord ($c);
	}

	/**
	 * binary 문자 1byte를 이진수로 변환
	 *
	 * @access public
	 * @param string $c 1byte binary 문자
	 * @param string $shift [optional - default none] >> [N] 또는 << [N] 문자열로 shift 를 지원
	 * @return 2진수 문자열
	 */
	function chr2bin ($c, $shift = '') {
		if ( preg_match ('/^(U\+|0x)/', $c) )
			$c = KSC5601_Stream::hex2chr ($c);

		$c = ord ($c);

		if ( $shift && preg_match ('/^([<>]+)[\s]*([0-9]+)/', $shift, $match) ) :
			switch ($match[1]) :
				case '>>' : $c = $c >> $match[2]; break;
				case '<<' : $c = $c << $match[2]; break;
				case '<'  : $c = $c <  $match[2]; break;
				case '>'  : $c = $c >  $match[2]; break;
			endswitch;
		endif;

		$c = decbin ($c);
		$l = strlen ($c);

		if ( $l < 8 ) :
			$n = 8 - $l;
			for ( $i=0; $i<$n; $i++ ) :
				$prefix .= '0';
			endfor;
			$c = $prefix . $c;
		endif;

		return $c;
    }

	/**
	 * 2진수 문자열을 1byte binary 문자로 변환
	 *
	 * @access public
	 * @param string $c 2진수 문자열
	 * @return 1byte binary 문자
	 */
	function bin2chr ($c) {
		return chr (bindec ($c));
	}

	/**
	 * byte 에 따른 UTF8의 2 번째 byte 표본을 반환
	 *
	 * @access public
	 * @param string $byte 체크 할 byte
	 * @return 2진 문자열
	 */
	function check2byte ($byte) {
		return decbin (0x80 >> (8 - $byte));
	}

	/**
	 * 10진수 문자열을 4자리 2진수 문자열로 변환
	 *
	 * @access public
	 * @param string $s 변환 할 10진수 문자열
	 * @param numeric $bit [optional - default:4] 변환 활 자리 수
	 * @return {$bit} 자리 이진 문자열
	 */
	function decbin ($s, $bit = 4) {
		$r = decbin ($s);
		$l = strlen ($r);

		if ( $l < $bit )
			$r = sprintf ("%0{$bit}s", $r);

		return $r;
	}

	/**
	 * 주어진 2 byte 가 KSX1001 범위 인지 아닌지를 체크
	 *
	 * @access public
	 * @param string $c1 첫번째 바이트 binary 문자
	 * @param string $c2 두번째 바이트 binary 문자
	 * @param boolean $is_dec [optional - default:false] 주어진 문자가
	 *     10진수로 지정할 경우 true 로 설정
	 * @return boolean KSX1001 범위 밖일 경우, true. UHC 범위가 아니거나
	 *     KSX1001 범위 안에 있을 경우 false
	 */
	function is_out_of_ksx1001 ($c1, $c2, $is_dec = false) {
		if ( ! $c1 || ! $c2 )
			return false;

		if ( $is_dec === false ) {
			$c1 = ord ($c1);
			$c2 = ord ($c2);
		}

		if ( (($c1 > 0x80 && $c1 < 0xa1) && ($c2 > 0x40 && $c2 < 0x5b )) ||
			 (($c1 > 0x80 && $c1 < 0xa1) && ($c2 > 0x60 && $c2 < 0x7b )) ||
			 (($c1 > 0x80 && $c1 < 0xa1) && ($c2 > 0x80 && $c2 < 0xff )) ||
			 (($c1 > 0xa0 && $c1 < 0xc6) && ($c2 > 0x40 && $c2 < 0x5b )) ||
			 (($c1 > 0xa0 && $c1 < 0xc6) && ($c2 > 0x60 && $c2 < 0x7b )) ||
			 (($c1 > 0xa0 && $c1 < 0xc6) && ($c2 > 0x80 && $c2 < 0xa1 )) ||
			 ($c1 == 0xc6 && ($c2 > 0x40 && $k < 0x53)) ) {
			return true;
		}

		return false;
	}

	/**
	 * 두개의 측정된 시간으로 수행 속도를 출력
	 *
	 * @access public
	 * @param array $t1 microtime() of starting
	 * @param array $t2 microtime() of ending
	 * @return string
	 */
	function execute_time ($t1, $t2) {
		$start = explode (' ', $t1);
		$end   = explode (' ', $t2);

		return sprintf("%.2f", ($end[1] + $end[0]) - ($start[1] + $start[0]));
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
