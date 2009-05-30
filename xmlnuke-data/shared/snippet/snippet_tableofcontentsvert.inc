<ul>
	<xsl:for-each select="page/blockcenter">
		<li><a><xsl:attribute name="href">#H<xsl:number format="1"/></xsl:attribute><xsl:value-of select="title"/></a></li>
	</xsl:for-each>
</ul>