<xsl:for-each select="page/blockcenter">
	<h2><a><xsl:attribute name="name">H<xsl:number format="1"/></xsl:attribute></a>
	<xsl:value-of select="title"/></h2>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
