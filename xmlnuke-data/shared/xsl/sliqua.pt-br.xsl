<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"/>
	<xsl:template match="/">
		<HTML>
		<head>
		<title>Sliqua</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<link rel="stylesheet" href="common/styles/sliqua.css" type="text/css" />
		<xmlnuke-htmlheader />
		</head>

		<body>
		<br />
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr> 
			<td colspan="3" class="pos1" height="55" valign="middle"> 
			<div class="topbox"><h2>Sliqua. The snow meets the sea.</h2></div>
			</td>
		</tr>
		<tr> 
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="topnav">
				<tr>
  					<td align="left" class="head">
		  				&#160;// <a href="#" title="back to the homepage">home</a> 
           					/ <a href="#" title="you are here">relative url</a>
					</td>
    				<td align="right"> 
						<a href="#" class="topmenu" title="aqua in english">&#160;::water::&#160;</a><a href="#" class="topmenu" title="aqua en francais">&#160;::l'eau::&#160;</a><a href="#" class="topmenu" title="aqua auf deutsch">&#160;::wasser::&#160;</a><a href="#" class="topmenu" title="aqua in italiano">&#160;::acqua::&#160;</a><a href="#" class="topmenu" title="aqua en espanol">&#160;::agua::&#160;</a>
					</td>
				</tr>
			</table>
		</td>
		</tr>
		<tr> 
			<td> 
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr> 
				<td valign="top" width="150"> <br />
		  			<div>
		  			
					<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
          				<div class="headbox"><xsl:value-of select="title"/></div>
						<xsl:for-each select="page">
							<a class="leftmenu"><xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id"/></xsl:attribute><xsl:value-of select="title"/></a>
						</xsl:for-each>
						<br />
					</xsl:for-each>
					  
					<div class="dynabox">
					<xsl:for-each select="page/blockleft">
          				<div class="headbox"><xsl:value-of select="title"/></div>
						<div class="dynacontent"><xsl:apply-templates select="body"/></div>
						<br />
					</xsl:for-each>
					</div>

					<br />
					<div class="dynacontent">
					Questions or Comments? Advice for me or from me? Please <a href="http://www.oswd.org/email.phtml?user=Phlash" class="dash" title="email the author">email me.</a>
					</div>	
		              
					</div>
				</td>
				<td valign="top"> <br />
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr> 
						<td width="10"></td>
						<td valign="top" class="mainbox"> 
					
						<xsl:for-each select="page/blockcenter">
          					<h3><xsl:value-of select="title"/></h3>
							<p><xsl:apply-templates select="body"/></p>
							<p>&#160;</p>
						</xsl:for-each>
					

						</td>
						<td width="145" valign="top" class="dynabox">Today is 6.23.02
						<br />
						<br />
						
						<xsl:for-each select="page/blockright">
	          				<div class="headbox">&#160;<xsl:value-of select="title"/></div>
							<div class="dynacontent"><xsl:apply-templates select="body"/></div>
						</xsl:for-each>

		
						</td>
					</tr>
					</table>
					<br />
				</td>
				</tr>
			</table>
			</td>
		</tr>	

		<tr class="pos1"> 
			<td height="20" colspan="2" class="head" align="right">shareright  2002 Phlash &#160; <br/> Ported to XMLNuke byJG&#160; </td>
		</tr>
		</table>
		</body>
		</HTML>
	</xsl:template>
	<xmlnuke-htmlbody/>
</xsl:stylesheet>
