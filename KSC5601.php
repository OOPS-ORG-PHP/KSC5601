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
 * @version    CVS: $Id: KSC5601.php,v 1.1.1.1 2008-09-29 14:41:17 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear
 * @since      File available since Release 0.1
 * $Id: KSC5601.php,v 1.1.1.1 2008-09-29 14:41:17 oops Exp $
 */

require_once 'KSC5601/UTF8.php';

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

	function usePure () {
		$this->obj->iconv = false;
		$this->obj->mbstring = false;
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

	function toutf8 ($string) {
		return $this->obj->utf8enc ($string);
	}

	function toksc5601 ($string) {
		return $this->obj->utf8dec ($string);
	}

	function toucs4 ($string, $asc = false) {
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) & 0x80 ) {
				$r .= 'U+' . strtoupper (dechex ($this->obj->ksc2ucs ($string[$i], $string[$i+1])));
				$i++;
			} else {
				# $asc == true, don't convert ascii code to NCR code
				$r .= ( $asc === false ) ? $string[$i] : 'U+' . strtoupper (dechex (ord ($string[$i])));
			}
		}

		return $r;
	}

	function todeucs4 ($string) {
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

	function toncr ($string, $asc = false) {
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) & 0x80 ) {
				$r .= '&#' . $this->obj->ksc2ucs ($string[$i], $string[$i+1]) . ';';
				$i++;
			} else {
				# $asc == true, don't convert ascii code to NCR code
				$r .= ( $asc === true ) ? $string[$i] : '&#' . ord ($string[$i]) . ';';
			}
		}

		return $r;
	}

	function todencr ($str) {
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

				if ( (strlen ($c) % 2) || preg_match ('/^[0-9]$/i', $c) )
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
