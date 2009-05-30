<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <title>
               <xsl:value-of select="page/meta/title" />
            </title>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta name="author" content="(Your Name)" />
            <meta name="keywords" content="(Keywords Here)" />
            <meta name="description" content="(Brief Description Here)" />
            <meta name="generator" content="(Text Editor Name Here)" />
            <link rel="stylesheet" type="text/css" href="common/styles/oggle.css" />
	    <xmlnuke-htmlheader />
         </head>
         <body>
            <!--### Alert Box ###-->
            <!--### With this, give your users the important headlines. ###-->
            <div class="sidebox">
               <span id="alertfont">I'd like to welcome you all to the new website!</span>
               <div class="readmorefontalert">
                  <a href="index.html#" class="readmorecolor">Read More...</a>
               </div>
            </div>
            <!--### Site Title ###-->
            <div class="title">
      Oggle
    </div>
            <!--### Side Box ###-->
            <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
               <div class="sidebox">
                  <div class="sideboxtitlebg">
                     <span class="titlefont">
                        <xsl:value-of select="title" />
                     </span>
                  </div>
                  <div class="menumargin">
                     <xsl:for-each select="page">
                        <a>
                           <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
- <xsl:value-of select="title" /></a>
                        <br />
                     </xsl:for-each>
                  </div>
               </div>
            </xsl:for-each>
            <xsl:for-each select="page/blockleft | page/blockright">
               <div class="sidebox">
                  <div class="sideboxtitlebg">
                     <span class="titlefont">
                        <xsl:value-of select="title" />
                     </span>
                  </div>
                  <div class="menumargin">
                     <xsl:apply-templates select="body" />
                  </div>
               </div>
            </xsl:for-each>
            <!--### Content Body ###-->
            <xsl:for-each select="page/blockcenter">
               <div class="contentarea">
                  <span class="articletitle">
                     <xsl:value-of select="title" />
                  </span>
                  <xsl:apply-templates select="body" />
                  <!--
                  <span class="date">10/14/03 @ 7:30 PM</span>
                  <span class="readmorecontent">
                     <a href="index.html#" class="readmorecolor">Read More</a> :: <a href="index.html#" class="readmorecolor">Comments(0)</a></span>
-->
                  <br />
               </div>
            </xsl:for-each>
            <div class="footertext">
      Copyright (c) 2003-2004 YourName.com
	 <br />
	 Design by Michael Merritt for <a href="http://www.oswd.org">OSWD</a><br />
	 Ported to XMLNuke by JG (João Gilberto Magalhães)</div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
