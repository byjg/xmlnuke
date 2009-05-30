<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- TABVIEW -->

<xsl:template match="sortablelist">
	<dt><xsl:value-of select="@caption" /></dt>
	<dd>
	<ul><xsl:attribute name="id"><xsl:value-of select="@name" />_list</xsl:attribute>
		<xsl:for-each select="item">
			<li>
				<xsl:attribute name="id"><xsl:value-of select="@key" /></xsl:attribute>
				<xsl:attribute name="class"><xsl:choose>
					  <xsl:when test="@state='highlight'">ui-state-highlight</xsl:when>
					  <xsl:when test="@state='disabled'">ui-state-disabled</xsl:when>
					  <xsl:otherwise>ui-state-default</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
 				<xsl:apply-templates />
			</li>
		</xsl:for-each>
	</ul>
	</dd>
	<input type="hidden" value="">
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
		<xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute>
		<xsl:attribute name="value"><xsl:for-each select="item"><xsl:if test="not(position()=1)">|</xsl:if><xsl:value-of select="@key" /></xsl:for-each></xsl:attribute>
	</input>
</xsl:template>
