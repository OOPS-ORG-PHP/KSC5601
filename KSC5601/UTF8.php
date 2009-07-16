<?php
/**
 * KSC5601 UTF8 internal API for pure code
 *
 * @category   Charset
 * @package    KSC5601
 * @subpackage KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: UTF8.php,v 1.13 2009-07-16 19:48:29 oops Exp $
 * @link       http://pear.oops.org/package/KSC5601
 */

/**
 * import High level API for convert character set
 */
require_once 'KSC5601/Stream.php';

/**
 * Only PHP don't support iconv/mbstring, UCS2.php is needed 
 */
if ( EXTMODE === false ) {
	/**
     * import API class that controls UCS2
	 */
	require_once 'KSC5601/UCS2.php';
} else {
	/**
	 * If needless USC2.php, define dummy class for compotable
	 * @ignore
	 */
	Class KSC5601_UCS2 {}
}

/**
 * UTF8 controle class api
 *
 * @package KSC5601
 */
class KSC5601_UTF8 extends KSC5601_UCS2
{
	// {{{ properties
	/**
	 * Whether print debug message
	 *
	 * @access private
	 */
	private $debug = false;
	// }}}

	// {{{ function rm_utf8bom ($s)
	/**
	 * remove utf8 bom code (first 3byte)
	 *
	 * @access public
	 * @return string
	 * @param  string Given strings
	 */
	function rm_utf8bom ($s) {
		if ( ord ($s[0]) == 0xef && ord ($s[1]) == 0xbb && ord ($s[2]) == 0xbf )
			return substr ($s, 3);

		return $s;
	}
	// }}}

	// {{{ function is_utf8 ($s)
	/**
	 * whether utf8 or not given strings
	 *
	 * @access public
	 * @return boolean If given strings ars utf-8, return true
	 * @param  string  Given strings
	 */
	function is_utf8 ($s) {
		if ( ord ($s[0]) == 0xef && ord ($s[1]) == 0xbb && ord ($s[2]) == 0xbf )
			return true;

		$l = strlen ($s);

		for ( $i=0; $i<$l; $i++ ) {
			# if single byte charactors, skipped
			if ( ! (ord ($s[$i]) & 0x80) )
				continue;

			$first = KSC5601_Stream::chr2bin ($s[$i]);

			# first byte of utf8 is must start 11
			if ( substr ($first, 0, 2) == '10' )
				return false;

			# except 1st byte
			$byte = strlen (preg_replace ('/^([1]+).*/', '\\1', $first));

			if ( $byte > 6 )
				continue;

			/*
			 * 2 byte UTF-8 check is skip, because some hangle is over wrapping 2byte utf-8
			 * For example, hangul 'Á¤' have 11000001 10100100
			 */
			if ( $byte < 3 )
				continue;

			/*
			 * 2byte: 1100000x (10xxxxxx)
			 * 3byte: 11100000 10xxxxxx (10xxxxxx)
			 * 4byte: 11110000 10xxxxxx (10xxxxxx 10xxxxxx)
			 * 5byte: 11111000 10xxxxxx (10xxxxxx 10xxxxxx 10xxxxxx)
			 * 6byte: 11111100 10xxx0xx (10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx)
			 */
			for ( $j=1; $j<$byte; $j++ ) {
				/*
				if ( $j == 1 ) {
					$n = 8 - $byte;
					if ( KSC5601_Stream::chr2bin ($s[$i+1], ">>$n") != KSC5601_Stream::check2byte ($byte) )
						return false;

					continue;
				}
				 */

				if ( KSC5601_Stream::chr2bin ($s[$i+$j], '>>6') != 10 )
					return false;
			}

			break;
		}

		return true;
	}
	// }}}

	// {{{ function utf8enc ($s)
	/**
	 * convert UCH to UTF-8
	 *
	 * @access public
	 * @return string UTF-8 strings
	 * @param  string Given UHC strings
	 */
	function utf8enc ($s) {
		$len = strlen ($s);

		for ( $i=0; $i<$len; $i++ ) {
			if ( ord ($s[$i]) & 0x80 ) {
				$c1 = $s[$i];
				$c2 = $s[$i+1];
				$ucs2 = $this->ksc2ucs ($c1, $c2);

				if ( $ucs2 == '?' ) {
					$r .= $ucs2;
					$i++;
					continue;
				}

				$uni[0] = $this->decbin ($ucs2 >> 12);
				$uni[1] = $this->decbin ($ucs2 >> 8 & 0x0f);
				$uni[2] = $this->decbin ($ucs2 >> 4 & 0x00f);
				$uni[3] = $this->decbin ($ucs2 & 0x000f);

				$uc1 = bindec ('1110' . $uni[0]);
				$uc2 = bindec ('10' . $uni[1] . substr ($uni[2], 0, 2));
				$uc3 = bindec ('10' . substr ($uni[2], 2, 2) . $uni[3]);

				$r .= chr ($uc1) . chr ($uc2) . chr ($uc3);
				$i++;
			} else
				$r .= utf8_encode ($s[$i]);
		}

		return $r;
	}
	// }}}

	// {{{ function utf8dec ($s)
	/**
	 * Convert UTF-8 to UHC
	 *
	 * @access public
	 * @return string UHC strings
	 * @param  string Given UTF-8 strings
	 */
	function utf8dec ($s) {
		$s = $this->rm_utf8bom ($s);
		$l = strlen ($s);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($s[$i]) & 0x80 ) {
				$uni1 = ord ($s[$i]);
				$uni2 = ord ($s[$i + 1]);
				$uni3 = ord ($s[$i + 2]);

				# 0x03 -> 00000011
				# 0x30 -> 00110000
				$ucs2 = dechex ($uni1 & 0x0f) .
						dechex ($uni2 >> 2 & 0x0f) .
						dechex ((($uni2 & 0x03) <<2) | (($uni3 & 0x30) >> 4)) .
						dechex ($uni3 & 0x0f);

				if ( $this->debug ) {
					#     ucs0     ucs1  ucs2       ucs3
					#1111(1111).11(1111)(11).11(11)(1111)
					echo 'HEX STR => ' . $ucs2 . "\n";
					echo '0 => ' . $ucs2[0] . ' ' . decbin (hexdec ($ucs2[0])) . "\n";
					echo '1 => ' . $ucs2[1] . ' ' . decbin (hexdec ($ucs2[1])) . "\n";
					echo '2 => ' . $ucs2[2] . ' ' . decbin (hexdec ($ucs2[2])) . "\n";
					echo '3 => ' . $ucs2[3] . ' ' . decbin (hexdec ($ucs2[3])) . "\n";
				}

				$r .= $this->ucs2ksc ($ucs2);

				$i += 2;
			} else
				$r .= utf8_decode ($s[$i]);
		}

		return $r;
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
