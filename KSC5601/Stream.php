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
 * $Id: Stream.php,v 1.1.1.1 2008-09-29 14:41:17 oops Exp $
 */

class KSC5601_Stream
{
	function chr2hex ($c, $prefix = true) {
		$prefix = $prefix ? '0x' : '';
		return $prefix . dechex (ord ($c));
	}

	function hex2chr ($c) {
		return chr (hexdec ($c));
	}

	function chr2dec ($c) {
		return ord ($c);
	}

	function chr2bin ($c, $shift = '') {
		if ( preg_match ('/^(U\+|0x)/', $c) )
			$c = $this->hex2chr ($c);

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
