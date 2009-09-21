// Function developed by Joao Gilberto Magalhaes
// http://www.XMLNuke.com
// http://www.byjg.com.br

var SEPARADORDECIMAL = '.';
var FORMATODATA = 0;

var MSG_REQUIRED = " is required";
var MSG_INTERVAL = " must be between ";
var MSG_STARTDATE = " Start date ";
var MSG_ENDDATE = " End date ";
var MSG_INVALIDNUMBER = " is not a valid number";
var MSG_INVALIDDATE = " is not a valid date. Accepted format is ";
var MSG_MONTH = "Month ";
var MSG_DAY = "Day ";
var MSG_30DAY = " have only 30 days";
var MSG_29DAY = "February have only 28 days in ";
var MSG_INVALIDEMAIL = " is not a valid email.";
var MSG_ERRORINFORM_TOP = "The following errors were encountered:";
var MSG_ERRORINFORM_BOTTOM = "Data has <b>not</b> been submitted.";
var MSG_MEMO_CHARLEFT1 = "There ";
var MSG_MEMO_CHARLEFT2 = " characters."

// CheckThis
function fn_checkthis(descr,tipo,requerido,obj,de,ate){
	var temp;
	var msg = descr;
	window.status = '';
	if (!obj)
	{
		return "";
	}
	if (obj.length && !(obj.tagName)) // XmlNodeList
	{
		return fn_checkthis(descr,tipo,requerido,obj.item(0),de,ate)
	}

	if ((requerido==1) && (obj.value=='')) {
		return fn_message( msg + MSG_REQUIRED );
		//obj.focus();
	}
	if ((requerido!=1) && (obj.value=='')) {
		return "";
	}
    
	if (tipo==1) {
		obj.value = obj.value.toLowerCase();
		return "";
	}
    
	if (tipo==2) {
		obj.value = obj.value.toUpperCase();
		return "";
	}
    
	if (tipo==3) {
		if (!fn_isValidNumber(obj.value, msg)) {
			//obj.focus();
			return 	fn_message( msg + MSG_INVALIDNUMBER );
		}

		if (((fn_toFloat(obj.value) < fn_toFloat(de)) || (fn_toFloat(obj.value) > fn_toFloat(ate))) && (obj.value!='')) {
			//obj.focus();
			return fn_message( msg + MSG_INTERVAL + de + ' -- ' + ate );
       		}
	}

	if (tipo==4) {
		result = fn_isValidDate(obj.value, descr);
		if ((result != "")) {
			return result;
		}
		if (fn_isValidDate(de, MSG_STARTDATE + descr) && fn_isValidDate(ate, MSG_ENDDATE + descr)) {
			return fn_isValidRangeDate(descr, de, ate, obj.value);
		}
	}

	if (tipo==5) {
		result = fn_isValidDateTime(obj.value, descr);
		if ((result != "")) {
			return result;
		}
		if (fn_isValidDate(de, MSG_STARTDATE + descr) && fn_isValidDate(ate, MSG_ENDDATE + descr)) {
			return fn_isValidRangeDate(descr, de, ate, obj.value);
		}
	}

	if (tipo==9) {
		var comacento='ÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ';
		var semacento='AAAAAEEEEIIIIOOOOOUUUUC';
		var i;
		obj.value = obj.value.toUpperCase();
		i = 0;
		while (i<=comacento.length-1) {
			j = obj.value.indexOf(comacento.charAt(i));
			if (j>-1) {
				obj.value = obj.value.substring(0, j) + semacento.charAt(i) + obj.value.substring(j + 1, obj.value.length);
			} else {
				i++;
			}
		}
	}

	if (tipo==10) {
		emailPat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		if (!emailPat.test(obj.value)) {
			return fn_message( msg + MSG_INVALIDEMAIL );
		}
	}

	return "";
}

// IsValidNumber
function fn_isValidNumber(numStr) {
	var auxSEP = (SEPARADORDECIMAL=="." ? "\\" : "" ) +  SEPARADORDECIMAL;
	var objRegExp  =  RegExp("^-?\\d\\d*(" + auxSEP + "\\d\\d*)?$");
	return objRegExp.test(numStr);
}
   
function fn_toFloat(str) {
	return parseFloat(str.replace(SEPARADORDECIMAL,'.'));
}
    
// IsValidDate
//var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{2}|\d{4})$/;
var datePat;
var dateTimePat;

if (FORMATODATA == 2) {
	datePat = /^(\d{4})(\/|-)(\d{2})(\/|-)(\d{2})$/;
	dateTimePat = /^(\d{4})(\/|-)(\d{2})(\/|-)(\d{2}) (\d{2}):(\d{2})(:\d{2})?$/;
} else {
	datePat = /^(\d{2})(\/|-)(\d{2})(\/|-)(\d{4})$/;
	dateTimePat = /^(\d{2})(\/|-)(\d{2})(\/|-)(\d{4}) (\d{2}):(\d{2})(:\d{2})?$/;
}

function fn_isValidDateTime(dateStr, strDesc) {
	var matchArray = dateStr.match(dateTimePat); 
	if (dateStr == '') {
		return "";
	}
	else if (matchArray == null)
	{
		msg = fn_isValidDate("err", strDesc);
		return msg.replace("</li>",  " hh:mm:ss</li>");
	}
	else
	{
		return "";
	}
}


function fn_isValidDate(dateStr, strDesc) {
	var matchArray = dateStr.match(datePat); 
	if (dateStr == '') {
		return "";
	}

	if (matchArray == null) {
		switch (FORMATODATA)
		{
			case 0:
				return fn_message (strDesc + MSG_INVALIDDATE + ' dd/mm/aaaa');
				break;
			case 1:
				return fn_message (strDesc + MSG_INVALIDDATE + ' mm/dd/aaaa');
				break;
			case 2:
				return fn_message (strDesc + MSG_INVALIDDATE + ' aaaa/mm/dd');
				break;
		}
	}


	switch (FORMATODATA)
	{
		case 0:
			day = matchArray[1];   // parse date into variables
			month = matchArray[3];
			year = matchArray[5];
			break;
		case 1:
			day = matchArray[3];   // parse date into variables
			month = matchArray[1];
			year = matchArray[5];
			break;
		case 2:
			day = matchArray[5];   // parse date into variables
			month = matchArray[3];
			year = matchArray[1];
			break;
	}

	if (month < 1 || month > 12) { // check month range
		return fn_message(MSG_MONTH + strDesc + MSG_INTERVAL + ' 01 -- 12.');
	}
	if (day < 1 || day > 31) {
		return fn_message(MSG_DAY + strDesc + MSG_INTERVAL + ' 01 -- 31.');
	}
	if ((month==4 || month==6 || month==9 || month==11) && day==31) {
		return fn_message (MSG_MONTH + month + ', ' + strDesc + ', ' + MSG_30DAY);
	}
	if (month == 2) { // check for february 29th
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day>29 || (day==29 && !isleap)) {
			return fn_message(MSG_29DAY + year);
		}
	}
   	return "";  // date is valid
}
   
function fn_isValidRangeDate(strDesc, dateStart, dateEnd, dateCmp) {
	var dStart, dEnd, dCmp;
	var matchArray;
	if ((dateStart == '') || (dateEnd == '') || (dateCmp == '')) {
		return "";
	}
	dStart = fn_toDate(dateStart); 
	dEnd = fn_toDate(dateEnd); 
	dCmp = fn_toDate(dateCmp); 

	if ((dStart <= dCmp) && (dCmp <= dEnd)) {
		return "";
	} else {
		return fn_message(strDesc + MSG_INTERVAL + dateStart + ' -- ' + dateEnd);
	};
}
   
function fn_toDate(dateStr) {
	var matchArray = dateStr.match(datePat);
   	switch (FORMATODATA)
	{
		case 0:
			return matchArray[5] + matchArray[3] + matchArray[1];
			break;
		case 1:
			return matchArray[5] + matchArray[1] + matchArray[3];
			break;
		case 2:
			return matchArray[1] + matchArray[3] + matchArray[5];
			break;
	}
}

function fn_message(msg)
{
	return "<li>" + msg + "</li>";
}

function fn_mountmessage(msg)
{
	return "<p><em>" + MSG_ERRORINFORM_TOP + "</em><ul>" + msgerror + "</ul>" + MSG_ERRORINFORM_BOTTOM + "</p>"
}


function fn_countChars(field, maxSize, evt, labelName)
{
	var keycode = 0;
	var iTotal  = 0;
	var iAux    = 0;
	
	iTotal += field.value.length;
	
	if (window.event)
	{
		keycode = window.event.keyCode;
	}
	else if (evt)
	{
		keycode = evt.which;
	}
	
	if (keycode == 8)
		iAux = iTotal - 1;
	else if ((keycode==9) || (keycode==16) || (keycode==17) || (keycode==18) )
		iAux = iTotal;
	else
		iAux = iTotal + 1;
	
	//alert(keycode + " - " + iTotal + " - " + iAux);
	
	if (iTotal > (maxSize-1) && keycode != 8 && keycode != 46)
	{
		return false;
	}
	
	if (iAux >= 0)
		document.getElementById(labelName).innerHTML = maxSize - iAux;
	
	return true;
}
