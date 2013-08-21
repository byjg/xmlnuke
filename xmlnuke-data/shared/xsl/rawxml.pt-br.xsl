<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  
  <xsl:output method="xml"/>

  <!-- Generic stylesheet for viewing XML -->
  <!-- This template will always be executed, even if this stylesheet is not run on the document root -->
  <xsl:template match="/">
    <div style="font-family:Courier; font-size:10pt; margin-bottom:2em">
      [ <a href="engine:xmlnuke?xml=[param:xml]&amp;xsl=page">Ver em HTML</a> | 
	<a href="javascript:history.go(-1)">Voltar</a> ] 
      <hr />
      <xsl:apply-templates />
    </div>
  </xsl:template>


  <xsl:template match="*">
     <div style="margin-left:1em; color:gray">
        &#60;<xsl:value-of select="name()"/><xsl:value-of select="string(' ')" /><xsl:apply-templates select="@*"/>/&#62;
     </div>
  </xsl:template>

  <xsl:template match="*[node()]">
     <div style="margin-left:1em">
        <span style="color:gray">&#60;<xsl:value-of select="name()"/><xsl:value-of select="string(' ')" /><xsl:apply-templates select="@*"/>&#62;</span><xsl:apply-templates select="node()"/><span style="color:gray">&#60;/<xsl:value-of select="name()"/>&#62;</span>
     </div>
  </xsl:template>

  <xsl:template match="@*">
     <span style="color:navy">  
	<xsl:if test="name() = 'href'">
		<xsl:value-of select="name()"/>="<span style="color:black"><a><xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute><xsl:value-of select="."/></a></span>"
	</xsl:if>
	<xsl:if test="name() != 'href'">
		<xsl:value-of select="name()"/>="<span style="color:black"><xsl:value-of select="."/></span>"
	</xsl:if>
     </span>
  </xsl:template>

</xsl:stylesheet>
