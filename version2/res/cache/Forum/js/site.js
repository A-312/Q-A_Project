function val_onfocus(o) {
	if (o.getAttribute("data-defaut") === null) {
		o.setAttribute("data-defaut", (o.tagName == "INPUT") ? o.value : o.innerHTML);
	}

	val_chang(o, o.getAttribute("data-defaut"), "");
}

function val_onblur(o) {
	val_chang(o, "", o.getAttribute("data-defaut"));
}

function val_chang(o, oldValue, newValue) {
	if (o.tagName == "INPUT" && o.value === oldValue) {
		o.value = newValue;
	} else if (o.innerHTML === oldValue) {
		o.innerHTML = newValue;
	}
}

function site_main() {
	if ($("body div#content form#search div#searchbloc.I-search-tag")[0]) {
		$("#term").bind("keydown", function(event) { 
			if (event.keyCode === $.ui.keyCode.TAB && $(this).data("ui-autocomplete").menu.active) { event.preventDefault(); }
		}).autocomplete({
			minLength: 2, source: "script/tagautocomplete.php",
			select: function(event, ui) { this.value = ui.item.label; $("form#search").submit(); }
		}).data("ui-autocomplete")._renderItem = function(ul, item) {
			return $("<li>").append("<a>"+item.label+" <span class=AOdesc>x"+item.desc+"</span></a>").appendTo(ul);
		};
	}

	if ($("body div#content div#redactionquestion.I-write-my-answer")[0]) {
		bkLib.onDomLoaded(function() { nicEditors.allTextAreas({fullPanel : true}); });
		function split(val) { return val.split(/,\s*/); }
		function extractLast(term) { return split(term).pop(); }
		$("#tagquestion").bind("keydown", function(event) { 
			if (event.keyCode === $.ui.keyCode.TAB && $(this).data("ui-autocomplete").menu.active) { event.preventDefault(); }
		}).autocomplete({
			minLength: 2, source: function(request, response) { $.getJSON("script/tagautocomplete.php", { term: extractLast(request.term) }, response); },
			focus: function() { return false; },
			select: function(event, ui) { var terms = split(this.value); terms.pop(); terms.push(ui.item.value); terms.push(""); this.value = terms.join(", "); return false; }
		}).data("ui-autocomplete")._renderItem = function( ul, item ) {
			return $("<li>").append("<a>"+item.label+" <span class=AOdesc>x"+item.desc+"</span></a>").appendTo(ul);
		};
	}

	if ($("#term.ui-for-pseudo-it-is-me")[0]) {
		$("#term").bind("keydown", function(event) { 
			if (event.keyCode === $.ui.keyCode.TAB && $(this).data("ui-autocomplete").menu.active) { event.preventDefault(); }
		}).autocomplete({
			minLength: 2, source: "script/tagautocomplete.php?pseudo=1",
			select: function(event, ui) { this.value = ui.item.label; $("form#search").submit(); }
		}).data("ui-autocomplete")._renderItem = function(ul, item) {
			return $("<li>").append("<a><img alt=\"avatar\" src=\""+item.avatar+"\" />"+item.label+" <span class=AOdesc>"+item.desc+"<br><span class=AOtype>"+item.type+"</span></span></a>").appendTo(ul);
		};
	}
}