<xsl:param name="xml" />
<xsl:param name="xsl" />
<xsl:param name="site" />
<xsl:param name="lang" />
<xsl:param name="transformdate" />

<xsl:template match="body">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="code"><xsl:value-of select="text()"/></xsl:template>
<xsl:template match="p">
	<p><xsl:apply-templates/></p>
</xsl:template>

<xsl:template match="text()"><xsl:value-of select="."/></xsl:template>

<xsl:template match="br"><br/></xsl:template>
<xsl:template match="u"><u><xsl:apply-templates /></u></xsl:template>
<xsl:template match="i"><EM><xsl:apply-templates /></EM></xsl:template>
<xsl:template match="b"><b><xsl:apply-templates /></b></xsl:template>
<xsl:template match="center"><xsl:apply-templates/><xsl:value-of select="text()"/></xsl:template>
<xsl:template match="a"><a><xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute><xsl:apply-templates /></a></xsl:template>

<xsl:template match="img"><img><xsl:attribute name="src"><xsl:value-of select="@src"/>.wbmp</xsl:attribute><xsl:attribute name="alt"><xsl:value-of select="@alt"/></xsl:attribute><xsl:if test="@width"><xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute></xsl:if><xsl:if test="@height"><xsl:attribute name="height"><xsl:value-of select="@height"/></xsl:attribute></xsl:if><xsl:if test="@align"><xsl:attribute name="ALIGN"><xsl:value-of select="@align"/></xsl:attribute></xsl:if></img></xsl:template>

<xsl:template match="ul">
	<br/><xsl:apply-templates select="li" />
</xsl:template>
<xsl:template match="ol">
	<br/><xsl:apply-templates select="li" />
</xsl:template>

<xsl:template match="li">- <xsl:apply-templates/><br/></xsl:template>


<xsl:template match="editform">
	<fieldset><xsl:if test="@title"><xsl:attribute name="title"><xsl:value-of select="@title"/></xsl:attribute></xsl:if>
	<xsl:apply-templates />
	</fieldset>
</xsl:template>

<xsl:template match="hidden"><input type="hidden"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute></input></xsl:template>

<xsl:template match="caption"><u><b><xsl:value-of select="text()"/></b></u><br/></xsl:template>

<xsl:template match="label"><b><xsl:value-of select="@caption"/>:</b> <xsl:value-of select="@value"/><br/></xsl:template>

<xsl:template match="textbox"><b><xsl:value-of select="@caption"/>:</b> <input type="text"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute><xsl:attribute name="size"><xsl:value-of select="@size"/></xsl:attribute></input><br/></xsl:template>

<xsl:template match="password"><b><xsl:value-of select="@caption"/>:</b> <input type="password"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="size"><xsl:value-of select="@size"/></xsl:attribute></input><br/></xsl:template>

<xsl:template match="memo"><b><xsl:value-of select="@caption"/>:</b> <input type="text"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="text()"/></xsl:attribute></input><br/></xsl:template>

<xsl:template match="select"><b><xsl:value-of select="@caption"/>:</b> <select><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:apply-templates select="option" /></select><br/></xsl:template>

<xsl:template match="option"><option><xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute><xsl:apply-templates /></option></xsl:template>
