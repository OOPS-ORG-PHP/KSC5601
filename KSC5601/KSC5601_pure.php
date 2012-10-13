<?php
/**
 *
 * KSC5601 �� pure php code �� ó���ϱ� ���� KSC5601 class
 *
 * @category    Charset
 * @package     KSC5601_pure
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   2009 (c) JoungKyun.Kim
 * @license     BSD License
 * @version     $Id: KSC5601_pure.php,v 1.3 2009-03-17 09:33:24 oops Exp $
 * @link        ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**#@+
 * ���� extension mode ���� ����
 */
/*
 * ���� iconv / mbstring ��� ��� ����
 */
define ('EXTMODE',    false);
/**#@-*/

require_once 'KSC5601/UTF8.php';

/**
 * KSC5601 �� UTF-8 ���� ���ڼ� ��ȯ �� ������ ���� ��� ����
 *
 * @category    Charset
 * @package     KSC5601_pure
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   2009 (c) JoungKyun.Kim
 * @license     BSD License
 * @version     Release:
 */
Class KSC5601
{
	private $obj;
	private $out_ksx1001 = false;

	function __construct () {
		$this->obj = new KSC5601_UTF8;

		if ( $GLOBALS['table_ksc5601'] )
			$obj->ksc = $GLOBALS['table_ksc5601'];
		if ( $GLOBALS['table_ksc5601_hanja'] )
			$obj->hanja = $GLOBALS['table_ksc5601_hanja'];
		if ( $GLOBALS['table_ksc5601_rev'] )
			$obj->revs = $GLOBALS['table_ksc5601_rev'];
	}

    /**
	 * KSX1001 ���� ���� �ѱ��� ó��
	 * @access  public
	 * @param   boolean $flag
	 *  <ol>
	 *      <li>true : UTF8 decode �ÿ� KSX1001 �������� �ѱ��� NCR ó�� �Ѵ�.</li>
	 *      <li>true : NCR encode �ÿ� KSX1001 ������ �ѱ۸� NCR ó�� �Ѵ�.</li>
	 *      <li>false : �ƹ��ϵ� ���� �ʴ´�.</li>
	 *  </ol>
	 * @return  void
	 */
	function out_of_ksx1001 ($flag = false) {
		$this->obj->out_ksx1001 = $flag;
	}

    /**
	 * �־��� ���ڿ��� utf8 ���� üũ
	 * @param   string  $string     �˻��� ���ڿ�
	 * @return  boolean utf8 �� ��� true �� ��ȯ
	 * @access  public
	 */
	function is_utf8 ($string) {
		return $this->obj->is_utf8 ($string);
	}

    /**
	 * UHC <-> UTF8 ���ڼ� ��ȯ
	 * @access  public
	 * @param   string  $string ��ȯ�� ���ڿ�
	 *      ������ ���� ������ �⺻���� UTF8 �� ���ڼ��� ��ȯ��.
	 *      UTF8 �� �ƴ� ��� UHC(CP949) �� ��ȯ ��. KSC5601::out_of_ksx1001
	 *      �� true �� ���, ���ڵ� �ÿ� KSX1001 ���� ���� �ѱ��� NCR ó��
	 *      ��. @see KSC5601::out_of_ksx1001
	 * @param   string  $to     ���ڵ�(UTF8)/���ڵ�(UHC) [�⺻: UTF8]
	 * @return  string
	 */
	function utf8 ($string, $to = UTF8) {
		if ( $to === UTF8 )
			return $this->obj->utf8enc ($string);

		return $this->obj->utf8dec ($string);
	}

    /**
	 * UHC <-> UCS2 ���ڼ� ��ȯ
	 * @access  public
	 * @param   string  $string ��ȯ�� ���ڿ�
	 *      ������ ���� ������ �⺻���� UCS2 hexical �� ���ڼ��� ��ȯ��.
	 *      UCS2 �� �ƴ� ��� UCS2 hexical �� UHC(CP949) �� ��ȯ ��.
	 * @param   string  $to     ���ڵ�(UCS2)/���ڵ�(UHC) [�⺻: UCS2]
	 * @param   boolean $asc    true ���, ��� ���ڸ� UCS2 hexical �� ��ȯ
	 *                          false �� ��� KSX1001 ���� ���� �ѱ۸� UCS hexical �� ��ȯ
	 *                          ���ڵ� �ÿ��� ������� ����
	 *                          �⺻�� false
	 * @return  string
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

    /**
	 * UHC <-> NCR (Numeric Code Reference) ���ڼ� ��ȯ
	 * @access  public
	 * @param   string  $string ��ȯ�� ���ڿ�
	 *      ������ ���� ������ �⺻���� NCR code �� ���ڼ��� ��ȯ��.
	 *      NCR �� �ƴ� ��� NCR code �� UHC(CP949) �� ��ȯ ��.
	 * @param   string  $to     ���ڵ�(NCR)/���ڵ�(UHC) [�⺻: NCR]
	 * @param   boolean $enc    true ���, ��� ���ڸ� NCR code �� ��ȯ
	 *                          false �� ��� KSC5601::out_of_ksx1001 �� ������ true �� ���
	 *                          KSX1001 ���� ���� �ѱ۸� NCR �� ��ȯ�ϸ�, false �� ��� UHC
	 *                          ��� ������ NCR�� ��ȯ. ���ڵ� �ÿ��� ������� ����
	 *                          �⺻�� false
	 * @return  string
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

	/**
	 * KSC5601 reverse table ������ ���� method
	 *
	 * @access public
	 * @return string
	 */
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
