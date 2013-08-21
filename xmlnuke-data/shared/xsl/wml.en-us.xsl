<?xml version="1.0"?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" media-type="text/vnd.wap.wml" />
	<xsl:template match="/">
		<wml>

			<card id="menu" title="Menu">
			<xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
				<b><xsl:value-of select="title"/></b><br/>
				<xsl:for-each select="page">
					<a><xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id"/></xsl:attribute><xsl:value-of select="title"/></a><br/>
				</xsl:for-each>
				<br/>
			</xsl:for-each>
			</card>

      
			<xsl:for-each select="page/blockcenter">
				<card><xsl:attribute name="id">c<xsl:number format="1"/></xsl:attribute><xsl:attribute name="id">c<xsl:number format="1"/></xsl:attribute><xsl:value-of select="title" /><xsl:apply-templates select="body" /></card>
			</xsl:for-each>
		</wml>
	</xsl:template>
	<xmlnuke-wmlbody />
</xsl:stylesheet>

