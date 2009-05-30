<meta name="keywords">
	<xsl:attribute name="value">
		<xsl:for-each select="page/meta/keyword"><xsl:value-of select="text()"/><xsl:if test="position() != last()">,</xsl:if></xsl:for-each>
	</xsl:attribute>

</meta>
