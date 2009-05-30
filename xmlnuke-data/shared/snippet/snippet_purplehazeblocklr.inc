<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
	<div class="sideBox LHS">
	<div><xsl:value-of select="title"/></div>
	<xsl:for-each select="page">
		<a><xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id"/></xsl:attribute><xsl:value-of select="title"/></a>
	</xsl:for-each>
	</div>
</xsl:for-each>

<xsl:for-each select="page/blockleft">
	<div class="sideBox LHS">
	<div><xsl:value-of select="title"/></div>
	<span><xsl:apply-templates select="body"/></span>
	</div>
</xsl:for-each>

<xsl:for-each select="page/blockright">
	<div class="sideBox RHS">
	<div><xsl:value-of select="title"/></div>
	<span><xsl:apply-templates select="body"/></span>
	</div>
</xsl:for-each>

<div id="bodyText">
<xsl:for-each select="page/blockcenter">
	<h1 id="introduction"><xsl:value-of select="title"/></h1>
	<p><xsl:apply-templates select="body"/></p>

	<a class="topOfPage" href="#top" title="Go to top of page">top</a>
</xsl:for-each>
</div>
<p/>
