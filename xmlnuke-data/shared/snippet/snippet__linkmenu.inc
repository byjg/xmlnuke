<a>
      <xsl:choose>
          <xsl:when test="starts-with(id,'module:')">
            <xsl:attribute name="href">
               <xsl:value-of select="id" />
            </xsl:attribute>
         </xsl:when>
         <xsl:when test="starts-with(id,'url://')">
            <xsl:attribute name="href">
               <xsl:value-of select="substring-after(id, 'url://')" />
            </xsl:attribute>
         </xsl:when>
         <xsl:otherwise>
            <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="title"/>
</a>

