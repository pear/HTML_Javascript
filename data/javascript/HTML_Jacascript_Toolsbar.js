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
// $Id: HTML_Jacascript_Toolsbar.js,v 1.3 2003-05-13 14:41:30 pajoye Exp $
function HTML_Javascript_toolbar (name, elements, positions, options) {
    var _toolbar = this;

    this.toolbar_elems = new Object();
    this.name = name;

    if(typeof options!="undefined"){
        if(options["cssClass"]){
            this.cssClass = options["cssClass"];
        }
        if(options["imgURL"]){
            this._imgURL = options["imgURL"];
        }
    } else {
        this.cssClass = "toolbar";
    }

    if( typeof elements=="undefined" ){
        this.element = new HTML_Javascript_toolbar.buttonList();
    } else {
        this.elements = elements;
    }

    if( typeof positions=="undefined" ){
        this.positions = new HTML_Javascript_toolbar.buttonList();
    } else {
        this.positions = positions;
    }
    this._create();
};

HTML_Javascript_toolbar.prototype._imgURL = './images/';

HTML_Javascript_toolbar.prototype._create = function (){

    var _toolbar = this;

    function newLine () {
        var table = document.createElement("table");
        table.border = "0px";
        table.cellSpacing = "0px";
        table.cellPadding = "0px";
        toolbar.appendChild(table);
        // IE runs in trouble without TBODY...
        var _body = document.createElement("tbody");
        table.appendChild(_body);
        _row = document.createElement("tr");
        _body.appendChild(_row);
    }

    // updates the state of a toolbar element
    function setButtonStatus(id, newval) {
        var oldval = this[id];
        var el = this.element;
        if (oldval != newval) {
            switch (id) {
                case "enabled":
                    if (newval) {
                        HTML_Javascript_toolbar._removeClass(el, "buttonDisabled");
                        el.disabled = false;
                    } else {
                        HTML_Javascript_toolbar._addClass(el, "buttonDisabled");
                        el.disabled = true;
                    }
                break;
                case "active":
                    if (newval) {
                        HTML_Javascript_toolbar._addClass(el, "buttonPressed");
                    } else {
                        HTML_Javascript_toolbar._removeClass(el, "buttonPressed");
                    }
                break;
            }
            this[id] = newval;
        }
    };

    function createElement (name) {
        function createText (elem){
            el = document.createElement('div');
            el.className = "button";
            var c=elem['label'];
            if(is_nav4){with(el.page){write(c||"");close()}}
            if(is_ie && is_mac){c=c+"\n";}
            el.innerHTML=c||"";
            el.title = elem['label'];
            return el;
        }

        function createImage (elem){
            el = document.createElement('div');
            el.className = "button";
            var img = document.createElement("img");
            img.src = _toolbar.imgURL(elem['image']);
            el.appendChild(img);
            return el;
        }

        function createSelect (elem){
            var options = null;
            options = elem['options'];
			if (options) {
                el = document.createElement("select");
                for (var i in options) {
                    var op = document.createElement("option");
                    op.appendChild(document.createTextNode(options[i]));
                    op.value = i;
                    el.appendChild(op);
                }
                el.name = elem['name'];
                txt = elem['name'];
                if(false)
				HTML_Javascript_toolbar._addEvent(el, "change", function () {
                    interrogate(_toolbar.toolbar_elems[name]);
                    alert(this.value);
				});
                if(typeof elem['onchange']=="function"){
                    alert(elem['onchange']);
                }
                eval("el.evt_onchange = "+elem['onchange']);
                HTML_Javascript_toolbar._addEvent(el, "change", function () {
                    HTML_Javascript_toolbar.comboChage(el, name);
                }
                    );
            }
            return el;
			var options = null;
			var el = null;
			var cmd = null;
			switch (elem['name']) {
			    case "fontsize":
			    case "fontname":
			    case "formatblock":
				options = editor.config[txt]; // HACK ;)
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
					state: setButtonStatus, // for changing state
                    onclick: this.options["onclick"]
				};
				tb_objects[txt] = obj;
				for (var i in options) {
					var op = document.createElement("option");
					op.appendChild(document.createTextNode(i));
					op.value = options[i];
					el.appendChild(op);
				}
				HTML_Javascript_toolbar._addEvent(el, "change", function () {
					editor._comboSelected(el, txt);

				});
			}
			return el;
        }

        function createRadio (elem){
            alert(elem['type']);
        }

        function createCheckbox (elem){
            alert(elem['type']);
        }

        function createSeparator(){
            el = document.createElement('div');
            el.className = "separator";
        }

        function createSpace(){
            el = document.createElement('div');
            el.className = "space";
        }

        var el = null;
        allowevent = false;
        if(name=='separator'){
            createSeparator();
        } else if ( name=='newline') {
            newLine();
            return false;
        } else if ( name=='space') {
			createSpace();
        } else {
            elem = _toolbar.elements[name];
            type = elem['type'];
            switch(type){
                case "text":
                    allowevent = true;
                    el = createText(elem);
                break;
                case "image":
                    allowevent = true;
                    el = createImage(elem);
                break;
                case "select":
                    el = createSelect(elem);
                break;
                default:
                    allowevent = false;
                break;
            }
        }

        var _cell = document.createElement("td");

		if ( el ) {
            el.name = name;
            var obj = {
                name: name,             // the button name (i.e. 'bold')
                element: el,            // the UI element (DIV)
                enabled: true,          // is it enabled?
                active: false,          // is it pressed?
                text: elem['label'],    // enabled in text mode?
                cmd: elem['callback'],  // the command ID
                state: setButtonStatus, // for changing state
            }
            //alert(this.onclick);
            _toolbar.toolbar_elems[name] = obj;

			var _cell = document.createElement("td");
			_row.appendChild(_cell);
			_cell.appendChild(el);
            if(allowevent){
                if(elem["onclick"]){
                    obj.onclick=elem["onclick"];
                }
                if(elem["onmouseover"]){
                    obj.onmouseover=elem["onmouseover"];
                }
                if(elem["onmouseout"]){
                    obj.onmouseover=elem["onmouseout"];
                }
                if(elem["onmousedown"]){
                    obj.onmouseover=elem["onmousedown"];
                }
                // Let define the events
                HTML_Javascript_toolbar._addEvent(el, "mouseover", function () {
                    if (obj.enabled) {
                        HTML_Javascript_toolbar._addClass(el, "buttonHover");
                        if(obj.onmouseover)
                            eval(obj.onmouseover+"(obj)");
                    }
                });
                HTML_Javascript_toolbar._addEvent(el, "mouseout", function () {
                    if (obj.enabled) with (HTML_Javascript_toolbar) {
                        _removeClass(el, "buttonHover");
                        _removeClass(el, "buttonActive");
                        (obj.active) && _addClass(el, "buttonPressed");
                    }
                });
                HTML_Javascript_toolbar._addEvent(el, "mousedown", function (ev) {
                    if (obj.enabled) with (HTML_Javascript_toolbar) {
                        _addClass(el, "buttonActive");
                        _removeClass(el, "buttonPressed");
                        _stopEvent(is_ie ? window.event : ev);
                    }
                });
                // when clicked, do the following:
                HTML_Javascript_toolbar._addEvent(el, "click", function (ev) {
                    if (obj.enabled) with (HTML_Javascript_toolbar) {
                        _removeClass(el, "buttonActive");
                        _removeClass(el, "buttonHover");
                        if(obj.onclick)
                            eval(obj.onclick+"(obj)");
                        _stopEvent(is_ie ? window.event : ev);
                    }
                });
            }
		} else {
            alert('HTML_Javascript Error: Error with the element: '+elem['name']);
        }
    }

    var toolbar = document.createElement("div");
    toolbar.className = this.cssClass;
    toolbar.unselectable = "1";

    newLine();

    // here we go
    for (var i in this.positions) {
		var name = this.positions[i];
        if(name!="separator" && name!="newline" && name!="space" && typeof this.elements[name]=="undefined"){
            alert("Error "+name+" element not found in elements list.");
        } else {
            createElement(name);
        }
    }

    // Get the element named this.name and append the toolbar to it
    var tas = document.getElementById(this.name);
    tas.appendChild(toolbar);
}

HTML_Javascript_toolbar.prototype.getElement = function (name){
    return this.toolbar_elems[name];
}

HTML_Javascript_toolbar.prototype.imgURL = function(file) {
	return this._imgURL + file;
};

HTML_Javascript_toolbar._removeClass = function(el, className) {
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

HTML_Javascript_toolbar._addClass = function(el, className) {
	// remove the class first, if already there
	HTML_Javascript_toolbar._removeClass(el, className);
	el.className += " " + className;
};

HTML_Javascript_toolbar._hasClass = function(el, className) {
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
HTML_Javascript_toolbar._addEvent = function(el, evname, func) {
	if (is_ie) {
		el.attachEvent("on" + evname, func);
	} else {
		el.addEventListener(evname, func, true);
	}
};

HTML_Javascript_toolbar._addEvents = function(el, evs, func) {
	for (var i in evs) {
		HTML_Javascript_toolbar._addEvent(el, evs[i], func);
	}
};

HTML_Javascript_toolbar._removeEvent = function(el, evname, func) {
	if (is_ie) {
		el.detachEvent("on" + evname, func);
	} else {
		el.removeEventListener(evname, func, true);
	}
};

HTML_Javascript_toolbar._removeEvents = function(el, evs, func) {
	for (var i in evs) {
		HTML_Javascript_toolbar._removeEvent(el, evs[i], func);
	}
};

HTML_Javascript_toolbar.comboChage = function (el, txt){
    el.evt_onchange(el, txt);
}

HTML_Javascript_toolbar._stopEvent = function(ev) {
	if (is_ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
};

