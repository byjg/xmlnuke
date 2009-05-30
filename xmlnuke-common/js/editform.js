function showHideGeneric( oname, show )
{
	var tagsSearch = Array();
	tagsSearch[0] = 'dt';
	tagsSearch[1] = 'dd';
	for (var count=0;count!=2;count++)
	{
		tags = document.getElementsByTagName(tagsSearch[count]);
		for (var i=0; i!=tags.length; i++)
		{
			if (tags[i].id == oname)
			{
				if (show=='auto') show = (tags[i].style.display=='none');
				tags[i].style.display = (show?"block":"none");
			}
		}
	}
	var imagem = document.getElementById('I_' + oname);
	if (imagem) imagem.src = "common/imgs/" + (show?"faqclose.gif":"faqopen.gif");
}
