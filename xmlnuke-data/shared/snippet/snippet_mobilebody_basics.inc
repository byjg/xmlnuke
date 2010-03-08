<xsl:param name="xml" />
<xsl:param name="xsl" />
<xsl:param name="site" />
<xsl:param name="lang" />
<xsl:param name="transformdate" />
<xsl:param name="urlbase" />
<xsl:param name="engine" />

<xsl:template match="body">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="code">
  <p>
  <xsl:if test="@information">
	  <strong><xsl:value-of select="@information" /></strong>
  </xsl:if>
  <div style="background: #FAFAFA; border: 1px solid #f2f2f2; border-left: 3px solid #c0c0c0;">
    <pre>
      <xsl:value-of select="text()"/>
    </pre>
  </div>
  </p>
</xsl:template>

<xsl:template match="p">
	<p><xsl:apply-templates/></p>
</xsl:template>

<xsl:template match="text()"><xsl:value-of select="."/></xsl:template>

<xsl:template match="br"><br/></xsl:template>
<xsl:template match="u"><span style="text-decoration: underline"><xsl:apply-templates /></span></xsl:template>
<xsl:template match="i"><em><xsl:apply-templates /></em></xsl:template>
<xsl:template match="b"><strong><xsl:apply-templates /></strong></xsl:template>
<xsl:template match="center"><p style="text-align: center"><xsl:apply-templates/><xsl:value-of select="text()"/></p></xsl:template>
<xsl:template match="a"><a><xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute><xsl:if test="@target"><xsl:attribute name="target"><xsl:value-of select="@target"/></xsl:attribute></xsl:if><xsl:apply-templates /></a></xsl:template>

<xsl:template match="img"><img border="0"><xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute><xsl:if test="@alt"><xsl:attribute name="alt"><xsl:value-of select="@alt"/></xsl:attribute><xsl:attribute name="title"><xsl:value-of select="@alt"/></xsl:attribute></xsl:if><xsl:if test="not(@alt)"><xsl:attribute name="alt">Image</xsl:attribute></xsl:if><xsl:if test="@width"><xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute></xsl:if><xsl:if test="@height"><xsl:attribute name="height"><xsl:value-of select="@height"/></xsl:attribute></xsl:if><xsl:if test="@align"><xsl:attribute name="align"><xsl:value-of select="@align"/></xsl:attribute></xsl:if><xsl:if test="@altimage"><xsl:attribute name="onerror">this.onerror=null; this.src='<xsl:value-of select="@altimage"/>'</xsl:attribute></xsl:if></img></xsl:template>

<xsl:template match="ul">
	<ul><xsl:if test="@name"><xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute></xsl:if>
		<xsl:apply-templates select="li" />
	</ul>
</xsl:template>
<xsl:template match="ol">
	<ol><xsl:if test="@name"><xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute></xsl:if>
		<xsl:apply-templates select="li" />
	</ol>
</xsl:template>

<xsl:template match="li"><li><xsl:apply-templates/></li></xsl:template>
<xsl:template match="script">
<script><xsl:attribute name="language"><xsl:value-of select="@language"/></xsl:attribute><xsl:if test="@src"><xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute></xsl:if>
<xsl:value-of select="text()"/>
</script>
</xsl:template>

<xsl:template match="table"><table><xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute><xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute><xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute><xsl:apply-templates select="tr"/></table></xsl:template>

<xsl:template match="tr"><tr><xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute><xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute><xsl:apply-templates select="td"/></tr></xsl:template>

<xsl:template match="td"><td><xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute><xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute><xsl:attribute name="colspan"><xsl:value-of select="@colspan"/></xsl:attribute><xsl:attribute name="rowspan"><xsl:value-of select="@rowspan"/></xsl:attribute><xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute><xsl:apply-templates /></td></xsl:template>

<xsl:template match="external">
		<p>This content requires External reference.</p>
</xsl:template>

<xsl:template match="flash">
		<p>This content requires JavaScript.</p>
</xsl:template>


<xsl:template match="container">
	<div><xsl:if test="@align"><xsl:attribute name="align"><xsl:value-of select="@align" /></xsl:attribute></xsl:if><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:if test="@style"><xsl:attribute name="style"><xsl:value-of select="@style" /></xsl:attribute></xsl:if><xsl:if test="@id"><xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute></xsl:if><xsl:apply-templates /></div>
</xsl:template>
