<xsl:for-each select="page/blockcenter">
	<a><xsl:attribute name="name">H<xsl:number format="1"/></xsl:attribute></a>
	<table cellSpacing="1" cellPadding="1" class="blockbackground" border="0" width="100%">
	<tr><td class="blocktitle">
	<xsl:number format="1"/>. <xsl:value-of select="title"/>
	</td></tr>
	<tr><td class="blocksep"><img src="nothing.gif" width="1" height="1"/></td></tr>
	<tr><td class="blocktext">
	<xsl:apply-templates select="body"/>
	</td></tr>
	</table><br/>
</xsl:for-each>
