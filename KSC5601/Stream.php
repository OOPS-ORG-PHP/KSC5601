<?php
/**
 *
 * ���ڼ� ��ȯ�� ���� high level API
 *
 * PHP �� ���ڼ��� �����ϱ� ���Ͽ� binary �� numeric �� �����ϰ�
 * �ѳ��� �� �ֵ��� �����ϴ� high level API
 *
 * @category   Charset
 * @package    KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: Stream.php,v 1.4 2009-03-16 16:48:53 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**
 *
 * ���ڼ� ��ȯ�� ���� high level API
 * PHP �� ���ڼ��� �����ϱ� ���Ͽ� binary �� numeric �� �����ϰ�
 * �ѳ��� �� �ֵ��� �����ϴ� high level API
 *
 * @category   Charset
 * @package    KSC5601_pure
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    Release:
 */
class KSC5601_Stream
{
	/**
	 * binary character �� hex ������ ��ȯ
	 *
	 * @access public
	 * @param string $c 1byte ����
	 * @param string $prefix [optional: default true]
	 *     true �� ��� ��ȯ �� �տ� 0x �� �ٿ� �ش�.
	 * @param boolena dec [optional: default false]
	 *     true �� ���� �Ǹ�, 10���� ������ ��ȯ�Ѵ�.
	 * @return string 16���� ���ڿ� �Ǵ� 10���� ���ڿ�
	 */
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

	/**
	 * Hexical ���ڸ� 1byte binary �� ��ȯ
	 *
	 * @access public
	 * @param string $c 1byte ����
	 * @return binary 1byte binary ����
	 */
	function hex2chr ($c) {
		return chr (hexdec ($c));
	}

	/**
	 * binary ���� 1byte�� �������� ��ȯ
	 *
	 * @access public
	 * @param string $c 1byte binary ����
	 * @return 10���� ���ڿ�
	 */
	function chr2dec ($c) {
		return ord ($c);
	}

	/**
	 * binary ���� 1byte�� �������� ��ȯ
	 *
	 * @access public
	 * @param string $c 1byte binary ����
	 * @param string $shift [optional - default none] >> [N] �Ǵ� << [N] ���ڿ��� shift �� ����
	 * @return 2���� ���ڿ�
	 */
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

	/**
	 * 2���� ���ڿ��� 1byte binary ���ڷ� ��ȯ
	 *
	 * @access public
	 * @param string $c 2���� ���ڿ�
	 * @return 1byte binary ����
	 */
	function bin2chr ($c) {
		return chr (bindec ($c));
	}

	/**
	 * byte �� ���� UTF8�� 2 ��° byte ǥ���� ��ȯ
	 *
	 * @access public
	 * @param string $byte üũ �� byte
	 * @return 2�� ���ڿ�
	 */
	function check2byte ($byte) {
		return decbin (0x80 >> (8 - $byte));
	}

	/**
	 * 10���� ���ڿ��� 4�ڸ� 2���� ���ڿ��� ��ȯ
	 *
	 * @access public
	 * @param string $s ��ȯ �� 10���� ���ڿ�
	 * @param numeric $bit [optional - default:4] ��ȯ Ȱ �ڸ� ��
	 * @return {$bit} �ڸ� ���� ���ڿ�
	 */
	function decbin ($s, $bit = 4) {
		$r = decbin ($s);
		$l = strlen ($r);

		if ( $l < $bit )
			$r = sprintf ("%0{$bit}s", $r);

		return $r;
	}

	/**
	 * �־��� 2 byte �� KSX1001 ���� ���� �ƴ����� üũ
	 *
	 * @access public
	 * @param string $c1 ù��° ����Ʈ binary ����
	 * @param string $c2 �ι�° ����Ʈ binary ����
	 * @param boolean $is_dec [optional - default:false] �־��� ���ڰ�
	 *     10������ ������ ��� true �� ����
	 * @return boolean KSX1001 ���� ���� ���, true. UHC ������ �ƴϰų�
	 *     KSX1001 ���� �ȿ� ���� ��� false
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

	/**
	 * �ΰ��� ������ �ð����� ���� �ӵ��� ���
	 *
	 * @access public
	 * @param array $t1 microtime() of starting
	 * @param array $t2 microtime() of ending
	 * @return string
	 */
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
