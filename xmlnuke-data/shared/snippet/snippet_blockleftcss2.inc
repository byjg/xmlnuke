<xsl:for-each select="page/blockleft">
	<h2><xsl:value-of select="title"/></h2>
	<xsl:apply-templates select="body"/>
</xsl:for-each>
