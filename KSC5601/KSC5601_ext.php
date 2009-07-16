<?php
/**
 * Project: KSC5601 :: convert character set between KSC5601 and UTF8
 * File:    KSC5601/KSC5601_ext.php
 * 
 * Sub pcakge of KSC5601 package. This package is used when php support
 * iconv or mbstring extensions.
 *
 * @category   Charset
 * @package    KSC5601
 * @subpackage KSC5601_ext
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: KSC5601_ext.php,v 1.4 2009-07-16 18:59:02 oops Exp $
 * @link       http://pear.oops.org/package/KSC5601
 */

// {{{ constant
/**
 * Define EXTMODE to true. This means that php support iconv or mbstring
 * extensions.
 */
define ('EXTMODE',    true);
// }}}

/**
 * import original API of KSC5601_ext::is_utf8 for check whether
 * UTF-8 or not.
 */
require_once 'KSC5601/UTF8.php';

/**
 * Original API of KSC5601 that used iconv or mbstring.
 */
Class KSC5601_ext
{
	// {{{ properties
	/**#@+
	 * @access private
	 */
	/**
	 * KSC5601_common object
	 */
	private $obj;
	/**
	 * Status whether process hangul that is out of ksx1001 range.
	 * Set false, no action for hangul that is out of ksx1001 range.
	 */
	private $out_ksx1001 = false;
	/**#@-*/
	// }}}

	// {{{ constructor
	/**
	 * Support iconv or mbstring extension, use KSC5601_ext internal class, or not
	 * support use KSC5601_pure internal class.
	 *
	 * @access public
	 * @return void
	 * @param  object  return value of KSC5601_Common class
	 */
	function __construct ($is_ext) {
		$this->obj  = $is_ext;
	}
	// }}}

	// {{{ function out_of_ksx1001 ($flag = false)
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
	function out_of_ksx1001 ($flag = false) {
		$this->out_ksx1001 = $flag;
		return $this->out_ksx1001;
	}
	// }}}

	// {{{ function is_utf8 ($string)
	/**
	 * Check given string wheter utf8 of not.
	 *
	 * @access  public
	 * @return  boolean Given string is utf8, return true.
	 * @param   string  Given strings
	 */
	function is_utf8 ($string) {
		return KSC5601_UTF8::is_utf8 ($string);
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
	 *                  KSC5601::out_of_ksx1001(true)), convert to NCR hangul
	 *                  that is out of KSX1001 range.
	 *                  @see KSC5601::out_of_ksx1001
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
		if ( preg_match ('/ucs[-]?2(be|le)?/i', $to) ) {
			/* to ucs2 */
			return $this->ucs2enc ($string, $asc);
		} else {
			/* to UHC */
			return $this->ucs2dec ($string);
		}
	}
	// }}}

	// {{{ private function ucs2enc ($string, $asc = false)
	/**
	 * Convert UHC to UCS2 hexical
	 *
	 * @access  private
	 * @return  string
	 * @param   string  Given String
	 * @param   boolean (optional) Defaults to false.
	 */
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
	// }}}

	// {{{ private function ucs2dec ($string)
	/**
	 * Convert UCS2 hexical to UHC
	 *
	 * @access private
	 * @return string
	 * @param  string Given String
	 */
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
	 *                  KSC5601::out_of_ksx1001 (true).
	 *
	 *                  Set true, convert all character to NCR code.
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
	// }}}

	// {{{ private function ncr2enc ($string, $enc = false)
	/**
	 * Convert NCR code to UCS2
	 *
	 * @access private
	 * @return string
	 * @param  string  Given String that is conscruct with NCR code or included NCR code.
	 * @param  boolena (optional) Defaults to false.
	 */
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
	// }}}

	// {{{ private function ncr2dec ($string)
	/**
	 * Convert NCR code to UHC
	 *
	 * @access private
	 * @return string
	 * @param  string Given string
	 */
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
