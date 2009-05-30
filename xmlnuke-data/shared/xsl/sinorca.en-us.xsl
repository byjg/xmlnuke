<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
            <meta name="author" content="haran" />
            <meta name="generator" content="Windows Notepad" />
            <link rel="stylesheet" type="text/css" href="common/styles/sinorca-grey.css" title="Grey boxes stylesheet" />
            <link rel="stylesheet alternative" type="text/css" href="common/styles/sinorca-white.css" title="White boxes stylesheet" />
            <link rel="stylesheet" type="text/css" href="common/styles/sinorca-color-scheme.css" />
            <title>
               <xsl:value-of select="page/meta/title" />
            </title>
	    <xmlnuke-htmlheader />
         </head>
         <body>
            <div id="top">
            </div>
            <!-- ###### Header ###### -->
            <div id="upperMenuBar">
               <div class="LHS">
                  <a href="./index.html">Online shop</a>|
        <a href="./index.html">Registration</a></div>
               <div class="RHS">
                  <a href="./index.html">Press Center</a>|
        <a href="./index.html">Partners</a>|
        <a href="./index.html">Company</a>|
        <a href="./index.html">Contacts</a>|
        <a href="./index.html">Worldwide</a></div>
            </div>
            <div id="header">Sinorca Homepage</div>
            <form id="headerSearch" action="module:Search?action=search" method="post">
               <div>
                  <input type="text" class="text" name="txtSearch" value="Search..." />
                  <!-- ###### Dynamic text box version:
        <input class="text" type="text" value="Search..."
               onFocus="if (this.value == 'Search...') this.value=''; else this.select();"
               onBlur="if (this.value == '') this.value='Search...';" />
             ###### -->
                  <input type="submit" class="submit" value="GO" />
               </div>
            </form>
            <div id="lowerMenuBar">
               <a href="./index.html" class="highlight">Home</a>|
      <a href="./index.html">Products</a>|
      <a href="./index.html">Download</a>|
      <a href="./index.html">Purchase</a>|
      <a href="./index.html">Support</a></div>
            <!-- ###### Side Boxes ###### -->
            <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
               <div class="sideMenuBox">
                  <div>
                     <xsl:value-of select="title" />
                  </div>
                  <xsl:for-each select="page">
                     <a>
                        <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>- <xsl:value-of select="title" /></a>
                  </xsl:for-each>
               </div>
            </xsl:for-each>
            <div class="sideMenuBox lighterBG">
               <div>Navegue nessa página</div>
               <xsl:for-each select="page/blockcenter">
                  <a>
                     <xsl:attribute name="href">#H<xsl:number format="1" /></xsl:attribute>- <xsl:value-of select="title" /></a>
               </xsl:for-each>
            </div>
            <xsl:for-each select="page/blockleft">
               <div class="sideTextBox">
                  <div>
                     <xsl:value-of select="title" />
                  </div>
                  <span>
                     <xsl:apply-templates select="body" />
                  </span>
               </div>
            </xsl:for-each>
            <!-- ###### Body Text ###### -->
            <div id="bodyText">
               <xsl:for-each select="page/blockcenter">
                  <xsl:if test="position() != 1">
                     <a class="topOfPage" href="#top" title="Top Of Page">^ topo</a>
                  </xsl:if>
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
                  <span class="footerLHS">
          E-mail: <a href="./index.html">webmaster@your.company.com</a></span>
                  <a href="./index.html" class="footerLHS">Contact information</a>
               </div>
               <div>
        Copyright (c) 2003, Your Company.
      </div>
               <div>
                  <a href="./index.html">Privacy Statement</a>
               </div>
               <br />
               <center>Ported to XMLNuke by JG (João Gilberto Magalhães)</center>
            </div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
