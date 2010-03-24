<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- TABVIEW -->

<xsl:template match="sortablelist">
	<xsl:variable name="name"><xsl:value-of select="@name" /></xsl:variable>
	<xsl:variable name="connect"><xsl:value-of select="@connectkey" /></xsl:variable>

	<dt>
		<xsl:if test="@fullsize='true'"><xsl:attribute name="style">width: 2px;</xsl:attribute></xsl:if>
		<xsl:if test="not(@fullsize='true')"><xsl:value-of select="@caption" /></xsl:if>
	</dt>
	<dd>
		<xsl:if test="@fullsize='true'"><xsl:attribute name="style">width: 100%;</xsl:attribute><xsl:value-of select="@caption" /><br/></xsl:if>
		<xsl:for-each select="column">
			<div id="{$name}_{@id}_list" class="{$connect}" style="float: left; width: {floor(96 div parent::node()/@columns)}%">
				<xsl:for-each select="item">
					<div id="{@key}">
						<xsl:attribute name="class"><xsl:choose>
							  <xsl:when test="@state='highlight'">ui-state-highlight</xsl:when>
							  <xsl:when test="@state='disabled'">ui-state-disabled</xsl:when>
							  <xsl:when test="@state='portlet'">portlet</xsl:when>
							  <xsl:otherwise>ui-state-default</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
						<xsl:if test="@state='portlet'">
							<div class="portlet-header"><xsl:value-of select="@title" /></div>
							<div class="portlet-content"><xsl:apply-templates /></div>
						</xsl:if>
						<xsl:if test="not(@state='portlet')">
							<xsl:apply-templates />
						</xsl:if>
					</div>
				</xsl:for-each>
			</div>
		</xsl:for-each>
	</dd>

	<xsl:for-each select="column">
		<input type="hidden" id="{$name}_{@id}" name="{$name}_{@id}">
			<xsl:attribute name="value">
				<xsl:for-each select="item">
					<xsl:if test="not(position()=1)">|</xsl:if><xsl:value-of select="@key" />
				</xsl:for-each>
			</xsl:attribute>
		</input>
	</xsl:for-each>

</xsl:template>
