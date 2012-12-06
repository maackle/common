<?php

function getval($v, $default) {
	if(!isset($v)) return $default;
	else return $v;
}

function array_last($arr) { 
	if(is_array($arr))
		return end($arr); 
	else
		return null;
}


function joinPaths() {
    $args = func_get_args();
    $paths = array();
    foreach ($args as $arg) {
        $paths = array_merge($paths, (array)$arg);
    }

    $paths = array_map(create_function('$p', 'return trim($p, "/");'), $paths);
    $paths = array_filter($paths);
    return join('/', $paths);
}

/**
 * More useful, HTML-friendly print_r
 */
function pr($o, $return=false) {
	static $pr_i = 0;
	static $pr_bgcolors = array(
		'#cfc',
		'#ccf',
		'#ffc',
		'#cff'
	);
	static $pr_bordercolors = array(
		'#3c3',
		'#33c',
		'#cc3',
		'#3cc'
	);
	$rep = print_r($o, 1);
	if (is_null($rep)) {
		$rep = 'NULL';
		$bg = '#ccc';
		$border = '#000';
	}
	elseif ($rep=='') {
		$rep = 'EMPTY';
		$bg = '#ccc';
		$border = '#000';
	}
	else {
		$bg = $pr_bgcolors[$pr_i];
		$border = $pr_bordercolors[$pr_i];
		$pr_i = ($pr_i+1) % count($pr_bgcolors);
	}
	$dbt = debug_backtrace();
	$file = $dbt[0]['file'];
	$line = $dbt[0]['line'];
	
	$out = <<<END
<div class="pr__" style="background: $bg; border: 1px solid $border;">
<b>$file:$line</b>
<pre>
<code> $rep </code>
</pre>
</div>
END;
	if ($return) return $out;
	else echo $out;
}


function getIP() { 
	$ip; 
	if (getenv("HTTP_CLIENT_IP")) 
		$ip = getenv("HTTP_CLIENT_IP"); 
	else if(getenv("HTTP_X_FORWARDED_FOR")) 
		$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if(getenv("REMOTE_ADDR")) 
		$ip = getenv("REMOTE_ADDR"); 
	else 
		$ip = null;
	return $ip; 
}

/**
 * do an http request, return the body
 */
function simple_request($method, $url, $params, $debug=FALSE) {
	$pieces = explode('?', $url);
	if(count($pieces) != 1) trigger_error("Query params passed in the URL will be ignored, pass them to $params instead");
	$url = $pieces[0];
	$method = strtolower($method);

	if($debug) {
		echo "method:"; pr($method);
		echo "url:"; pr($url);
		echo "params:"; pr($params);
	}

	$ch = curl_init();
	switch ($method) {
		case 'get':
			$url .= '?' . http_build_query($params);
			break;
		case 'post':
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			break;
		case 'put':
			curl_setopt($ch, CURLOPT_PUT, TRUE);
			curl_setopt($ch, CURLOPT_PUTFIELDS, $params);
			break;
		case 'delete':
			curl_setopt($ch, CURLOPT_DELETE, TRUE);

			break;
		default:
			die("verb not supported: $method");
			break;
	}
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch,CURLOPT_URL, $url);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

define('DATE_MYSQL', 'Y-m-d H:i:s');

function gmtime() {
	return strtotime(gmdate(DATE_MYSQL));
}

function ago($tm,$rcs = 0) {
   $cur_tm = gmtime(); $dif = $cur_tm-$tm;
   $pds = array('second','minute','hour','day','week','month','year','decade');
   $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
   for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

   $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
   if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
   return $x;
}

function mysql_gmdate($php=null) {
	if(!$php) $php = time();
	return gmdate( 'Y-m-d H:i:s', $php );
}

