<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" />
	<xsl:template match="/">
		<HTML>
			<head>
				<title>
					<xsl:value-of select="page/meta/title" />
				</title>
				<style>
				body { font-size: 11px; font-family: arial, verdana; }
				</style>
				<xmlnuke-keywords />
				<xmlnuke-scripts />
				<xmlnuke-htmlheader />
			</head>
			<body>
				<xmlnuke-blockcentercss />
			</body>
		</HTML>
	</xsl:template>
	<xmlnuke-htmlbody />
</xsl:stylesheet>

