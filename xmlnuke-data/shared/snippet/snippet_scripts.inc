<xsl:for-each select="page/script">
<script><xsl:attribute name="language"><xsl:value-of select="@language"/></xsl:attribute><xsl:if test="@src"><xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute></xsl:if>
<xsl:value-of select="text()"/>
</script>
</xsl:for-each>
