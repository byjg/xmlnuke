// Function developed by Joao Gilberto Magalhaes
// http://www.XMLNuke.com
// http://www.byjg.com.br

MSG_NONESELECTED = "You must select one line";
MSG_CONFIRMDELETE = "Do you want delete the selected record(s)?";
MSG_SELECTONE = "You must select only one line";

BTN_NEW = "New";
BTN_VIEW = "View";
BTN_DELETE = "Delete";
BTN_EDIT = "Edit";
BTN_FIRST = "First Page";
BTN_PREVIOUS = "Previous Page";
BTN_NEXT = "Next Page";
BTN_LAST = "Last Page";

function submitEditList(name, li, row)
{
	if ($("#"+li.id).attr("url") != "")
	{
		$("#form_" + name).attr("action", $("#"+li.id).attr("url"));
	}
	$("#form_" + name).find("input[name='acao']").attr("value", $("#"+li.id).attr("action"));
	$("#form_" + name).find("input[name='valueid']").attr("value", $("#"+row.id).attr("value"));
	$("#form_" + name).submit();
}

function defineImageCaption(idImg, text)
{
	imgObj = document.getElementById(idImg);
	if (imgObj)
	{
		imgObj.alt = text;
		imgObj.title = text;
	}
}

function defineCaption(id, text)
{
	obj = document.getElementById(id);
	if (obj)
	{
		obj.innerHTML = text;
	}
}