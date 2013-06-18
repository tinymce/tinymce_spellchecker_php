/**
 * Various build tools for Jake.
 */

/*jshint smarttabs:true, undef:true, node:true, latedef:true, curly:true, bitwise:true */
"use strict";

var fs = require("fs");
var path = require("path");

exports.zip = function (options) {
	var ZipWriter = require('moxie-zip').ZipWriter;
	var archive = new ZipWriter();

	function process(filePath, zipFilePath) {
		var args, stat = fs.statSync(filePath);

		zipFilePath = zipFilePath || filePath;
		filePath = filePath.replace(/\\/g, '/');
		zipFilePath = zipFilePath.replace(/\\/g, '/');

		if (options.pathFilter) {
			args = {filePath: filePath, zipFilePath: zipFilePath};
			options.pathFilter(args);
			zipFilePath = args.zipFilePath;
		}

		if (options.exclude) {
			for (var i = 0; i < options.exclude.length; i++) {
				var pattern = options.exclude[i];

				if (pattern instanceof RegExp) {
					if (pattern.test(filePath)) {
						return;
					}
				} else {
					if (filePath === pattern) {
						return;
					}
				}
			}
		}

		if (stat.isFile()) {
			var data = fs.readFileSync(filePath);

			if (options.dataFilter) {
				args = {filePath: filePath, zipFilePath: zipFilePath, data: data};
				options.dataFilter(args);
				data = args.data;
			}

			archive.addData(path.join(options.baseDir, zipFilePath), data);
		} else if (stat.isDirectory()) {
			fs.readdirSync(filePath).forEach(function(fileName) {
				process(path.join(filePath, fileName), path.join(zipFilePath, fileName));
			});
		}
	}

	options.baseDir = (options.baseDir || '').replace(/\\/g, '/');

	options.from.forEach(function(filePath) {
		if (filePath instanceof Array) {
			process(filePath[0], filePath[1]);
		} else {
			process(filePath);
		}
	});

	archive.saveAs(options.to);
};

exports.getReleaseDetails = function (filePath) {
	var firstLine = ("" + fs.readFileSync(filePath)).split('\n')[0];

	return {
		version: /^Version ([0-9xabrc.]+)/.exec(firstLine)[1],
		releaseDate: /^Version [^\(]+\(([^\)]+)\)/.exec(firstLine)[1]
	};
};
