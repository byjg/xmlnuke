<xsl:for-each select="page/script[@src]">
<script language="{@language}" src="{@src}" />
</xsl:for-each>

<xsl:if test="page/script[text()]">
	<script type="text/javascript">
	<xsl:for-each select="page/script[text()]">

		<xsl:value-of select="text()"/>
		
	</xsl:for-each>
	</script>
</xsl:if>