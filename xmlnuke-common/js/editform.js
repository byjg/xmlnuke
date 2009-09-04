function showHideGeneric( oname, show )
{
	var arr = [ "dt", "dd" ];
	
	for (i=0; i < arr.length; i++) 
	{	
		$(arr[i]).each(function(){
			if ($(this).attr("id") == oname)
			{
				if (show=='auto') show = (this.style.display=='none'); 
				if (show)
					$(this).fadeIn();
	            else
					$(this).fadeOut();
			}
		});
    }
	
	$('#I_' + oname).attr("src", "common/imgs/" + (show?"faqclose.gif":"faqopen.gif"));
}
