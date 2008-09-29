<?
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
 * $Id: UCS2.php,v 1.1.1.1 2008-09-29 14:41:17 oops Exp $
 */

require_once 'KSC5601/Stream.php';

Class KSC5601_UCS4 extends KSC5601_Stream
{
	public $ksc     = NULL;
	public $hanja   = NULL;
	public $revs    = NULL;
	public $ksc_max = 0;
	public $han_max = 0;
	public $rev_max = 0;
	public $ksx1001 = true;

	function __construct () {
		$this->init_ksc5601 ();
	}

	/*
	 * init char table
	 */
	function init_ksc5601 () {
		if ( $this->ksc != NULL ) {
			if ( ! $this->ksc_max )
				$this->ksc_max = count ($this->ksc);

			if ( ! $this->han_max )
				$this->han_max = count ($this->hanja);

			if ( ! $this->rev_max )
				$this->rev_max = count ($this->revs);

			return;
		}

		if ( $GLOBALS['table_ksc5601'] ) {
			$this->ksc   = $GLOBALS['table_ksc5601'];
			$this->hanja = $GLOBALS['table_ksc5601_hanja'];
			$this->revs  = $GLOBALS['table_ksc5601_rev'];
		} else {
			#$t1 = microtime ();
			require_once 'KSC5601/ksc5601.php';
			#$t2 = microtime ();
			#$t = $this->execute_time ($t1, $t2);
			#printf ("############ INCLUDE CODE FILE (%s sec)\n", $t);
			$this->ksc   = $table_ksc5601;
			$this->hanja = $table_ksc5601_hanja;
			$this->revs  = $table_ksc5601_rev;
		}
		$this->ksc_max = count ($this->ksc);
		$this->han_max = count ($this->hanja);
		$this->rev_max = count ($this->revs);
	}

	/*
	 * KSC5601 -> USC4
	 * return decimical value or question mark '?'
	 */
	function ksc2ucs ($c1, $c2) {
		$this->init_ksc5601 ();

		$c1 = ord ($c1);
		$c2 = ord ($c2);

		if ( $c1 >= 0xca && $c1 <= 0xfd ) {
			/* Hanja Area */
			if ( $c2 < 0xa1 || $c2 > 0xfe )
				return '??';

			$idx = ($c1 - 0xca) * 94 + ($c2 - 0xa1);
			if ( $idx <= 0 || $idx > $this->han_max )
				return '??';

			return $this->hanja[$idx];
		}

		if ( $c2 < 0x41 || $c2 > 0xfe )
			return '??';
		else if ( $c2 > 0x5a && $c2 < 0x61 )
			return '??';
		else if ( $c2 > 0x7a && $c2 < 0x81 )
			return '??';

		if ( $c2 > 0x7a ) $c2 -= 6;
		if ( $c2 > 0x5a ) $c2 -= 6;

		$idx = ($c1 - 0x81) * 178 + ($c2 - 0x41);

		if ( $idx <= 0 || $idx > $this->ksc_max )
			return '??';

		return $this->ksc[$idx];
	}

	/*
	 * USC4 -> KSC5601
	 */
	function ucs2ksc ($s) {
		$this->init_ksc5601 ();

		$c1 = 0x81;
		$c2 = 0x41;

		if ( ! strncmp ($s, 'U+', 2) )
			$s = preg_replace ('/^U\+/', '0x', $s);
		else if ( strncmp ($s, '0x', 2) )
			$s = '0x' . $s;

		$s = hexdec ($s);
		$idx = $GLOBALS['table_ksc5601_rev'][$s];


		if ( ! isset ($idx) )
			return '??';

		$k1 = $idx >> 8;
		$k2 = $idx & 0x00ff;

		# KSX 1001 range
		if ( $this->ksx1001 === true ) {
			if ( (($k1 > 0x80 && $k1 < 0xa1) && ($k2 > 0x40 && $k2 < 0xff)) ||
				 (($k1 > 0xa0 && $k1 < 0xc7) && ($k2 > 0x40 && $k2 < 0xa1)) ) {
				return '&#' . $this->ksc2ucs (chr ($k1), chr ($k2)) . ';';
			 }
		}

		return chr ($k1) . chr ($k2);
	}

	function mk_revTable () {
		$this->init_ksc5601 ();

		echo "<?\n" .
			"/*\n" .
			" * this array is made by KSC5601_UCS4::mk_revtable method\n" .
			" */\n" .
			"\$GLOBALS['table_ksc5601_rev'] = array (\n";

		$records = 1;
		$arrno   = 0;

		/* Hangul reverse Area */

		$c1 = 0x81;
		$c2 = 0x41;

		for ( $i=0; $i<$this->ksc_max; $i++ ) {
			if ( $this->ksc[$i] ) {
				$r = ( $c1 << 8 ) + $c2;
				printf ('%5d => 0x%x', $this->ksc[$i], $r);
			}

			$c2++;
			if ( $c2 == 0x5b ) $c2 = 0x61;
			else if ( $c2 == 0x7b ) $c2 = 0x81;
			else if ( $c2 == 0xff ) {
				$c2 = 0x41;
				$c1++;
			}

			if ( $this->ksc[$i] ) {
				if ( $records == 8 ) {
					echo ",\n";
					$records = 0;
				} else
					echo ', ';	

				$records++;
				$arrno++;
			}
		}

		/* Hanja reverse Area */
		$c1 = 0xca;
		$c2 = 0xa1;

		for ( $i=0; $i<$this->han_max; $i++ ) {
			if ( $this->hanja[$i] ) {
				$r = ( $c1 << 8 ) + $c2;
				printf ('%5d => 0x%x', $this->hanja[$i], $r);
			}

			$c2++;
			if ( $c2 == 0xff ) {
				$c2 = 0xa1;
				$c1++;
			}

			if ( $this->hanja[$i] ) {
				if ( $records == 8 ) {
					echo ",\n";
					$records = 0;
				} else
					echo ', ';	

				$records++;
				$arrno++;
			}
		}

		echo ");\n?>\n";
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
