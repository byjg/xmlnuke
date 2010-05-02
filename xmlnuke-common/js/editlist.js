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

function submitEditList(name, trigger)
{
	if ($("#"+trigger.id).attr("action") == "delete")
	{
		if (!confirm(MSG_CONFIRMDELETE))
		{
			return;
		}
	}

	valueid = "";
	$("#editlist." + name).find('tr.selected').each(function() {
		valueid += (valueid != "" ? "," : "") + $(this).attr("value");
	});

	if (valueid == "")
	{
		alert(MSG_NONESELECTED);
		return;
	}

	if ($("#"+trigger.id).attr("url") != "")
	{
		$("#form_" + name).attr("action", $("#"+trigger.id).attr("url"));
	}

	$("#form_" + name).find("input[name='acao']").attr("value", $("#"+trigger.id).attr("action"));
	$("#form_" + name).find("input[name='valueid']").attr("value", valueid);
	$("#form_" + name).submit();
}

function navigate(name, qtd)
{
	$("#form_" + name).find("input[name='acao']").attr("value", 'move');
	$("#form_" + name).find("input[name='curpage']").attr("value", qtd);
	$("#form_" + name).submit();
}

function defineImageCaption(id, text)
{
	obj = document.getElementById("navbtn_" + id);
	if (obj)
	{
		obj.innerHTML = text;
		obj.title = text;
	}
}

function defineCaption(id, text)
{
	imgObj = document.getElementById("lbl_" + id);
	if (imgObj)
	{
		imgObj.innerHTML = text;
	}
	btnObj = document.getElementById("valbtn_" + id);
	if (btnObj)
	{
		btnObj.value = text;
	}
}