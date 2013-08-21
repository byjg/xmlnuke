<xsl:if test="page/blockright">
	<TD>
	<xsl:for-each select="page/blockright">
		<table cellSpacing="1" cellPadding="1" class="blockrightbackground" border="0">
		<tr><td class="blockrighttitle">
		<xsl:value-of select="title"/>
		</td></tr>
		<tr><td class="blockrightsep"><img src="nothing.gif" width="1" height="1"/></td></tr>
		<tr><td class="blockrighttext">
		<xsl:apply-templates select="body"/>
		</td></tr>
		</table>
	</xsl:for-each>
	</TD>
</xsl:if>
