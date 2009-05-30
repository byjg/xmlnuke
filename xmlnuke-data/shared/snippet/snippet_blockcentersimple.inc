<xsl:for-each select="page/blockcenter">
	<a><xsl:attribute name="name">H<xsl:number format="1"/></xsl:attribute></a>
	<b><xsl:value-of select="title"/></b><br/>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
