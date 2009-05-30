<ul>
	<xsl:for-each select="page/blockcenter">
		<xsl:if test="title!=''">
		<li><a>
			<xsl:attribute name="href">#H<xsl:number format="1" /></xsl:attribute>
			<xsl:value-of select="title" />
		</a></li>
		</xsl:if>
	</xsl:for-each>
</ul>