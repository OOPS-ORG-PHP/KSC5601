<?php
/**
 * Project: KSC5601 :: convert character set between KSC5601 and UTF8
 * File:    KSC5601/KSC5601_pure.php
 * 
 * Sub pcakge of KSC5601 package. This package is used when php don't support
 * iconv or mbstring extensions.
 *
 * @category   Charset
 * @package    KSC5601
 * @subpackage KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    BSD License
 * @version    $Id$
 * @link       http://pear.oops.org/package/KSC5601
 * @filesource
 */

// {{{ constant
/**
 * Define EXTMODE to false. This means that php don't support iconv or mbstring
 * extensions.
 */
define ('EXTMODE',    false);
// }}}

/**
 * import UTF-8 API that has pure php code for UTF-8
 */
require_once 'KSC5601/UTF8.php';


/**
 * Original API of KSC5601 that used pure php code
 *
 * @package KSC5601
 */
Class KSC5601_pure
{
	// {{{ properties
	/**#@+
	 * @access private
	 */
	/**
	 * KSC5601_UTF8 object
	 * @var object
	 */
	private $obj;
	/**
	 * Status whether process hangul that is out of ksx1001 range.
	 * Set false, no action for hangul that is out of ksx1001 range.
	 * @var boolean
	 */
	private $out_ksx1001 = false;
	/**#@-*/
	// }}}

	// {{{ construct
	/**
	 * @access public
	 * @return vold
	 */
	function __construct () {
		$this->obj = new KSC5601_UTF8;

		if ( $GLOBALS['table_ksc5601'] )
			$obj->ksc = $GLOBALS['table_ksc5601'];
		if ( $GLOBALS['table_ksc5601_hanja'] )
			$obj->hanja = $GLOBALS['table_ksc5601_hanja'];
		if ( $GLOBALS['table_ksc5601_rev'] )
			$obj->revs = $GLOBALS['table_ksc5601_rev'];
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
		$this->obj->out_ksx1001 = $flag;
		return $this->obj->out_ksx1001;
	}
	// }}}

	// {{{ function is_utf8 ($string, $ascii)
	/**
	 * Check given string wheter utf8 of not.
	 *
	 * @access  public
	 * @return  boolean Given string is utf8, return true.
	 * @param   string  Given strings
	 * @param   boolean Check whether is ascii only or not
	 */
	function is_utf8 ($string, $ascii = false) {
		return $this->obj->is_utf8 ($string, $ascii);
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
			return $this->obj->utf8enc ($string);

		return $this->obj->utf8dec ($string);
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
	// }}}

	// {{{ private function ncr2dec ($string)
	/**
	 * Convert NCR code to UHC
	 *
	 * @access private
	 * @return string
	 * @param  string Given string
	 */
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
		$this->obj->mk_revTable ();
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
