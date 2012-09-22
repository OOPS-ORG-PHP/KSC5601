<?
function mtime($old, $new) {
	$start = explode(" ", $old);
	$end = explode(" ", $new);

	return sprintf("%.2f", ($end[1] + $end[0]) - ($start[1] + $start[0]));
}
function pr ($title, $msg) {
	printf ("%-6s => %s\n", $title, $msg);
}

$time1 = microtime ();

$cli = ( php_sapi_name () == 'cli' ) ? true : false;

if ( $cli !== true ) {
	header('Content-Type: text/html');
	echo "<pre>";
}

if ( file_exists ('../KSC5601.php') ) {
	# 하위 디렉토리에 KSC5601.php 가 존재할 경우, source tree에서의 테스트로
	# 간주하여, include_path에 현재 디렉토리의 파일을 가장 우선시 시키고,
	# 하위 디렉토리로 이동
	$path = ini_get ('include_path');
	$path = '.:' . $path;
	ini_set ('include_path', $path);

	chdir ('..');
}

require_once 'KSC5601.php';


/*
 * TEST CODE START
 */


$obj = new KSC5601;

# 표시할 수 없는 KSX1001 범위 밖의 문자 (CP949/UHC 확장 영역) 를 
# NCR code 로 변경 한다.
#$obj->out_of_ksx1001 (true);

$t1 = microtime ();
$ksc = file_get_contents ('./test/test.txt');

pr ('원문', $ksc);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

/*
 * Convert EUC-KR(or UHC/CP949) to UTF8
 */
$t1 = microtime ();
$utf = $obj->utf8 ($ksc, UTF8);

pr ('UTF8', $utf);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

/*
 * Convert UTF8 to UHC/CP949
 *
 * todo : utf8 -> UHC/CP949 처리
 *
 */
$t1 = microtime ();
$ksc1 = $obj->utf8 ($utf, EUC-KR);

pr ('KSC', $ksc1);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

/*
 * convert EUC-KR (or UHC/CP949) to UCS2
 */
$t1 = microtime ();
$ucs = $obj->ucs2 ($ksc, UCS2);

pr ('UCS2', $ucs);
$t2 = microtime ();

/*
 * convert UCS2 to UHC/CP949
 */
$t1 = microtime ();
$ducs = $obj->ucs2 ($ucs, UHC);

pr ('DUCS2', $ducs);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

/*
 * conver EUC-KR (or UHC/CP949) to NCR
 */
$t1 = microtime ();
$ncr = $obj->ncr ($ksc, NCR);

pr ('NCR', $ncr);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

/*
 * convert NCR to UHC/CP949
 */
$t1 = microtime ();
$dncr = $obj->ncr ($ncr, UHC);

pr ('DNCR', $dncr);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";


#$z = utf8encode_lib ($ksc);
#$z = utf8decode_lib ($z, 'cp949');
#pr ('TEMP', $z);
#echo uniencode_lib ($ksc, 'U+') . "\n";
#echo unidecode_lib ($ucs, 'euc-kr', 'U+') . "\n";

$time2 = microtime ();

echo "=>  " . mtime ($time1, $time2) . " sec\n";

?>
