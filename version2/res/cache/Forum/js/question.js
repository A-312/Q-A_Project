var _EnCours = false;
function vote(s, idmsg, button) {
	if (!_EnCours) {
		_EnCours = true;
		if ($(button).hasClass("on")) {
			$("#d_vt"+idmsg+" a").removeClass("on");
		} else {
			$("#d_vt"+idmsg+" a").removeClass("on");
			$(button).addClass("on");
		}
		$.get("./script/vote.php?s="+s+"&id="+idmsg, function (data) {
			if (data != "NaN") {
				$("#d_vt"+idmsg+" span#vote_num").html(data);
			} else {
				$("#d_vt"+idmsg+" a").removeClass("on");
			}
			_EnCours = false;
		});
	} else {
		console.info("Patientez...");
	}
}

function suivi(idquestion, idmsg, button) {
	if (!_EnCours) {
		_EnCours = true;
		if ($(button).hasClass("on")) {
			$("#d_vt"+idmsg+" a.star").removeClass("on");
		} else {
			$("#d_vt"+idmsg+" a.star").removeClass("on");
			$(button).addClass("on");
		}
		$.get("./script/vote.php?s=suivre&id="+idquestion, function (data) {
			if (data != "NaN") {
				$("#d_vt"+idmsg+" span#suivi_num").html(data);
			} else {
				$("#d_vt"+idmsg+" a.star").removeClass("on");
			}
			_EnCours = false;
		});
	} else {
		console.info("Patientez...");
	}
}

function submitComment() {
	$(this).parent().parent().find("input[name='commentaire']").val($(this).parent().parent().find("span").text());
	this.parentNode.parentNode.submit();
}

function codeMirror_parse() {
	var list = $("pre[data-class]"), obj = null, text = "", langage = "", bool = false, format = "";
	for (var i = 0; i<$(list).length; i++) {
		obj = $(list)[i];
		langage = $(obj).attr("data-class").toLowerCase();
		if (typeof(langage) != "undefined" && langage != "script"
			&& !$(obj).hasClass("cm-s-default")) {
			format = ($(obj).attr("data-format") == "ligne") ? "ligne" : "normal";
			$(obj).attr("data-format", format);
			text = $(obj).html().replace(/<br( ?\/)?>/g, "\n");
			if (format == "ligne") { text = text.replace(/\s+/g, " ").replace(/\s$/g, ""); }
			$(obj).html(text);
			text = $(obj).text();
			$(obj).attr("data-plaintext", text);
			$(obj).addClass("cm-s-default");

			bool = false;

			boucle_loop:
			for (var x in CodeMirror.modeInfo) {
				if (langage == CodeMirror.modeInfo[x].name.toLowerCase() || langage == CodeMirror.modeInfo[x].mime) {
					langage = CodeMirror.modeInfo[x];
					bool = true;
					break boucle_loop;
				}
			}
			if (bool) {
				text = text.replace(/^\#\{script:[ ]?([A-z0-9-+#().]+)(,[ ]?mime:[ ]?(text|application|message)\/([A-z0-9-+]+))?\}[\s]*/, "");
				$(obj).attr("data-langagename", langage.name);
				CodeMirror.runMode(text, langage.mime, $(obj)[0]);
			} else {
				$(obj).html(text + ((format == "normal") ? "\n" : "")+"<span class=\"cm-error\">#La coloration syntatique n'est pas disponible pour \""+langage+"\".");
			}
		}
	}
}

function question_main() {
	if ($("body div#content div#subContent.I-read-this-content")[0]) {
		/* Commentaire */
		$(".ajouter").click(function () {
			$(this).parent().prev().toggle().find("span").focus();
			$(this).parent().prev().find("a").click(submitComment);
			$(this).parent().remove();
		});

		/* nicEdit */
		if (typeof(bkLib) != "undefined") {
			bkLib.onDomLoaded(function() { nicEditors.allTextAreas({fullPanel : true}); });
		}

		/* codeMirror */
		codeMirror_parse();

		/* Colle du texte et non du HTML - http://stackoverflow.com/a/12028136/2226755 */
		document.querySelector("html").addEventListener("paste", function(e) {
			e.preventDefault();
			var text = e.clipboardData.getData("text/plain");
			document.execCommand("insertHTML", false, $("<div></div>").text(text).html());
		});

		/* Liste des réponses */
		var func = function() {
			var hauteur = $(window).scrollTop()-($("#sommairemsg").parent().position().top-10);
			var hmax  = ($("#sommairemsg").parent().height() - $("#sommairemsg").height());
			if (0 < hauteur) {
				if (hauteur < hmax) {
					$("#sommairemsg").css("top", hauteur+"px");
				} else {
					$("#sommairemsg").css("top", hmax+"px");
				}
			} else {
				$("#sommairemsg").css("top", "0px");
			}
		};
		$(window).bind('scroll', func); func();
		if (1272 > $("#content").width()) {
			$("#sommairemsg").hide();
		}
		$(window).resize(function() {
			if (1272 < $("#content").width()) {
				$("#sommairemsg").show(100);
			} else {
				$("#sommairemsg").hide(500);
			}
		});
		$("#sommairemsg a").click(function(){
			$("html, body").animate({
				scrollTop: $($.attr(this, 'href')).offset().top
			}, 500);
			return false;
		});
	}
}

var _nicEditorSel = null;
function toggleEditor(id) {
	var c = false;

	id = 'textmsg'+id;
	if(!_nicEditorSel) {
		c = true;
	} else {
		_nicEditorSel[0].removeInstance(_nicEditorSel[1]);
		$('#'+_nicEditorSel[1]).next().hide();
		$('#'+_nicEditorSel[1]).html($('#'+_nicEditorSel[1]).attr("data-plaintext"));
		codeMirror_parse();

		c = (_nicEditorSel[1] != id);
		_nicEditorSel = null;
	}

	if (c) {
		$('#'+id).find("pre[data-class]").each(function(){
			if ($(this).hasClass("cm-s-default")) {
				$(this).text($(this).attr("data-plaintext"));
				$(this).removeAttr("data-plaintext").removeClass("cm-s-default");
			}
		});
		_nicEditorSel = [new nicEditor({fullPanel : true}).panelInstance(id, {hasPanel : true}), id];
		$('#'+id).attr("data-plaintext", $('#'+id).html());
		$('#'+id).next().show();
	}
}

function submitEdit(form) {
	var id = 'textmsg'+$(form).find("input[name='idmessage']").val(),
		obj = nicEditors.findEditor(id).elm;

	$(obj).find("pre[data-class]").each(function() {
		var format = ($(this).attr("data-format") == "ligne") ? "ligne" : "normal",
			text = $(this).html().replace(/<br( ?\/)?>/g, "\n");

		if (format == "ligne") { text = text.replace(/\s+/g, " ").replace(/\s$/g, ""); }
		$(this).html(text);

		$(this).text($(this).text());
	});

	$(form).find("input[name='message']").val($(obj).html());

	return true;
}