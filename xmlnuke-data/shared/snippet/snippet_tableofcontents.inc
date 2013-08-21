<table width="100%" cellpadding="0" cellspacing="1"  class="menutitle">
<tr><td>
</td></tr>
</table>
<table width="100%" cellpadding="0" cellspacing="1" class="menubackground">
<tr><td width="100%">
<table cellpadding="3" cellspacing="0" width="100%" class="menutextarea">
	<xsl:for-each select="page/blockcenter">
		<tr><td class="menuitem">
			<a><xsl:attribute name="href">#H<xsl:number format="1"/></xsl:attribute><xsl:value-of select="title"/></a>
		</td></tr>
	</xsl:for-each>
</table>
</td></tr>
</table>
