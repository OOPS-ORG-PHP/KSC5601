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
 * $Id: Stream.php,v 1.3 2009-03-16 12:04:39 oops Exp $
 */

class KSC5601_Stream
{
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

	function hex2chr ($c) {
		return chr (hexdec ($c));
	}

	function chr2dec ($c) {
		return ord ($c);
	}

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

	function bin2chr ($c) {
		return chr (bindec ($c));
	}

	function check2byte ($byte) {
		return decbin (0x80 >> (8 - $byte));
	}

	function decbin ($s, $bit = 4) {
		$r = decbin ($s);
		$l = strlen ($r);

		if ( $l < $bit )
			$r = sprintf ("%0{$bit}s", $r);

		return $r;
	}

	/*
	 * proto boolean is_out_of_ksx1001 (chr1, chr2[, is_dec])
	 *
	 * Check out of ksx1001 range in UHC/CP949
	 * return value:
	 *       return true if char1 and char2 is UHC/CP949 extended range
	 *       nor return false
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
