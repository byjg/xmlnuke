<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
            <meta name="author" content="haran" />
            <meta name="generator" content="Windows Notepad" />
            <link rel="stylesheet" type="text/css" href="common/styles/gila.css" title="Gila stylesheet" />
            <link rel="stylesheet" type="text/css" href="common/styles/gila-color-scheme.css" title="Gila stylesheet" />
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
               <a href="./index.html" class="headerTitle" title="Homepage">Gila <span>Homepage</span></a>
               <div class="headerLinks">
                  <a href="./index.html">Site Map</a>|
        <a href="./index.html">Feedback</a>|
        <a href="./index.html">Help</a></div>
            </div>
            <div class="menuBar">
               <a href="./index.html">Products</a>|
      <a href="./index.html">Solutions</a>|
      <a href="./index.html">Store</a>|
      <a href="./index.html">Support</a>|
      <a href="./index.html">Contact Us</a>|
      <a href="./index.html">About Us</a></div>
            <!-- ###### Left Sidebar ###### -->
            <div class="leftSideBar">
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
            <!-- ###### Right Sidebar ###### -->
            <div class="rightSideBar">
               <xsl:for-each select="page/blockright">
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
                  <h1>
                     <xsl:attribute name="id">H<xsl:number format="1" /></xsl:attribute>
                     <xsl:value-of select="title" />
                  </h1>
                  <xsl:apply-templates select="body" />
               </xsl:for-each>
            </div>
            <!-- ###### Footer ###### -->
            <div id="footer">
               <div>
                  <a href="./index.html">Tell a Friend</a> |
        <a href="./index.html">Privacy Policy</a> |
        <a href="./index.html">Site Map</a> |
        <a href="./index.html">Feedback</a> |
        <a href="./index.html">Help</a></div>
               <div>
        Copyright (c) 2003, Your Company |
        Modified on 2003-02-03 by
          <a href="http://www.oswd.org/email.phtml?user=haran" title="Email webmaster">haran</a><br />
Ported to XMLNuke by JG (João Gilberto Magalhães)</div>
            </div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
