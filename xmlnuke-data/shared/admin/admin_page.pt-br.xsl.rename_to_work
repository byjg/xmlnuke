<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"/>
	<xsl:template match="/">
	
	
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><xsl:value-of select="page/blockcenter[1]/title" /> - <xsl:value-of select="page/meta/title" /></title>
<link rel="stylesheet" type="text/css" href="common/imgs/admin/admin.css" media="screen" />
<script language="javascript" src="common/imgs/admin/admin.js" ></script>
<xmlnuke-htmlheader />
</head>

<body>

	<div id="header">
	 <h1>Control Panel - <xsl:value-of select="page/meta/title" /></h1>
	 <div id="menu">
	  <ul id="nav">
	   <li><a href="admin:controlpanel">Home</a></li>
	   <xsl:for-each select="page/group">
	   	<xsl:if test="starts-with(id,'CP_')">
		   <li><a><xsl:attribute name="href">admin:controlpanel?group=<xsl:value-of select="substring-after(id, 'CP_')" /></xsl:attribute><xsl:value-of select="title" /></a></li>
		</xsl:if>
	   </xsl:for-each>
	   <li><a href="admin:controlpanel?logout=true">Logout</a></li>
	  </ul>
	 </div>
	</div>
	
	<div id="content">
		<div id="right">
		
			<!-- MENU DO LISTMODULES -->
			<xsl:for-each select="page/group[id=/page/listmodules/@group]/page">
			<div id="item" class="item_effect_off" onmouseout="effectOff(this)"><xsl:attribute name="onmouseover">effectOn(this,"<xsl:value-of select="title"/>");</xsl:attribute>
				<img border="0" align="left" width="1" height="90" src="common/imgs/admins/spacer.gif" />
				<a><xsl:attribute name="href"><xsl:value-of select="id"/></xsl:attribute>
				<img border="0" align="left" width="68" height="57"><xsl:attribute name="src"><xsl:value-of select="icon"/></xsl:attribute></img>
				<span class="item_title"><xsl:value-of select="title"/></span><br/>
				<span class="item_text"><xsl:value-of select="summary"/></span>
				</a><br/><br/>
			</div>
			</xsl:for-each>
			
			<div style="clear:both;"></div><br/>
		
			<xsl:for-each select="page/blockcenter">
				<xsl:if test="position() > 1">
				<h2><xsl:value-of select="title"/></h2>
				<xsl:apply-templates select="body"/>
				</xsl:if>
			</xsl:for-each>
		</div>
	
		<div id="left">
			<div class="box">
				<h2><xsl:value-of select="page/blockcenter[1]/title" /></h2>	
				<p><xsl:value-of select="page/blockcenter[1]/body" /></p>
			</div>
			
			<xsl:if test="page/group[id='__DEFAULT__']/page">
			<xsl:for-each select="page/group[id='__DEFAULT__']">
				<h2>Sub Menu</h2>
				<ul>
				  <xsl:for-each select="page">
				    	<li>- <a><xsl:attribute name="href"><xsl:value-of select="id" /></xsl:attribute>
						      <xsl:value-of select="title" /></a></li>
				  </xsl:for-each>
				</ul>
			</xsl:for-each>
			</xsl:if>
		
		</div>
	</div>

</body>

<xmlnuke-scripts />

</html>
	</xsl:template>
	<xmlnuke-htmlbody/>
</xsl:stylesheet>
