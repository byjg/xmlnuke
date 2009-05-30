<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
	<h2><xsl:value-of select="title"/></h2>
	<ul>
	<xsl:for-each select="page">
		<li><xmlnuke-_linkmenu /></li>
	</xsl:for-each>
	</ul>
</xsl:for-each>
