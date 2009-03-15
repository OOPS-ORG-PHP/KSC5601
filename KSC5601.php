<?php
/**
 * Copyright (c) 2008, JoungKyun.Kim <http://oops.org>
 * 
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the authors nor the names of its contributors
 *       may be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   pear
 * @package    Character Set
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2008, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: KSC5601.php,v 1.3 2009-03-15 16:56:02 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear
 * @since      File available since Release 0.1
 * $Id: KSC5601.php,v 1.3 2009-03-15 16:56:02 oops Exp $
 */

require_once 'KSC5601/UTF8.php';
define ('UTF8',   'utf8');
define ('EUC-KR', 'euc-kr');
define ('UHC',    'cp949');
define ('CP949',  'cp949');
define ('UCS2',   'ucs-2be');

/**
 * Manipulation character set between KSC5601 and UTF-8
 */
Class KSC5601
{
	private $obj;

	function __construct () {
		$this->obj = new KSC5601_UTF8;

		if ( $GLOBALS['table_ksc5601'] )
			$obj->ksc = $GLOBALS['table_ksc5601'];
		if ( $GLOBALS['table_ksc5601_hanja'] )
			$obj->hanja = $GLOBALS['table_ksc5601_hanja'];
		if ( $GLOBALS['table_ksc5601_rev'] )
			$obj->revs = $GLOBALS['table_ksc5601_rev'];
	}

	function usePure ($v = true) {
		$this->obj->iconv = ($v === true) ? false : true;
		$this->obj->mbstring = ($v === true) ? false : true;
	}

	function noKSX1001 ($flag = 'false') {
		$this->obj->ksx1001 = $flag;
	}

	/**
	 * return boolean whether utf8 or not about given string
	 *
	 * @param   string  $string     check string
	 * @return  boolean if 0, not uft8, and if 1, utf8
	 * @static
	 * @access  public
	 */
	function is_utf8 ($string) {
		return $this->obj->is_utf8 ($string);
	}

	function utf8 ($string, $to = UTF8) {
		if ( $to === UTF8 )
			return $this->obj->utf8enc ($string);

		return $this->obj->utf8dec ($string);
	}

	function ucs2 ($string, $enc = true, $asc = false) {
		if ( $enc === true ) {
		}
	}

	function toucs2 ($string, $asc = false) {
		if ( $this->obj->is_extfunc () )
			$string = $this->obj->extfunc (UHC, UCS2, $string);

		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( $this->obj->is_extfunc () ) {
				/* iconv or mbstring mode */
				if ( ord ($string[$i]) == 0 ) {
					/* ascii area */
					$r .= ( $asc === false ) ?
						$string[$i+1] :
						'U+' . $this->obj->chr2hex ($string[$i+1], false);
				} else {
					$r .= 'U+' .
						$this->obj->chr2hex ($string[$i], false) .
						$this->obj->chr2hex ($string[$i+1], false);
				}
				$i++;
			} else {
				/* pure mode */
				if ( ord ($string[$i]) & 0x80 ) {
					$r .= 'U+' . strtoupper (dechex ($this->obj->ksc2ucs ($string[$i], $string[$i+1])));
					$i++;
				} else {
					# $asc == true, don't convert ascii code to NCR code
					$r .= ( $asc === false ) ? $string[$i] : 'U+' . $this->obj->chr2hex ($string[$i], false);
				}
			}
		}

		return $r;
	}

	function todeucs2 ($string) {
		$s = preg_replace ('/0x([a-z0-9]{2,4})/i', 'U+\\1', trim ($string));

		if ( $this->obj->is_extfunc () ) {
			$r = preg_replace_callback ('/U\+([[:alnum:]]{2})([[:alnum:]]{2})?/',
					create_function ('$matches', "
						if ( \$matches[2] )
							\$r = chr (hexdec (\$matches[1])) . chr (hexdec (\$matches[2]));
						else
							\$r = chr (0) . chr (hexdec (\$matches[1]));
						\$r = iconv (UCS2, UHC, \$r);
						return \$r;
					"),
					$s
				);
			return $r;
		}

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

	function toncr ($string, $asc = false) {
		if ( $this->obj->is_extfunc () )
			$string = $this->obj->extfunc (UHC, UCS2, $string);

		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( $this->obj->is_extfunc () ) {
				/* iconv or mbstring mode */
				if ( ord ($string[$i]) == 0 ) {
					/* ascii area */
					$r .= ( $asc === true ) ?
						$string[$i+1] :
						'&#' . $this->obj->chr2hex ($string[$i+1], false, true) . ';';
				} else {
					/*
					 * print dec
					$hex = $this->obj->chr2hex ($string[$i], false) .
							$this->obj->chr2hex ($string[$i+1], false);
					$r .= '&#' .
						hexdec ($hex) . ';';
					 */
					/*
					 * print hex
					 */
					$r .= '&#x' .
						$this->obj->chr2hex ($string[$i], false) .
						$this->obj->chr2hex ($string[$i+1], false) . ';';
				}
				$i++;
			} else {
				/* pure mode */
				if ( ord ($string[$i]) & 0x80 ) {
					$r .= '&#' . $this->obj->ksc2ucs ($string[$i], $string[$i+1]) . ';';
					$i++;
				} else {
					# $asc == true, don't convert ascii code to NCR code
					$r .= ( $asc === true ) ? $string[$i] : '&#' . ord ($string[$i]) . ';';
				}
			}
		}

		return $r;
	}

	function todencr ($str) {
		if ( $this->obj->is_extfunc () ) {
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
						return iconv ('ucs-2be', 'uhc', \$n);
					}

					/* little endian */
					\$n .= chr (0);

					return iconv ('ucs-2', 'uhc', \$n);
				"),
				$str
			);

			return $r;
		}

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
					$org_ksx1001 = $this->obj->ksx1001;
					$this->obj->ksx1001 = false;

					$r .= $this->obj->ucs2ksc ($c);

					$this->obj->ksx1001 = $org_ksx1001;
				} else
					$r .= chr (hexdec ($c));
			} else
				$r .= $str[$i];
		}

		return $r;
	}

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
