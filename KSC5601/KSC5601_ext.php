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
 * @version    CVS: $Id: KSC5601_ext.php,v 1.1 2009-03-16 12:04:39 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear
 * @since      File available since Release 0.1
 * $Id: KSC5601_ext.php,v 1.1 2009-03-16 12:04:39 oops Exp $
 */

require_once 'KSC5601/UTF8.php';

/**
 * Manipulation character set between KSC5601 and UTF-8
 */
Class KSC5601
{
	private $obj;
	private $out_ksx1001 = false;

	function __construct () {
		$this->obj  = $GLOBALS['chk'];
	}

	function out_of_ksx1001 ($flag = false) {
		$this->out_ksx1001 = $flag;
	}

	function is_utf8 ($string) {
		return KSC5601_UTF8::is_utf8 ($string);
	}

	function utf8 ($string, $to = UTF8, $dec = false) {
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
