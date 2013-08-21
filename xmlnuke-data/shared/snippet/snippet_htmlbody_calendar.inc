<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->

<!-- Calendar -->

<xsl:template match="calendar">
		<div id="{@name}"></div>
		<div id="{@name}_dialog" title="{@title}">
			<xsl:for-each select="event">
			<p><b><xsl:value-of select="@day" /></b> - <xsl:value-of select="text()" /></p>
			</xsl:for-each>
		</div>		
		<script language="javascript">
			calDays_<xsl:value-of select="@name" /> = {
				<xsl:for-each select="event">day_<xsl:value-of select="@day" />: 'bgcaltype<xsl:value-of select="@type" />'<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>
			};
		</script>
</xsl:template>

