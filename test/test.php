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
$test = false;

if ( $cli !== true ) {
	header('Content-Type: text/html');
	echo "<pre>";
}

if ( $test === true ) {
	$path = ini_get ('include_path');
	$path .= ':..';
	ini_set ('include_path', $path);
}

require_once 'KSC5601.php';

$obj = new KSC5601;
$obj->usepure ();
#$obj->noKSX1001 ();

$t1 = microtime ();
$ksc = file_get_contents ('./test.txt');

pr ('¿ø¹®', $ksc);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

$t1 = microtime ();
$utf = $obj->toutf8 ($ksc);

pr ('UTF8', $utf);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

$t1 = microtime ();
$ksc1 = $obj->toksc5601 ($utf);

pr ('KSC', $ksc1);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

$t1 = microtime ();
$ucs = $obj->toucs4 ($ksc);

pr ('UCS4', $ucs);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

$t1 = microtime ();
$ducs = $obj->todeucs4 ($ucs);

pr ('DUCS4', $ducs);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

$t1 = microtime ();
$ncr = $obj->toncr ($ksc, true);

pr ('NCR', $ncr);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";

$t1 = microtime ();
$dncr = $obj->todencr ($ncr);

pr ('DNCR', $dncr);
$t2 = microtime ();
echo "=>  " . mtime ($t1, $t2) . " sec\n";


#$z = utf8encode_lib ($ksc);
#$z = utf8decode_lib ($z, 'cp949');
#pr ('TEMP', $z);
#echo uniencode_lib ($ksc, 'U+') . "\n";
#echo unidecode_lib ($ucs, 'euc-kr', 'U+') . "\n";

$time2 = microtime ();

echo "=>  " . mtime ($time1, $time2) . " sec\n"
?>
