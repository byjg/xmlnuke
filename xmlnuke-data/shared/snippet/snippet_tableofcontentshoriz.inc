<p></p>
<div class="tableoftextarea">
	<span class="tableofitem">
	<xsl:for-each select="page/blockcenter">
		<a><xsl:attribute name="href">#H<xsl:number format="1"/></xsl:attribute><xsl:value-of select="title"/></a><xsl:if test="position() != last()"> | </xsl:if>
	</xsl:for-each>
	</span>
	<BR/>
</div>
<p></p>

