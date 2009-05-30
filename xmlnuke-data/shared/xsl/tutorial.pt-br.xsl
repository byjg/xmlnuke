<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" />
	<xsl:template match="/">
		<HTML>
			<HEAD>
				<link href="common/styles/portal-light.css" type="text/css" rel="stylesheet" /> 
				<TITLE>
					<xsl:value-of select="page/meta/title" />
				</TITLE>
				<xmlnuke-keywords />
				<xmlnuke-scripts />
				<xmlnuke-htmlheader />
			</HEAD>
			<BODY>
				<xmlnuke-navigate_contents />
				<hr />
				<xmlnuke-navigate_thispage />
				<xmlnuke-blockcenter />
				<hr />
				<table>
				<tr>
				<td><xmlnuke-navigate_previous /></td>
				<td><xmlnuke-navigate_next /></td>
				</tr>
				</table>
			</BODY>
		</HTML>
	</xsl:template>
	<xmlnuke-htmlbody />
</xsl:stylesheet>
