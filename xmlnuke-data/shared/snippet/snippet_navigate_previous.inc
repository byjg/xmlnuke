<xsl:for-each select="page/group/page[id=$xml]">
	<xsl:if test="preceding-sibling::page/id and ../keyword!='all'">
		<a>
			<xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="preceding-sibling::page[1]/id" /></xsl:attribute>
			<xsl:choose>
				<xsl:when test="$lang='pt-br'">Anterior</xsl:when>
				<xsl:otherwise>Previous</xsl:otherwise>
			</xsl:choose>
		</a>
		<br/>
		<xsl:value-of select="preceding-sibling::page[1]/title" />
	</xsl:if>
</xsl:for-each>
