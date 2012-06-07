// Function developed by Joao Gilberto Magalhaes
// http://www.XMLNuke.com
// http://www.byjg.com.br

MSG_NONESELECTED = "You need to select at least one line to execute this command";
MSG_CONFIRMDELETE = "Do you want delete the selected record(s)?";
MSG_SELECTONE = "This command accept only one line selected.";

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
		valueid += (valueid != "" ? "," : "") + $(this).attr("valueid");
	});
	
	if ( (valueid == "") && ($("#"+trigger.id).attr("require") != "0"))
	{
		alert(MSG_NONESELECTED);
		return;
	}
	else if (valueid.match(",") && ($("#"+trigger.id).attr("require") != "2") )
	{
		alert(MSG_SELECTONE);
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

function initializeEditList(name, multiple)
{
	// Enable Context Menu
	$(document).ready(function() {
		$('#editlist.' + name).find('tr.odd, tr.even').contextMenu(name + 'Menu', {
			defaultAction: function(source, row)
			{
				submitEditList(name, source);
			},
		    onShowMenu: function(e, menu)
			{
				if ($(e.target).attr('id') == 'edit_caption') {
					menu.find("li[require!='0']").remove();
				}
				return menu;
			}
		});
	} );

	// Enable actions on buttons
	$('#editlist.' + name).find('th').find('input').click(function() {
		submitEditList(name, this);
	});

	// Enable line selection
	$('#editlist.' + name).find('tr.even, tr.odd').mousedown(function(event) {
		if (event.which != 3) // Ignoring right click.
		{
			attrClass = ($(this).attr("class"));
			if (!multiple)
			{
				$(this).parent().find("tr").removeClass("selected");
			}
			if (!attrClass.match("selected"))
			{
				$(this).addClass("selected");
			}
			else
			{
				$(this).removeClass("selected");
			}
		}
	});

	// Define internationalized button captions
	defineCaption(name + "_new", BTN_NEW);
	defineCaption(name + "_view", BTN_VIEW);
	defineCaption(name + "_edit", BTN_EDIT);
	defineCaption(name + "_delete", BTN_DELETE);
	defineImageCaption(name + "_first", BTN_FIRST);
	defineImageCaption(name + "_previous", BTN_PREVIOUS);
	defineImageCaption(name + "_next", BTN_NEXT);
	defineImageCaption(name + "_last", BTN_LAST);

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