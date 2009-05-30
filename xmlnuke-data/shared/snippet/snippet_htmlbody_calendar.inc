<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->

<!-- Calendar -->

<xsl:template match="calendar">
		<div id="{@name}"></div>
		<div id="{@name}_dialog" title="{@title}">
			<xsl:for-each select="event">
			<p><b><xsl:value-of select="@day" /></b> - <xsl:value-of select="text()" /></p>
			</xsl:for-each>
		</div>		
</xsl:template>

