<xsl:for-each select="page/blockleft">
	<h1><xsl:value-of select="title"/></h1>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
