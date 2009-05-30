<xsl:for-each select="page/blockcenter">
	<h1><a><xsl:attribute name="name">H<xsl:number format="1"/></xsl:attribute></a>
	<xsl:value-of select="title"/></h1>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
