<?xml version="1.0" encoding="utf-8" ?> 
<!-- 
Original XSL Template from
http://www.dotnetjunkies.com/Tutorial/9FB56D07-4052-458C-B247-37C9E4B6D719.dcik
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
               <style media="all" lang="en" type="text/css">
                    .ChannelTitle
                    {
                         font-family:  Verdana;
                         font-size:  11pt;
                         font-weight:  bold;
                         width:  500px;
                         text-align:  center;
                    }
                    .ArticleEntry
                    {
                         border-width:  2px;
                         border-color:  #336699;
                         border-style:  solid;
                         width:  500px;
                    }
                    .ArticleTitle
                    {
                         background-color:  #3366CC;
                         color:  #FFFFFF;
                         font-family:  Verdana;
                         font-size:  9pt;
                         font-weight:  bold;
                         padding-left:  5px;
                         padding-top:  5px;
                         padding-bottom:  5px;
                    }
                    .ArticleHeader
                    {
                         background-color:  #3399FF;
                         color:  #FFFFFF;
                         font-family:  Verdana;
                         font-size:  7pt;
                         padding-left:  5px;
                         padding-top:  2px;
                         padding-bottom:  2px;
                    }
                    .ArticleHeader A:visited
                    {
                         color:  #FFFFFF;
                         text-decoration:  none;
                    }
                    .ArticleHeader A:link
                    {
                         color:  #FFFFFF;
                         text-decoration:  none;
                    }
                    .ArticleHeader A:hover
                    {
                         color:  #FFFF00;
                         text-decoration:  underline;
                    }
                    .ArticleDescription
                    {
                         color:  #000000;
                         font-family:  Verdana;
                         font-size:  9pt;
                         padding-left:  5px;
                         padding-top:  5px;
                         padding-bottom:  5px;
                         padding-right:  5px;
                    }
               </style>
          </head>     
          <body>
               <xsl:apply-templates select="image" />

               <div class="ChannelTitle">
                   <xsl:value-of select="title" />
		   <br/>
		   <small><small><xsl:value-of select="description" /></small></small>
               </div>
               <br />

               <xsl:apply-templates select="item" />
          </body>
     </xsl:template>

     <xsl:template match="image">
	<a href="{link}"><img src="{url}" alt="{title}" border="0" /></a>
     </xsl:template>

     <xsl:template match="item">
          <div class="ArticleEntry">
               <div class="ArticleTitle">
                    <xsl:value-of select="title" />
               </div>
               <div class="ArticleHeader">
                    <a href="{link}">Link</a> - <xsl:value-of select="pubDate" />  - <a href="mailto:{author}">Email The Author</a>
               </div>
               <div class="ArticleDescription">
                    <b>Description:</b> <xsl:value-of select="description" />
               </div>
          </div>
          <br />
     </xsl:template>
</xsl:stylesheet>