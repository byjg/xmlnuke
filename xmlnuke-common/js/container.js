function loadUrl(containerId, url)
{
	var container = document.getElementById(containerId);
	if (container)
	{
		container.style.display = "block";
		container.innerHTML = '<'+'object id="objectfoo" name="objectfoo" type="text/html" data="'+url+'"><\/object>';
	}
	else
	{
		alert("Container '" + containerId + "' does not exists");
	}
}
