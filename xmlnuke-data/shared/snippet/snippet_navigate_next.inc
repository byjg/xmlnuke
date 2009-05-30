<xsl:for-each select="page/group/page[id=$xml]">
	<xsl:if test="following-sibling::page/id and ../keyword!='all'">
		<a>
			<xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="following-sibling::page/id" /></xsl:attribute>
			<xsl:choose>
				<xsl:when test="$lang='pt-br'">Próximo</xsl:when>
				<xsl:otherwise>Next</xsl:otherwise>
			</xsl:choose>
		</a>
		<br/>
		<xsl:value-of select="following-sibling::page/title" />
	</xsl:if>
</xsl:for-each>
