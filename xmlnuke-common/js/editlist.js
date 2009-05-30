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

function submitEditList(form, editmode, url, multiple)
{   
	if (multiple=='')
	{
		multiple = 1;
	}
	formlist = document.getElementById(form.name+"_LIST");
	onechecked = ( (editmode=='new') || (editmode=='move') || (multiple==0) );
	justonechecked = (editmode=='check') || (editmode=='edit') || (multiple==1);
	form.valueid.value = "";
	if (formlist.inputvalueid)
	{
		if (formlist.inputvalueid.length)
		{
			sDeli = "";
			iMark = 0;
			for(i=0;(formlist.inputvalueid.length-i)!=0;i++)
			{
				onechecked = onechecked || formlist.inputvalueid[i].checked;
				if (formlist.inputvalueid[i].checked)
				{
					form.valueid.value = form.valueid.value + sDeli + formlist.inputvalueid[i].value;
					sDeli = "_";
					iMark++;
				}
			}
			if (justonechecked && (iMark > 1))
			{
				alert(MSG_SELECTONE);
				return;
			}
		}
		else
		{
			onechecked = onechecked || formlist.inputvalueid.checked;
			form.valueid.value = formlist.inputvalueid.value;
		}
	}
	if (!onechecked)
	{
		alert(MSG_NONESELECTED);
		return;
	}
	else
	{
		form.acao.value=editmode;
		if (editmode == 'move')
		{
			form.curpage.value = url;
		}
		else if (url != "")
		{
			eval('formcustom=this.document.'+url);
			//alert(formcustom.name);
			//alert(formcustom.action);
			formcustom.acao.value = editmode;
			//alert(formcustom.acao.value);
			//alert(form.valueid.value);
			formcustom.valueid.value = form.valueid.value;
			formcustom.submit();
			return;
		}
		
		if(editmode=='delete')
		{
			if (!confirm(MSG_CONFIRMDELETE))
			{
				return;
			} 
		}
	}
	
	eval("submit"+form.name+"()");
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