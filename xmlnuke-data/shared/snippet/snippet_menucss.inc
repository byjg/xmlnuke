<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
	<h1><xsl:value-of select="title"/></h1>
	<p>
	<xsl:for-each select="page">
		<xmlnuke-_linkmenu /><br/>
	</xsl:for-each>
	</p>
</xsl:for-each>
