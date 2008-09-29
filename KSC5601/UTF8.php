<?php
/*
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
 * $Id: UTF8.php,v 1.1.1.1 2008-09-29 14:41:17 oops Exp $
 */

Require_once 'KSC5601/Stream.php';
require_once 'KSC5601/UCS4.php';

class KSC5601_UTF8 extends KSC5601_UCS4
{
	private $debug = false;
	public $iconv = true;
	public $mbstring = true;

	/*
	 * remove utf8 bom code (first 3byte)
	 */
	function rm_utf8bom ($s) {
		if ( ord ($s[0]) == 0xef && ord ($s[1]) == 0xbb && ord ($s[2]) == 0xbf )
			return substr ($s, 3);

		return $s;
	}

	/*
	 * whether utf8 or not given strings
	 */
	function is_utf8 ($s) {
		$s = $this->rm_utf8bom ($s);
		$l = strlen ($s);

		for ( $i=0; $i<$l; $i++ ) {
			# if single byte charactors, skipped
			if ( ! (ord ($s[$i]) & 0x80) )
				continue;

			$first = $this->chr2bin ($s[$i]);

			# first byte of utf8 is must start 11
			if ( substr ($first, 0, 2) == '10' )
				return 0;

			# except 1st byte
			$byte = strlen (preg_replace ('/^([1]+).*/', '\\1', $first));

			if ( $byte > 6 )
				continue;

			/*
			 * 2byte: 1100000x (10xxxxxx)
			 * 3byte: 11100000 100xxxxx (10xxxxxx)
			 * 4byte: 11110000 1000xxxx (10xxxxxx 10xxxxxx)
			 * 5byte: 11111000 10000xxx (10xxxxxx 10xxxxxx 10xxxxxx)
			 * 6byte: 11111100 100000xx (10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx)
			 */
			for ( $j=1; $j<$byte; $j++ ) {
				if ( $j == 1 ) {
					$n = 8 - $byte;
					if ( $this->chr2bin ($s[$i+1], ">>$n") != $this->check2byte ($byte) )
						return 0;

					continue;
				}

				if ( $this->chr2bin ($str[$i+$j], '>>6') != 10 )
					return 0;
			}

			break;
		}

		return 1;
	}

	function utf8enc ($s) {
		if ( extension_loaded ('iconv') && $this->iconv )
			return iconv ('euc-kr', 'utf8', $s);

		if ( extension_loaded ('mbstring') && $this->mbstring )
			return mb_convert_encoding ($s, 'utf8', 'euc-kr');

		$len = strlen ($s);

		for ( $i=0; $i<$len; $i++ ) {
			if ( ord ($s[$i]) & 0x80 ) {
				$c1 = $s[$i];
				$c2 = $s[$i+1];
				$ucs4 = $this->ksc2ucs ($c1, $c2);

				if ( $ucs4 == '?' ) {
					$r .= $ucs4;
					$i++;
					continue;
				}

				$uni[0] = $this->decbin ($ucs4 >> 12);
				$uni[1] = $this->decbin ($ucs4 >> 8 & 0x0f);
				$uni[2] = $this->decbin ($ucs4 >> 4 & 0x00f);
				$uni[3] = $this->decbin ($ucs4 & 0x000f);

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

	function utf8dec ($s) {
		if ( extension_loaded ('iconv') && $this->iconv )
			return iconv ('utf8', 'euc-kr', $s);
		if ( extension_loaded ('mbstring') && $this->mbstring )
			return mb_convert_encoding ($s, 'euc-kr', 'utf8');

		$s = $this->rm_utf8bom ($s);
		$l = strlen ($s);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($s[$i]) & 0x80 ) {
				$uni1 = ord ($s[$i]);
				$uni2 = ord ($s[$i + 1]);
				$uni3 = ord ($s[$i + 2]);

				# 0x03 -> 00000011
				# 0x30 -> 00110000
				$ucs4 = dechex ($uni1 & 0x0f) .
						dechex ($uni2 >> 2 & 0x0f) .
						dechex ((($uni2 & 0x03) <<2) | (($uni3 & 0x30) >> 4)) .
						dechex ($uni3 & 0x0f);

				if ( $this->debug ) {
					#     ucs0     ucs1  ucs2       ucs3
					#1111(1111).11(1111)(11).11(11)(1111)
					echo 'HEX STR => ' . $ucs4 . "\n";
					echo '0 => ' . $ucs4[0] . ' ' . decbin (hexdec ($ucs4[0])) . "\n";
					echo '1 => ' . $ucs4[1] . ' ' . decbin (hexdec ($ucs4[1])) . "\n";
					echo '2 => ' . $ucs4[2] . ' ' . decbin (hexdec ($ucs4[2])) . "\n";
					echo '3 => ' . $ucs4[3] . ' ' . decbin (hexdec ($ucs4[3])) . "\n";
				}

				$r .= $this->ucs2ksc ($ucs4);

				$i += 2;
			} else
				$r .= utf8_decode ($s[$i]);
		}

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
