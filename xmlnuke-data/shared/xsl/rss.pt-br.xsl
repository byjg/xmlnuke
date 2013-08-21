<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <rss version="2.0">
	      <xsl:for-each select="page/group">
            <channel>
               <title>[param:site] - <xsl:value-of select="title" /></title>
	       <link>http://[param:SERVER_name]/[param:xmlnuke.URLXMLNUKEENGINE]?site=[param:site]</link>
               <description>
                  Canal <xsl:value-of select="title" /> do site [param:site]
               </description>
               <language>pt-br</language>
               <xsl:for-each select="page">
                  <item>
                     <title>
                        <xsl:value-of select="title" />
                     </title>
                     <link>http://[param:SERVER_name][param:SCRIPT_name]?site=[param:site]&amp;xml=<xsl:value-of select="id" /></link>
                     <description>
                        <xsl:value-of select="summary" />
                     </description>
                  </item>
               </xsl:for-each>
            </channel>
         </xsl:for-each>
      </rss>
   </xsl:template>
</xsl:stylesheet>
