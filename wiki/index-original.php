<?php

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 'Home' ;
$title = str_replace('-', ' ', $page);

if (file_exists("/opt/xmlnuke.wiki/$page.md") || $page = '_pages')
{
	require_once('Parsedown.php');

	$Parsedown = new Parsedown();

	if ($page != '_pages')
	{
		$md = file_get_contents("/opt/xmlnuke.wiki/$page.md");
		$time = filemtime("/opt/xmlnuke.wiki/$page.md");
		$isoTime = date('c', $time);
		$extTime = date('M jS, Y h:i A', $time);
		$textTime = "<div class='time'>Last modified  <time itemprop='startDate' datetime='$isoTime'>$extTime</time></div>";
	}
	else
	{
		$title = 'All Pages';
		$textTime = '';
		$files = scandir('/opt/xmlnuke.wiki');
		$md = "";
		foreach ($files as $file)
		{
			if (strpos($file, '.md') === false)
				continue;

			$file = str_replace('.md', '', $file);
			$fileTitle = str_replace('-', ' ', $file);

			$md .= "* **[$fileTitle](index.php?page=$file)**\n";
		}
	}

	$md = str_replace('https://github.com/byjg/xmlnuke/wiki/', 'index.php?page=', $md);
	$md = str_replace('/byjg/xmlnuke/wiki/', 'index.php?page=', $md);
	$md = str_replace('https://github.com/byjg/xmlnuke/wiki', 'index.php', $md);
	$md = str_replace('/byjg/xmlnuke/wiki', 'index.php', $md);

	$html = "<p>XMLNuke documentation: ";
	$html .= "<a href='index.php'>Wiki Home</a> | ";
	$html .= "<a href='index.php?page=Article-by-Topics'>Topics</a> | ";
	$html .= "<a href='index.php?page=_pages'>All Pages</a> | ";
	$html .= "<a href='http://xmlnuke.com/'>Xmlnuke Home</a> | ";
	$html .= "<a href='http://github.com/byjg/xmlnuke'>Github</a>";
	$html .= "</p>";
	$html .= "<h1 class='title'>" . $title . $textTime . "</h1>";
	$html .= $Parsedown->text($md); 
}
else
{
	$html = "Not Found";
}

?>




<html>

<head>
<!--link href="http://kevinburke.bitbucket.org/markdowncss/markdown.css" rel="stylesheet"></link-->
<link href="markdown6.css" rel="stylesheet" />

<style>
h1.title {
margin-top: 5px;
margin-bottom: 10px;
border: 1px solid #c7cfd5;
background: #f1f5f9;
margin: 20px 0;
padding: 8px;
text-align: left;
}
div.author {
font-style: italic;
font-size: 10px;
text-align: right;
/*border-top: black 1px dotted;*/
margin-top: 15px;
padding-top: 10px;
}
h1 div.time {
display: block;
font-size: 10px;
margin-top: 10px;
font-weight: normal;
}
</style>

<title>XMLNuke Wiki | <?php echo $title; ?></title>

</head>

<body>

<?php echo $html; ?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-874486-1', 'xmlnuke.com');
  ga('send', 'pageview');

</script>

<!--
<div itemscope itemtype="http://data-vocabulary.org/Person" class="author">
  This wiki @ <a href="http://www.xmlnuke.com" itemprop="url">xmlnuke</a> is maintained by <span itemprop="name">Jo&atilde;o Gilberto Magalh&atilde;es</span> a.k.a. <span itemprop="nickname">JG</span> / <span itemprop="title">Software Architect</span> /  
  <span itemprop="address" itemscope  itemtype="http://data-vocabulary.org/Address">
    <span itemprop="locality">Rio de Janeiro</span>,
    <span itemprop="region">RJ</span>         
  </span>
</div>
-->


</body>

