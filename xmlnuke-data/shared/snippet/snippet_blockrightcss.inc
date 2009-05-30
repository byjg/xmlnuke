<xsl:for-each select="page/blockright">
	<h1><xsl:value-of select="title"/></h1>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
