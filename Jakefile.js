var zip = require('./tools/BuildTools').zip;
var getReleaseDetails = require('./tools/BuildTools').getReleaseDetails;
var fs = require("fs");

desc("Default build task");
task("default", ["release"], function () {});

task("release", [], function () {
	var details = getReleaseDetails("changelog.txt");

	if (!fs.existsSync("tmp")) {
		fs.mkdirSync("tmp");
	}

	zip({
		baseDir: "spellchecker",

		from: [
			"includes",
			"spellchecker.php",
			"dicts/readme.md",
			"changelog.txt"
		],

		to: "tmp/tinymce_spellchecker_php_" + details.version + ".zip"
	});
});
