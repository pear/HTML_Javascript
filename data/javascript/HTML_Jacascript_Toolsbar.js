/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt                                   |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:                                                             |
// | Pierre-Alain Joye <paj@pearfr.org>                                   |
// +----------------------------------------------------------------------+
// $Id: HTML_Jacascript_Toolsbar.js,v 1.2 2003-05-12 22:38:19 pajoye Exp $
function interrogate(what) {
    var output = '';
    for (var i in what){
        output += i+ "\n";
    }
    alert(output);
}

function HTMLJS_toolbar (buttonList, config) {
    if( typeof buttonList=="undefined" ){
        this.buttonList = new HTMLJS_toolbar.buttonList();
    } else {
        this.buttonList = buttonList;
    }
};

HTMLJS_toolbar.client = browserDetect();

HTMLJS_toolbar.prototype._create = function () {
    var editor = this;
	var toolbar = document.createElement("div");
	this._toolbar = toolbar;
	toolbar.className = "toolbar";

	toolbar.unselectable = "1";
//    toolbar.style.border = "1px solid red";

	var _row = null;
	var _objects = new Object();
	this._toolbarObjects = _objects;

	function newLine() {
		var table = document.createElement("table");
		table.border = "0px";
		table.cellSpacing = "0px";
		table.cellPadding = "0px";
		toolbar.appendChild(table);
		// TBODY is required for IE, otherwise you don't see anything
		// in the TABLE.
		var _body = document.createElement("tbody");
		table.appendChild(_body);
		_row = document.createElement("tr");
		_body.appendChild(_row);
	};

	function createButton(txt) {
		// updates the state of a toolbar element
		function setButtonStatus(id, newval) {
			var oldval = this[id];
			var el = this.element;
			if (oldval != newval) {
				switch (id) {
				    case "enabled":
                        if (newval) {
                            HTMLJS_toolbar._removeClass(el, "buttonDisabled");
                            el.disabled = false;
                        } else {
                            HTMLJS_toolbar._addClass(el, "buttonDisabled");
                            el.disabled = true;
                        }
					break;
				    case "active":
                        if (newval) {
                            HTMLJS_toolbar._addClass(el, "buttonPressed");
                        } else {
                            HTMLJS_toolbar._removeClass(el, "buttonPressed");
                        }
					break;
				}
				this[id] = newval;
			}
		};
		// this function will handle creation of combo boxes
		function createSelect() {
			var options = null;
			var el = null;
			var cmd = null;
			switch (txt) {
			    case "fontsize":
			    case "fontname":
			    case "formatblock":
                    //options = editor.config[txt]; // HACK ;)
				cmd = txt;
				break;
			}
			if (options) {
				el = document.createElement("select");
				var obj = {
					name: txt,     // field name
					element: el,   // the UI element (SELECT)
					enabled: true, // is it enabled?
					text: false,   // enabled in text mode?
					cmd: cmd,      // command ID
					state: setButtonStatus // for changing state
				};
				_objects[txt] = obj;
				for (var i in options) {
					var op = document.createElement("option");
					op.appendChild(document.createTextNode(i));
					op.value = options[i];
					el.appendChild(op);
				}
				HTMLJS_toolbar._addEvent(el, "change", function () {
                        editor._comboSelected(el, txt);
                    }
                );
			}
			return el;
		};
		// the element that will be created
		var el = null;
		var btn = null;

		switch (txt) {
		    case "separator":
                el = document.createElement("div");
                el.className = "separator";
			break;
		    case "space":
                el = document.createElement("div");
                el.className = "space";
			break;
		    case "linebreak":
                newLine();
                return false;
		    case "textindicator":
                el = document.createElement("div");
                el.appendChild(document.createTextNode("A"));
                el.className = "indicator";
                //  el.title = HTMLArea.I18N.tooltips.textindicator;
                var obj = {
                    name: txt,     // the button name (i.e. 'bold')
                    element: el,   // the UI element (DIV)
                    enabled: true, // is it enabled?
                    active: false, // is it pressed?
                    text: false,   // enabled in text mode?
                    cmd: "textindicator", // the command ID
                    state: setButtonStatus // for changing state
                };
                _objects[txt] = obj;
			break;
		    default:
                btn = editor.buttonList.data[txt];
			break;
		}
		if (!el && btn) {
			el = document.createElement("div");
			el.title = btn[1];
			el.className = "button";
			// let's just pretend we have a button object, and
			// assign all the needed information to it.
			var obj = {
				name: txt,     // the button name (i.e. 'bold')
				element: el,   // the UI element (DIV)
				enabled: true, // is it enabled?
				active: false, // is it pressed?
				text: btn[3],  // enabled in text mode?
				cmd: btn[0],   // the command ID
				state: setButtonStatus // for changing state
			};
			_objects[txt] = obj;

			// handlers to emulate nice flat toolbar buttons
			HTMLJS_toolbar._addEvent(el, "mouseover", function () {
				if (obj.enabled) {
					HTMLJS_toolbar._addClass(el, "buttonHover");
				}
			});
			HTMLJS_toolbar._addEvent(el, "mouseout", function () {
				if (obj.enabled) with (HTMLJS_toolbar) {
					_removeClass(el, "buttonHover");
					_removeClass(el, "buttonActive");
					(obj.active) && _addClass(el, "buttonPressed");
				}
			});
			HTMLJS_toolbar._addEvent(el, "mousedown", function (ev) {
				if (obj.enabled) with (HTMLJS_toolbar) {
					_addClass(el, "buttonActive");
					_removeClass(el, "buttonPressed");
					_stopEvent(client.ie ? window.event : ev);
				}
			});
			// when clicked, do the following:
			HTMLJS_toolbar._addEvent(el, "click", function (ev) {
				if (obj.enabled) with (HTMLJS_toolbar) {
					_removeClass(el, "buttonActive");
					_removeClass(el, "buttonHover");
					editor._buttonClicked(txt);
					_stopEvent(client.ie ? window.event : ev);
				}
			});

            if(txt=='bold'){
                var c="<b>Bold and GO AHEAD</b>";
                if(Client.ns4){with(el.page){write(c||"");close()}}
                if(Client.ie&&Client.userAgent.indexOf("Mac")>0){c=c+"\n";}
                el.innerHTML=c||"";
            } else {
                var img = document.createElement("img");
                img.src = editor.imgURL(btn[2]);
                el.appendChild(img);
            }
		} else if (!el) {
			el = createSelect();
		}
		if (el) {
			var _cell = document.createElement("td");
			_row.appendChild(_cell);
			_cell.appendChild(el);
		} else {
			//alert("FIXME: Unknown toolbar item: " + txt);
		}
		return el;
	};
	// init first line
	newLine();

    for (var i in HTMLJS_toolbar.toolbar) {
		var group = HTMLJS_toolbar.toolbar[i];
		for (var j in group) {
			createButton(group[j]);
		}
	}

	var tas = document.getElementsByTagName("div");
	var htmlarea = document.createElement("div");
	htmlarea.className = "htmlarea";
    htmlarea.className = "htmlarea";
	//for (var i = tas.length; i > 0; (new HTMLArea(tas[--i])).generate());
	htmlarea.appendChild(toolbar);
    ///tas[0].className = "textarea";
    //alert(tas[0].innerHTML);
    tas[0].appendChild(htmlarea);
}


HTMLJS_toolbar._removeClass = function(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};

HTMLJS_toolbar._addClass = function(el, className) {
	// remove the class first, if already there
	HTMLJS_toolbar._removeClass(el, className);
	el.className += " " + className;
};

HTMLJS_toolbar._hasClass = function(el, className) {
	if (!(el && el.className)) {
		return false;
	}
	var cls = el.className.split(" ");
	for (var i = cls.length; i > 0;) {
		if (cls[--i] == className) {
			return true;
		}
	}
	return false;
};



// event handling
HTMLJS_toolbar._addEvent = function(el, evname, func) {
	if (HTMLJS_toolbar.client.ie) {
		el.attachEvent("on" + evname, func);
	} else {
		el.addEventListener(evname, func, true);
	}
};

HTMLJS_toolbar._addEvents = function(el, evs, func) {
	for (var i in evs) {
		HTMLJS_toolbar._addEvent(el, evs[i], func);
	}
};

HTMLJS_toolbar._removeEvent = function(el, evname, func) {
	if (HTMLJS_toolbar.client.ie) {
		el.detachEvent("on" + evname, func);
	} else {
		el.removeEventListener(evname, func, true);
	}
};

HTMLJS_toolbar._removeEvents = function(el, evs, func) {
	for (var i in evs) {
		HTMLJS_toolbar._removeEvent(el, evs[i], func);
	}
};

HTMLJS_toolbar._stopEvent = function(ev) {
	if (HTMLJS_toolbar.client.ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
};

// paths

HTMLJS_toolbar.prototype.imgURL = function(file) {
    //interrogate(this.buttonList);
    //alert(this.imgURL + file);
	return this.buttonList.imgURL + file;
};

HTMLJS_toolbar.prototype.popupURL = function(file) {
	return this.buttonList.popupURL + file;
};


HTMLJS_toolbar.prototype._buttonClicked = function(txt) {
    btn = this.buttonList.data[txt];
    var cmd=btn[0];
    switch(cmd.toLowerCase()){
    case 'bold':
            alert("Bold selected");
        break;
    }
    return;
	var editor = this;	// needed in nested functions
	this.focusEditor();
	var btn = this.config.btnList[txt];
	if (!btn) {
		alert("FIXME: Unconfigured button!");
		return false;
	}
	var cmd = btn[0];
	if (typeof cmd == "function") {
		return cmd(this, txt);
	}
	switch (cmd.toLowerCase()) {
	    case "htmlmode":
		this.setMode(this._mode != "textmode" ? "textmode" : "wysiwyg");
		break;
	    case "forecolor":
	    case "backcolor":
		this._popupDialog("select_color.html", function(color) {
			editor._execCommand(cmd, false, "#" + color);
		}, HTMLJS_toolbar._colorToRgb(this._doc.queryCommandValue(btn[0])));
		break;
	    case "createlink":
		this._execCommand(cmd, true);
		break;
	    case "insertimage":
		this._insertImage();
		break;
	    case "inserttable":
		this._insertTable();
		break;
	    case "popupeditor":
		if (HTMLJS_toolbar.client.ie) {
			window.open(this.popupURL("fullscreen.html"), "ha_fullscreen",
				    "toolbar=no,location=no,directories=no,status=yes,menubar=no," +
				    "scrollbars=no,resizable=yes,width=640,height=480");
		} else {
			window.open(this.popupURL("fullscreen.html"), "ha_fullscreen",
				    "toolbar=no,menubar=no,personalbar=no,width=640,height=480," +
				    "scrollbars=no,resizable=yes");
		}
		// pass this object to the newly opened window
		HTMLJS_toolbar._object = this;
		break;
	    case "about":
		this._popupDialog("about.html", null, null);
		break;
	    case "help":
		alert("Help not implemented");
		break;
	    default:
		this._execCommand(btn[0], false, "");
		break;
	}
	this.updateToolbar();
	return false;
};
