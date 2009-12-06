<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- FAQ -->

<xsl:template match="faq">

  <xsl:if test="@title">
	<div style="text-weight: bold; font-size:18px"><xsl:value-of select="@title" /></div>
  </xsl:if>

  <div id="faqaccordion" class="faqaccordion" style="margin-top: 2em;">
      <xsl:for-each select="item">
      <div>
    	 <h5><a href="#"><xsl:value-of select="@question" /></a></h5>
		 <div><xsl:apply-templates /></div>
	  </div>
      </xsl:for-each>
  </div><br />
</xsl:template>
