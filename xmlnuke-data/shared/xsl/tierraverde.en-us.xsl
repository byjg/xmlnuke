<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
            <meta name="author" content="haran" />
            <meta name="generator" content="Windows Notepad" />
            <link rel="stylesheet" type="text/css" href="common/styles/tierraverde.css" title="Tierra Verde stylesheet" />
            <link rel="stylesheet" type="text/css" href="common/styles/tierraverde-color-scheme.css" title="Tierra Verde stylesheet" />
            <title>
               <xsl:value-of select="page/meta/title" />
            </title>
	    <xmlnuke-htmlheader />
         </head>
         <body>
            <div id="top">
            </div>
            <!-- ###### Header ###### -->
            <div id="header">
               <a href="./index.html" class="headerTitle" title="Homepage">Tierra <span>Verde</span></a>
               <div class="menuBar">
                  <a href="./index.html">Contact Us</a>|
        <a href="./index.html">Site Map</a>|
        <a href="./index.html">Help</a></div>
            </div>
            <!-- ###### Side Boxes ###### -->
            <div class="sideBar">
               <div class="sideBarTitle">This Page</div>
               <xsl:for-each select="page/blockcenter">
                  <a>
                     <xsl:attribute name="href">#H<xsl:number format="1" /></xsl:attribute>- <xsl:value-of select="title" /></a>
               </xsl:for-each>
               <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
                  <div class="sideBarTitle">
                     <xsl:value-of select="title" />
                  </div>
                  <xsl:for-each select="page">
                     <a>
                        <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>- <xsl:value-of select="title" /></a>
                  </xsl:for-each>
               </xsl:for-each>
               <xsl:for-each select="page/blockleft">
                  <div class="sideBarTitle">
                     <xsl:value-of select="title" />
                  </div>
                  <span class="sideBarText">
                     <xsl:apply-templates select="body" />
                  </span>
               </xsl:for-each>
            </div>
            <!-- ###### Body Text ###### -->
            <div id="bodyText">
               <xsl:for-each select="page/blockcenter">
                  <div class="boxedLight">
                     <h1>
                        <xsl:attribute name="id">H<xsl:number format="1" /></xsl:attribute>
                        <xsl:value-of select="title" />
                     </h1>
                     <xsl:apply-templates select="body" />
                  </div>
               </xsl:for-each>
            </div>
            <!-- ###### Footer ###### -->
            <div id="footer">
               <div class="footerLHS">
                  <a href="http://validator.w3.org/check/referer" class="footerLHS">
          Valid XHTML 1.0 Strict</a> |
        <a href="http://jigsaw.w3.org/css-validator/check/referer">Valid CSS2</a></div>
               <div>
        Modified: 2004-10-11 |
        Designer: <a href="http://www.oswd.org/email.phtml?user=haran" title="Email author">haran</a></div>
            </div>
            <div class="subFooter">
      Copyright (c) 2003, Your Company<br />
Ported to XMLNuke by JG (João Gilberto Magalhães)<br /><a href="./index.html">Home</a>|
      <a href="./index.html">Site Map</a>|
      <a href="./index.html">Contact Us</a>|
      <a href="./index.html">Disclaimer</a>|
      <a href="./index.html">Privacy Statement</a></div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
