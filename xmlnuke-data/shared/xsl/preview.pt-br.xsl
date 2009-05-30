<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" />
	<xsl:template match="/">
		<HTML>
			<link href="common/styles/portal-light.css" type="text/css" rel="stylesheet" />
			<HEAD>
				<TITLE>
					<xsl:value-of select="page/meta/title" />
				</TITLE>
				<xmlnuke-keywords />
				<xmlnuke-scripts />
				<xmlnuke-htmlheader />
			</HEAD>
			<BODY>
				<font face="verdana" size="4">
					<xsl:value-of select="page/meta/title" />
				</font>
				<br />
				<xsl:value-of select="page/meta/abstract" />
	Em <xsl:value-of select="page/meta/created" /><p></p><xmlnuke-blockcenter /><p></p>
	Links existentes nessa pagina:
	<xmlnuke-links /></BODY>
		</HTML>
	</xsl:template>
	<xmlnuke-htmlbody />
</xsl:stylesheet>
