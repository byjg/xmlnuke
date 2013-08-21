<xsl:if test="page/blockcenter/body/p/a">
	<UL>
		<xsl:for-each select="page/blockcenter/body/p/a">
			<LI><xsl:value-of select="text()"/> - <xsl:value-of select="@href"/></LI>
		</xsl:for-each>
	</UL>
</xsl:if>
