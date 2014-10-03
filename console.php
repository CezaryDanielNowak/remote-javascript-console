<?php
/*
	simple usage:
	<script src="http://localhost/console/console.php?key=XYZ"></script>

	make sure if you /logs/ directory is writable.
*/

/**** CONFIGS: ****/
/*
	key:	default: default
			Which log file will be used.
			If not exist, /log/{$key} will be generated automaticly.
			Select any key, you want.
			Dont use special characters like /, \, &, | in your key,
			because it is filename.
*/
$key = isset($_GET['key']) ? $_GET['key']			: 'default';
/*
	async:	default: true
			when it's "false", console.log triggers async request to console.
*/
$async = isset($_GET['async']) ? ($_GET['async'] === 'false' ? false : true) : true;
/*
	consoleUrl:	default: null
				when it's not passed, the only way you can insert
				console.php to your code is: <script src=".."></script>
				and you can not use async or defer.
				When it contains console.php url, yo ucan get the script
				any way, you want.
*/
$consoleUrl = isset($_GET['consoleUrl']) ? $_GET['consoleUrl']: null;


$_args = isset($_GET['_args']) ? $_GET['_args']		: false;
$_method = isset($_GET['_method']) ? $_GET['_method']	: false;

/* write logs, logToServer target */
if($_method !== false && $_args !== false) {
	$file = "./logs/{$key}.log";
	$data = date('c') . "|{$_method}|{$_args}\n";
	@file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
	die('OK');
}

/* default action: get javascript */
header('Content-Type: application/javascript');
?>
<?php if($consoleUrl): ?>
var scriptSrc = <?php echo json_encode($consoleUrl); ?>;
<?php else: ?>
var scriptSrc = (function() {
	var scripts = document.getElementsByTagName('script');
	var thisScriptTag = scripts[scripts.length-1];
	return thisScriptTag.attributes.src.value;
})();
<?php endif; ?>

function httpGet(theUrl) {
    var xmlHttp;
    xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", theUrl, <?php echo $async ? 'true' : 'false'; ?> );
    xmlHttp.send( null );
    return xmlHttp;
}

function logToServer(type, args) {
	args = JSON.stringify(Array.prototype.slice.call(args));
	httpGet(scriptSrc + '&_method=' + encodeURIComponent(type) + '&_args=' + encodeURIComponent(args))
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
	var consoleWrapper = function() {
		var method = Array.prototype.shift.call(arguments);
		logToServer(method, arguments);
		return origConsole[method].apply(origConsole, arguments);
	};
	for (var i = 0, ml = methods.length; i < ml; i++) {
		if (window.console[methods[i]]) {
			window.console[methods[i]] = consoleWrapper.bind(null, methods[i])
		} else {
			window.console[methods[i]] = (function(method) {
				return function() {
					logToServer(method, arguments);
				};
			})(methods[i]);
		}
	}
})();
