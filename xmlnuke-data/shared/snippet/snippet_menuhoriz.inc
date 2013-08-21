<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
	<table class="menutextareahoriz" width="100%">
	<tr>
		<td class="menutitlehoriz"><xsl:value-of select="title"/></td>
		<td>
		<xsl:for-each select="page">
			<xmlnuke-_linkmenu /><xsl:if test="position() != last()"> | </xsl:if>
		</xsl:for-each>
		</td>
	</tr>
	</table>
</xsl:for-each>
