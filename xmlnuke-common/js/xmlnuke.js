/*
 * Functions for general purpose use in XMLNuke
 * 
 * Joao Gilberto Magalhaes
 */


function fn_addEvent(objStr, eventName, functionPtr)
{
	obj = document.getElementById(objStr);
   	if (document.addEventListener)
       	obj.addEventListener(eventName, functionPtr, true);
   	else if (document.attachEvent)
		obj.attachEvent('on'+eventName, functionPtr);
}

var XMLNUKE_DISABLEBUTTON = true; /* Put False Inside JavaScript do avoid disable button */
var XMLNUKE_WAITLOADING = false; /* Put True Inside JavaScript to show Loading Alert */

function fn_disableButton() 
{
	if (XMLNUKE_DISABLEBUTTON)
	{
		$("input").each(function(){
			if( ($(this).attr("type") == "submit") || ($(this).attr("type") == "button") || ($(this).attr("type") == "reset") )
			{
				$(this).hide();
			}
		});	
	}
	
	fn_waitLoading();
}

function fn_waitLoading()
{
	if (XMLNUKE_WAITLOADING)
	{
		$('#lding_div').fadeIn("slow");
	}	
}


$(document).ready(function (){
	var x = 
		"<div id='lding_div' style='background:#fff; width: 100%; position: absolute; height:37px; top: 0px; left: 0px; border-bottom: 1px solid #233a79; opacity: 0.65; filter:alpha(opacity=65); padding-top: 5px'>" +
		"<center><img src='common/imgs/ajax-loader.gif' border='0' /></center>" +
		"</div>";
	$("body").append(x);
	$('#lding_div').hide();
});
