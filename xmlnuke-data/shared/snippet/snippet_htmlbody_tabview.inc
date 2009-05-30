<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- TABVIEW -->

<xsl:template match="tabview">

	<div class="tabber">

	     <xsl:for-each select="tabitem">
	     <div class="">
	     	<xsl:attribute name="class">tabbertab<xsl:if test="@default"> tabbertabdefault</xsl:if></xsl:attribute>
	     	<xsl:attribute name="title"><xsl:value-of select="@title" /></xsl:attribute>
		  <h2>Tab <xsl:value-of select="position()" /></h2>
		  <xsl:apply-templates />
	     </div>
	     </xsl:for-each>

	</div>

</xsl:template>
