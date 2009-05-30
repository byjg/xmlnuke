<xsl:for-each select="page/blockright">
	<h2><xsl:value-of select="title"/></h2>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
