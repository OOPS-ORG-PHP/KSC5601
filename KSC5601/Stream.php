<?php
/**
 * High level API for convert character set
 *
 * This api is high level api that convert between binary and numeric
 * for convert character set with PHP
 *
 * @category   Charset
 * @package    KSC5601
 * @subpackage KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id$
 * @link       http://pear.oops.org/package/KSC5601
 * @filesource
 */

/**
 * High level API for convert character set
 *
 * This api is high level api that convert between binary and numeric
 * for convert character set with PHP
 *
 * @package KSC5601
 */
class KSC5601_Stream
{
	// {{{ function chr2hex ($c, $prefix = true, $dec = false)
	/**
	 * Convert character to hex string
	 *
	 * @access public
	 * @return string  hexical strings or decimal strings
	 * @param  string  1 byte binary character
	 * @param  string  (optional) Defaults to true. Set ture, retuan with
	 *                 prefix '0x'
	 * @param  boolean (optional) Defaults to false. Set true, return with
	 *                 decimal strings.
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
	// }}}

	// {{{ function hex2chr ($c)
	/**
	 * Convert hexical string to 1 byte binary character.
	 *
	 * @access public
	 * @return string 1 byte binary character
	 * @param  string hexical string
	 */
	function hex2chr ($c) {
		return chr (hexdec ($c));
	}
	// }}}

	// {{{ function chr2dec ($c)
	/**
	 * Convert 1 byte binary character to decimal strings.
	 *
	 * @access public
	 * @return Decimal strings
	 * @param string  1 byte binary character
	 */
	function chr2dec ($c) {
		return ord ($c);
	}
	// }}}

	// {{{ function chr2bin ($c, $shift = '')
	/**
	 * Convert binary character 1 byte to binary(numeric) strings
	 *
	 * @access public
	 * @return binary strings
	 * @param string  1 byte binary character
	 * @param string  (optional) Defaults to empty. shift string with '>> [N]' or '<< [N]'
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
	// }}}

	// {{{ function bin2chr ($c)
	/**
	 * Convert binary strings to 1byte binary character
	 *
	 * @access public
	 * @return string  1byte binary character
	 * @param  string  binary strings
	 */
	function bin2chr ($c) {
		return chr (bindec ($c));
	}
	// }}}

	// {{{ function check2byte ($byte)
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
	// }}}

	// {{{ function decbin ($s, $bit = 4)
	/**
	 * Convert decimal strings to 4-digit binary(numeric) strings.
	 *
	 * @access public
	 * @return {$bit}-digit binary strings
	 * @param string  Given decimal strings
	 * @param numeric (optiona) Defaults to 4. number of digit.
	 */
	function decbin ($s, $bit = 4) {
		$r = decbin ($s);
		$l = strlen ($r);

		if ( $l < $bit )
			$r = sprintf ("%0{$bit}s", $r);

		return $r;
	}
	// }}}

	// {{{ function is_out_of_ksx1001 ($c1, $c2, $is_dec = false)
	/**
	 * Check given 2byte is whether KSX1001 or out of range.
	 *
	 * @access public
	 * @return boolean When out of range, true.
	 * @param string  1st byte binary character
	 * @param string  2st byte binary character
	 * @param boolean (optional) Defaults to false. If type of 1st and 2st arguments
	 *                is decimal, set true.
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
	// }}}

	// {{{ function execute_time ($t1, $t2)
	/**
	 * Print execute time
	 *
	 * @access public
	 * @return string
	 * @param array microtime() of starting
	 * @param array microtime() of ending
	 */
	function execute_time ($t1, $t2) {
		$start = explode (' ', $t1);
		$end   = explode (' ', $t2);

		return sprintf("%.2f", ($end[1] + $end[0]) - ($start[1] + $start[0]));
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
