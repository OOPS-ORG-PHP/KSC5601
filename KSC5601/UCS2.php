<?
/*
 * KSC5601 패키지에서 USC2를 제어하기 위한 API Class
 *
 * @category   Charset
 * @package    KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: UCS2.php,v 1.5 2009-03-16 16:48:53 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

require_once 'KSC5601/Stream.php';

/**
 * KSC5601 패키지에서 USC2를 제어하기 위한 API Class
 *
 * @category   Charset
 * @package    KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    Release:
 */
Class KSC5601_UCS2 extends KSC5601_Stream
{
	public $ksc     = NULL;
	public $hanja   = NULL;
	public $revs    = NULL;
	public $ksc_max = 0;
	public $han_max = 0;
	public $rev_max = 0;

	function __construct () {
		$this->init_ksc5601 ();
	}

	/*
	 * KSC5601 코드 테이블을 초기화 한다.
	 *
	 * pure code 를 사용할 경우 필요한 KSC5601 code table 을 메모리에
	 * 로딩한다. 만약 되어 있을 경우 skip을 한다.
	 *
	 * @access public 
	 * @param void
	 * @return void
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

	/**
	 * KSC5601 -> UCS2
	 * return decimical value or question mark '?'
	 *
	 * @access public
	 * @param string $c1 1st byte binary 문자
	 * @param string $c2 2st byte binary 문자
	 * @return string 10진수 문자열 [42531]
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
	 * UCS2 -> KSC5601
	 * 16진수 문자열을 2byte binary 문자로 변환
	 *
	 * @access public
	 * @param string $s 16진수 문자열
	 * @return string 2byte binary 문자
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

		# out of KSX 1001 range in CP949/UHC
		if ( $this->out_ksx1001 === true ) {
			if ( $this->is_out_of_ksx1001 ($k1, $k2, true) ) {
				$hex = dechex ($this->ksc2ucs (chr ($k1), chr ($k2)));
				return '&#x' . strtoupper ($hex) . ';';
			}
		}

		return chr ($k1) . chr ($k2);
	}

	/*
	 * UCS2 -> KSC5601
	 * 16진수 문자열을 2byte binary 문자로 변환
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function mk_revTable () {
		$this->init_ksc5601 ();

		echo "<?\n" .
			"/*\n" .
			" * this array is made by KSC5601_UCS2::mk_revtable method\n" .
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
