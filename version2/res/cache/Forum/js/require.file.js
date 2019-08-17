var require = {
	nbrfile_max: 0,
	nbrfile_load: 0,
	success: function() { },
	load: function(tableau, callback, baseURL, prefix) {
		var func = function(e) {
			require.nbrfile_load++;

			if (require.nbrfile_max == require.nbrfile_load) {
				require.success();
			}
		};
		prefix = prefix || "";
		baseURL = baseURL || "";

		if (tableau.js) {
			for (var x in tableau.js) {
				if (typeof(js_is_minify) == "undefined" || js_is_minify.indexOf(tableau.js[x]) == -1) {
					//require.addScript((tableau.js[x]=='javascript/lib/jquery-ui.js')?tableau.js[x]:tableau.js[x]+prefix).onload = func;
					require.addScript(baseURL+tableau.js[x]+prefix).onload = func;

					require.nbrfile_max++;
				}
			}
		}
		if (tableau.css) {
			for (var x in tableau.css) {
				require.addCSS(baseURL+tableau.css[x]+prefix).onload = func;
				
				require.nbrfile_max++;
			}
		}
		require.success = callback;
	},
	addScript: function(url) {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = url;
		script.charset = 'utf-8';
		script.async = false;

		document.getElementsByTagName("head")[0].appendChild(script);

		return script;
	},
	addCSS: function (url) {
		var css = document.createElement("link");
		css.type = "text/css";
		css.href = url;
		css.rel = "stylesheet";

		document.getElementsByTagName("head")[0].appendChild(css);

		return css;
	}
};
require.load({
	js: [
		"js/lib/jquery.js",
		"js/lib/jquery-ui.js",
		"js/lib/codemirror-highlight.js",
		"js/question.js",
		"js/site.js"
	],
	css: [
		"design/ui/jquery-ui.css",
		"design/codemirror.css"
	]
}, function() {
	site_main();
	question_main();
}, getMetaContentByTagName("requirejs.dir")); //, "?debug="+(new Date()).getTime());

function getMetaContentByTagName(c) {
	for (var b = document.getElementsByTagName("meta"), a = 0; a < b.length; a++) {
		if (c == b[a].name || c == b[a].getAttribute("property")) { return b[a].content; }
	} return false;
}