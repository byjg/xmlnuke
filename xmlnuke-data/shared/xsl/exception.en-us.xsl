<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

   <xsl:output method="xml"
                omit-xml-declaration="yes"
                doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
                indent="no"/>

   <xsl:template match="/">

	<html>
	<xmlnuke-blockcentercss />
	</html>

   </xsl:template>
   <xmlnuke-htmlbody/>
</xsl:stylesheet>