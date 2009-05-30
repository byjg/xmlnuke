<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"/>
	<xsl:template match="/">
		<HTML>
		<head>
		<title>'The Maker' by Caio Begotti (caio1982)</title>
		<style type="text/css">
			body          {font-family: Helvetica, Arial, Verdana; font-size: 12px; background-color: #FFFFFF; margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px}
			.logo         {font-family: Helvetica, Arial, Verdana; font-size: 50px; font-weight: bold; color: #426242}
			.form1        {font-size: 10px; color: #FFFFFF; border: 1px solid; font-family: Helvetica, Arial, Verdana; background-color: #537B53; border-color: #FFFFFF solid}
			.form2        {font-size: 9px; color: #FFFFFF; border: 1px solid; font-family: Helvetica, Arial, Verdana; background-color: #537B53; border-color: #FFFFFF solid}
			.banner       {font-family: Helvetica, Arial, Verdana; font-size: 10px; color: #5E8A5E; border: #5E8A5E; border-style: dashed; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px}
			a.top:visited {font-family: Helvetica, Arial, Verdana; font-size: 12px; color: #4B704B; text-decoration: none}
			a.top:link    {font-family: Helvetica, Arial, Verdana; font-size: 12px; color: #4B704B; text-decoration: none}
			a.top:hover   {font-family: Helvetica, Arial, Verdana; font-size: 12px; color: #537B53; text-decoration: none}
			a.top:active  {font-family: Helvetica, Arial, Verdana; font-size: 12px; color: #537B53; text-decoration: none}
			.text         {font-family: Helvetica, Arial, Verdana; font-size: 12px; color: #000000}

			ul                         {padding: 0; margin: 0}
			ul li                      {list-style-type: none; position: relative}
			ul ul                      {display: none}
			ul li:hover > ul           {display: block; position: absolute}
			ul ul                      {width: 118px}
			li a                       {display: block}
			li.sub > a                 {font-weight: bold}
			.rtnv > ul                 {font-size: 16px}
			.rtnv ul li                {padding: 2 0 2 5px; line-height: 15px}
			.rtnv li > a               {color: #FFFFFF; background-color: transparent; padding: 1px}
			.rtnv li:hover             {background-color: #639263}
			.rtnv li.sub:hover         {margin-left: -118px; background: #426242}
			.rtnv li.sub:hover > a     {color: #FFFFFF}
			.rtnv li.sub:hover > ul    {top: 21px; left: 0px; background: #537B53}
		</style>
		<xmlnuke-htmlheader />
		</head>

		<body>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr bgcolor="#426242"> 
			<td> 
			<form method="POST" action="module:search" >
				<div align="right"><a href="index.html" class="top">oswd.org</a>&#160;&#160;<a href="index.html" class="top">freshmeat.net</a>&#160;&#160;<a href="index.html" class="top">google.com.br</a>&#160;&#160;<a href="index.html" class="top">sf.net</a>&#160; 
				<a href="index.html" class="top">mozilla.org</a> &#160; 
				<input class="form1" type="name" name="txtSearch" width="20" size="29" length="20" value=" search foobar..."/>
				<input class="form2" type="submit" value=" :-) " name="submit"/>
				<input type="hidden" name="action" value="search" />
				</div>
			</form>
			</td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="10">
		<tr> 
			<td width="35%" bgcolor="#537B53" valign="top"> 
			<div class="logo">The Maker</div>
			</td>
			<td width="65%" bgcolor="#537B53" valign="middle"> 
			<table class="banner" width="470" border="0" cellspacing="0" cellpadding="1" height="70" align="right">
				<tr>
					<td>
					<div align="center">put your banner here put your banner here put 
					your banner here put your banner here </div>
				</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="10">
		<tr> 
			<td width="80%" valign="top"> 
			<table width="88%" border="0" cellspacing="0" cellpadding="20">
				<tr> 
				<td>

					<xsl:for-each select="page/blockcenter">
						<b><font size="4"><xsl:value-of select="title"/></font></b><br/>
						<xsl:apply-templates select="body"/>
						<br/><br/>
					</xsl:for-each>

				</td>
				</tr>
			</table>

			<p><br/>
				<span class="white">
				Author: Caio Begotti &#60;entercaio@uol.com.br&#62;<br/>
				Ported to XMLNUke: Joao Gilberto Magalhaes&#60;joao@xmlnuke.com&#62;<br/>
				Join #oswd at irc.freenode.net</span></p>

			</td>
			<td width="20%" bgcolor="#639263" valign="top"><span class="rtnv"> 
			<xmlnuke-menu/>
			</span>
			</td>
		</tr>
		</table>

		</body>
		</HTML>
	</xsl:template>
	<xmlnuke-htmlbody/>
</xsl:stylesheet>
