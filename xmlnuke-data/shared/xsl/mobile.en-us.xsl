<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml"
		omit-xml-declaration="yes"
		doctype-public="-//WAPFORUM//DTD XHTML Mobile 1.0//EN"
		doctype-system="http://www.wapforum.org/DTD/xhtml-mobile10.dtd"
		indent="yes"/>

   <xsl:template match="/">

		<html>
			<head>
				<title><xsl:value-of select="page/meta/title" /> - Mobile</title>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			</head>

			<body>
		
				<img src="common/imgs/logo_xmlnuke_pb.gif" alt="Logo" title="Logo" />

				<xmlnuke-blockleftcss2 />
				<hr />
				<xmlnuke-blockcentercss2 />

			</body>
			
		</html>

   </xsl:template>
   <xmlnuke-mobilebody />
</xsl:stylesheet>