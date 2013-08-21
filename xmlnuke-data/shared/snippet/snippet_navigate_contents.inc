<ul>
<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword]">
	<li>
		<xsl:value-of select="title" />
		<ol>
			<xsl:for-each select="page">
				<li><a>
					<xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
					<xsl:value-of select="title" />
				</a></li>
			</xsl:for-each>
		</ol>
	</li>
</xsl:for-each>
</ul>
