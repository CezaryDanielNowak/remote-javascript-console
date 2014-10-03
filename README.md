REMOTE JAVASCRIPT CONSOLE
===========

What it does: This script saves console output to file remotely.


USAGE:
===========
- make sure if you /logs/ directory is writable.


Basic usage:
-----------
In html:
```
	<script src="http://localhost/console/console.php?key=XYZ"></script>
```
Your logs will be saved in `logs/XYZ.log`
You can not use `async` or `defer`.

Advanced usage:
-----------
In javascript:
```
(function(filename, args) {
	var fileref = document.createElement('script');
	var params = Object.keys(args).map(function(key) {
		return key + '=' + encodeURIComponent(args[key]);
	}).join('&');
	args.consoleUrl || (params += '&consoleUrl=' + encodeURIComponent(filename + '?' + params) );

	fileref.async=1;
	fileref.src=filename + '?' + params;
	document.head.appendChild(fileref);
	return fileref;
})('http://localhost/console/console.php', {
	key: 'XYZ',
	async: true
});
```

PARAMS:
===========
`key`	default: default

	Which log file will be used.
	If not exist, /log/{$key} will be generated automaticly.
	Select any key, you want.
	Dont use special characters like /, \, &, | in your key,
	because it is filename.

`async`	default: true
	when it's "false", console.log triggers async request to console.

`consoleUrl`	default: null
	when it's not passed, the only way you can insert
	console.php to your code is: (Basic usage)
	When it contains console.php url, you can get the script
	any way, you want (Advanced usage).
