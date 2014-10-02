<?php
/*
	usage:
	<script src="http://localhost/console/console.php?key=XYZ">
*/


$key = isset($_GET['key']) ? $_GET['key'] 			: 'default';
$method = isset($_GET['method']) ? $_GET['method']	: false;
$args = isset($_GET['args']) ? $_GET['args']		: false;
$async = isset($_GET['async']) ? $_GET['async']		: true;

/* write logs, logToServer target */
if($method !== false && $args !== false) {
	$file = "./logs/{$key}.log";
	$data = date('c') . "|{$method}|{$args}\n";
	@file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
	die('OK');
}

header('Content-Type: application/javascript');
?>
var scriptSrc = (function() {
	var scripts = document.getElementsByTagName('script');
	var thisScriptTag = scripts[scripts.length-1];
	return thisScriptTag.attributes.src.value;
})();

function httpGet(theUrl) {
    var xmlHttp;
    xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", theUrl, <?php echo $async ? 'true' : 'false'; ?> );
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

function logToServer(type, args) {
	args = JSON.stringify(Array.prototype.slice.call(args));
	httpGet(scriptSrc + '&method=' + encodeURIComponent(type) + '&args=' + encodeURIComponent(args))
}

(function() {
	if (!window.console) {
		window.console = {};
	}
	var methods = [
		"log", "info", "warn", "error", "debug", "trace", "dir", "group",
		"groupCollapsed", "groupEnd", "time", "timeEnd", "profile", "profileEnd",
		"dirxml", "assert", "count", "markTimeline", "timeStamp", "clear"
	];
	var origConsole = window.console;
	window.console = {};
	for (var i = 0, ml = methods.length; i < ml; i++) {
		if (window.console[methods[i]]) {
			window.console[methods[i]] = (function(method) {
				return function() {
					logToServer(method, arguments);
					return origConsole[method].apply(window.console, arguments);
				};
			})(methods[i]);
		} else {
			window.console[methods[i]] = (function(method) {
				return function() {
					logToServer(method, arguments);
				};
			})(methods[i]);
		}
	} 
})();
