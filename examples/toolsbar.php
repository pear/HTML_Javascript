<html>
<head>
<title>HTML_Javascript::Toolsbar: Usage</title>
<style type="text/css">
@import url(toolbar.css);

html, body {
  font-family: Verdana,sans-serif;
  background-color: #fea;
  color: #000;
}
a:link, a:visited { color: #00f; }
a:hover { color: #048; }
a:active { color: #f00; }

textarea { background-color: #fff; border: 1px solid 00f; }
</style>
<script type="text/javascript" src="browserSniffer.js"></script>
<script src="pear/html/javascript/toolsbar.js"></script>
<script>
function interrogate(what) {
    var output = '';
    for (var i in what){
        output += i+ "\n";
    }
    alert(output);
}
</script>
<script>
function fontsOnchange(el, txt){
    val = el.value;
    alert("I'm the font change of("+txt+") "+el.name+" and my value is "+val);
}
<?php
define('HTML_Javascript_URL', 'js');
include_once 'HTML/Javascript/Toolsbar.php';

$options  = array(
                'URL'       => 'js2',
                'filepath'  => 'jspath3',
                'cssClass'  => 'toolbar',
                'imgURL'    => './images/',
                );

$data = array(
                'bold'=>array(
                    'type'      => 'text',
                    'callback'  => 'execBold',
                    'label'     => 'Bold'
                ),
                'home'=>array(
                    'type'      => 'image',
                    'link'      => 'test.html',
                    'label'     => 'Homepage',
                    'image'     => 'ed_about.gif'
                ),
                'italic'=>array(
                    'type'      => 'text',
                    'callback'  => 'execItalic',
                    'label'     => 'Italic'
                ),
                'underscore'=>array(
                    'type'      => 'text',
                    'callback'  => 'execUnderscore',
                    'label'     => 'Underscore'
                ),
                'fonts'=>array(
                    'type'      => 'select',
                    'callback'  => 'execFonts',
                    'label'     => 'Fonts',
                    'options'   => array(
                                          "1"=>"1 (8 pt)",
                                          "2"=>"2 (10 pt)",
                                          "3"=>"3 (12 pt)",
                                          "4"=>"4 (14 pt)",
                                          "5"=>"5 (18 pt)",
                                          "6"=>"6 (24 pt)",
                                          "7"=>"7 (36 pt)"
                                    ),
                    'onchange'=>'fontsOnchange'
                )
            );
$positions = array('bold','home','separator','italic','newline','underscore','space','fonts');
$toolbar = new HTML_Javascript_Toolsbar('mytoolbar', $options);
$toolbar->addElements($data);
$toolbar->setPositions($positions);
echo $toolbar->get();

$toolbar = new HTML_Javascript_Toolsbar('mytoolbar2', $options);
$toolbar->addElements($data);
$toolbar->setPositions($positions);
echo $toolbar->get();
?>

var mytoolbar;
var mytoolbar2;

function initToolbar(){
    mytoolbar = new HTML_Javascript_toolbar(mytoolbar_name, mytoolbar_data, mytoolbar_position, mytoolbar_options);
    mytoolbar2 = new HTML_Javascript_toolbar(mytoolbar2_name, mytoolbar2_data, mytoolbar2_position, mytoolbar2_options);
}

function enable(on){
    name = document.forms['debug'].id.value
    if(name){
        el = mytoolbar.getElement(name);
        if(el){
            el.enabled = on;
        }
    }
}

function setActive2(on){
    name = document.forms['debug'].id.value;
    if(name){
        el = mytoolbar.getElement(name);
        el.state("active",on);
    }
}

function getElem(){
    name = document.forms['debug'].id.value
    if(name){
        el = mytoolbar.getElement(name);
        alert(el.name);
        interrogate(el);
    }
}

</script>
</head>
<body onload="initToolbar()">
<div id="mytoolbar" ></div>
<br>
<br>
<br>
<div id="mytoolbar2"></div>
<a href="" onclick="interrogate(mytoolbar);return false;">mytoolbar</a>
<a href="" onclick="interrogate(mytoolbar.getElement('bold'));return false;">get Elem</a>
<a href="" onclick="getElem();return false;">get Elem</a>
<a href="" onclick="enable(false);return false;">Disable...</a>
<a href="" onclick="enable(true);return false;">Enable...</a>
<a href="" onclick="setActive2(true);return false;">Active...</a>
<a href="" onclick="setActive2(false);return false;">Desactive...</a>
<form name="debug">
<input name="id" value="bold">
</form>
<pre>
<?php print_r($toolbar); ?>
<pre>
</body>
</html>