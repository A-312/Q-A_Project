require.load({
	js: [
		"lib/jquery-ui.js",
		"lib/codemirror-highlight.js",
		"question.js",
		"site.js"
	],
	css: [
		"./design/ui/jquery-ui.css",
		"./design/codemirror.css"
	]
}, function() {
	site_main();
	question_main();
}); //, "?debug="+(new Date()).getTime());