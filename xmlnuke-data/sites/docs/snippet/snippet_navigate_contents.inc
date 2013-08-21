<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword]">
	<b><xsl:value-of select="title" />:</b>
	<ol>
		<xsl:for-each select="page">
			<li><a>
				<xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
				<xsl:value-of select="title" />
			</a></li>
		</xsl:for-each>
	</ol>
</xsl:for-each>
