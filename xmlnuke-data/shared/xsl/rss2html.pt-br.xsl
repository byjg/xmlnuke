<?xml version="1.0" encoding="utf-8" ?> 
<!--
Original XSLT Template From:
http://www.bigbold.com/snippets/posts/show/1163
-->
<xsl:stylesheet
     version="1.0"
     xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
     <xsl:output method="xml" />

     <xsl:template match="/">
     <html>
	<xsl:if test="rss/channel">
	   <xsl:apply-templates select="rss/channel" />
	</xsl:if>
	<xsl:if test="not(rss/channel)">
	   O documento não é um Feed RSS
	</xsl:if>
     </html>
     </xsl:template>


     <xsl:template match="rss/channel">
          <head>
               <title><xsl:value-of select="title" /></title>
          </head>     
          <body>
		<xsl:apply-templates select="image" />
	        <h3><xsl:value-of select="title"/></h3>
	        <p><xsl:value-of select="description"/></p>
	        <ul>
	            <xsl:apply-templates select="item"/>
	        </ul>
          </body>
     </xsl:template>

     <xsl:template match="image">
	<a href="{link}"><img src="{url}" alt="{title}" border="0" /></a>
     </xsl:template>

     <xsl:template match="item">
        <li>
            <a href="{link}" title="{pubDate}"><xsl:value-of select="title"/></a>
            <p><xsl:value-of select="description"/></p>
        </li>
     </xsl:template>
</xsl:stylesheet>