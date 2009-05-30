<xsl:for-each select="page/blockleft">
	<br/>
	<table cellSpacing="1" cellPadding="1" class="blockleftbackground" border="0">
	<tr><td class="blocklefttitle">
	<xsl:value-of select="title"/>
	</td></tr>
	<tr><td class="blockleftsep"><img src="nothing.gif" width="1" height="1"/></td></tr>
	<tr><td class="blocklefttext">
	<xsl:apply-templates select="body"/>
	</td></tr>
	</table>
</xsl:for-each>
