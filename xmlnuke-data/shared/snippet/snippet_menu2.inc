<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
	<table width="100%" cellpadding="0" cellspacing="1"  class="menutitle">
	<tr><td>
	<xsl:value-of select="title"/>
	</td></tr>
	</table>
	<table width="100%" cellpadding="0" cellspacing="1" class="menubackground">
	<tr><td width="100%">
	<table cellpadding="3" cellspacing="0" width="100%" class="menutextarea">
		<tr><td>
			<xsl:for-each select="page">
				<b>&#183;</b>&#32;<xmlnuke-_linkmenu /><br/>
			</xsl:for-each>
		</td></tr>
	</table>
	</td></tr>
	</table>
</xsl:for-each>
