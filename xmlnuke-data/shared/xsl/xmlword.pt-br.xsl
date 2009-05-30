<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

   <xsl:output method="xml"/>

   <xsl:template match="/">


<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="Generator" content="Microsoft Word 10 (filtered)"/>

<title>Teste</title>

<style>
<!--

/* Style Definitions */
p.MsoNormal, li.MsoNormal, div.MsoNormal
	{margin:0in;
	margin-bottom:.0001pt;
	font-size:12.0pt;
	font-family:"Times New Roman";}

h1
	{margin-top:12.0pt;
	margin-right:0in;
	margin-bottom:3.0pt;
	margin-left:0in;
	page-break-after:avoid;
	font-size:16.0pt;
	font-family:Arial;}

h2
	{margin-top:12.0pt;
	margin-right:0in;
	margin-bottom:3.0pt;
	margin-left:0in;
	page-break-after:avoid;
	font-size:14.0pt;
	font-family:Arial;
	font-style:italic;}

h3
	{margin-top:12.0pt;
	margin-right:0in;
	margin-bottom:3.0pt;
	margin-left:0in;
	page-break-after:avoid;
	font-size:13.0pt;
	font-family:Arial;}

p.MsoMessageHeader, li.MsoMessageHeader, div.MsoMessageHeader
	{margin-top:0in;
	margin-right:0in;
	margin-bottom:0in;
	margin-left:56.7pt;
	margin-bottom:.0001pt;
	text-indent:-56.7pt;
	background:#CCCCCC;
	font-size:12.0pt;
	font-family:Arial;}
code
	{font-family:"Courier New"; border:solid windowtext 1.0pt;padding:1.0pt 1.0pt 1.0pt 1.0pt}

@page Section1
	{size:8.5in 11.0in;
	margin:1.0in 1.25in 1.0in 1.25in;}

div.Section1
	{page:Section1;}
-->
</style>


</head>



<body><xsl:attribute name="lang"><xsl:value-of select="$lang" /></xsl:attribute>

<div class="Section1">

	<h3>Document Info:</h3>	
	<p class="MsoNormal"><b>Title: </b><xsl:value-of select="page/meta/title" /></p>
	<p class="MsoNormal"><b>Abstract: </b><xsl:value-of select="page/meta/abstract" /></p>
	<p class="MsoNormal"><b>Last modified: </b><xsl:value-of select="page/meta/modified" /></p>

	<xsl:for-each select="page/blockcenter">
	<h1><xsl:value-of select="title"/></h1>
	<xsl:apply-templates select="body"/>
	</xsl:for-each>

</div>


</body>

</html>

</xsl:template>

<xsl:template match="body"><xsl:apply-templates/></xsl:template>
<xsl:template match="text()"><xsl:value-of select="."/></xsl:template>
<xsl:template match="br"><br/></xsl:template>
<xsl:template match="u"><u><xsl:apply-templates/></u></xsl:template>
<xsl:template match="i"><em><xsl:apply-templates/></em></xsl:template>
<xsl:template match="b"><b><xsl:apply-templates/></b></xsl:template>
<xsl:template match="center"><xsl:apply-templates/></xsl:template>
<xsl:template match="p"><p class="MsoNormal"><xsl:apply-templates/></p></xsl:template>
<xsl:template match="img"><b><i>[image: <xsl:value-of select="@src"/>]</i></b></xsl:template>
<xsl:template match="ul">
	<ul style="margin-top:0in" type="disc">
		<xsl:apply-templates select="li"/>
	</ul>
</xsl:template>
<xsl:template match="ol">
	<ol style="margin-top:0in" type="disc">
		<xsl:apply-templates select="li"/>
	</ol>
</xsl:template>
<xsl:template match="li"><li><xsl:apply-templates/></li></xsl:template>
<xsl:template match="code">
	<div style="border:solid windowtext 1.0pt;padding:1.0pt 1.0pt 1.0pt 1.0pt;  background:#CCCCCC">
		<b><u><xsl:value-of select="@information" /></u></b>
		<p class="MsoMessageHeader"><code><pre><span style="font-size:10.0pt"><xsl:value-of select="text()"/></span></pre></code></p>
	</div>
</xsl:template>



</xsl:stylesheet>
