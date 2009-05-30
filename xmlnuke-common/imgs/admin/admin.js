var defaultStatusBarTitle = "XMLNuke Control Panel";
function setStatusBarTitle(newtitle)
{
	if(!newtitle)
	{
		newtitle = defaultStatusBarTitle;
	}
	window.status = newtitle;
	window.defaultStatus = newtitle;
}
function effectOn(e, msg)
{
	e.className = "item_effect_on";
	setStatusBarTitle(msg);
}
function effectOff(e)
{
	e.className = "item_effect_off";
	setStatusBarTitle();
}
function pro()
{
var pro = '';
	obj = window;
	for( nome in obj)
	{
		pro += "nome: " + nome + " valor: " + obj[nome] + "<br/>";
	}
}
