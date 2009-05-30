<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
            <meta name="author" content="haran" />
            <meta name="generator" content="Windows Notepad" />
            <link rel="stylesheet" type="text/css" href="common/styles/bluehaze.css" title="Blue Haze stylesheet" />
            <link rel="stylesheet" type="text/css" href="common/styles/bluehaze-color-scheme.css" title="Blue Haze stylesheet" />
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
               <span class="headerTitle">Blue Haze</span>
               <div class="menuBar">
                  <a href="http://www.oswd.org/index.phtml">Home</a>|
        <a href="http://www.oswd.org/browse.php">Browse Designs</a>|
        <a href="http://www.oswd.org/userinfo.phtml?user=haran">Designs by haran</a>|
        <span title="You're already here!">Blue Haze</span></div>
            </div>
            <!-- ###### Side Boxes ###### -->
            <!-- Navegue -->
            <div class="sideBox LHS">
               <div>This Page</div>
               <xsl:for-each select="page/blockcenter">
                  <a>
                     <xsl:attribute name="href">#H<xsl:number format="1" /></xsl:attribute>- <xsl:value-of select="title" /></a>
               </xsl:for-each>
            </div>
            <!-- MENU -->
            <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
               <div class="sideBox LHS">
                  <div>
                     <xsl:value-of select="title" />
                  </div>
                  <xsl:for-each select="page">
                     <a>
                        <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>- <xsl:value-of select="title" /></a>
                  </xsl:for-each>
               </div>
            </xsl:for-each>
            <!-- Block Right -->
            <xsl:for-each select="page/blockleft">
               <div class="sideBox LHS">
                  <div>
                     <xsl:value-of select="title" />
                  </div>
                  <span>
                     <xsl:apply-templates select="body" />
                  </span>
               </div>
            </xsl:for-each>
            <xsl:for-each select="page/blockright">
               <div class="sideBox RHS">
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
                     <a class="topOfPage" href="#top" title="Top Of Page">top</a>
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
               <div class="footerLHS">
                  <a href="http://validator.w3.org/check/referer">Valid XHTML 1.0 Strict</a>
               </div>
               <div class="footerLHS">
                  <a href="http://jigsaw.w3.org/css-validator/check/referer">Valid CSS 2</a>
               </div>
               <div>
        http://www.oswd.org/design/1112/bluehaze/index.html
      </div>
               <div>
        Website designed by <a href="http://www.oswd.org/email.phtml?user=haran" title="Email author">haran</a><br />Ported to XMLNuke <a href="http://www.byjg.com">by JG</a> (João Gilberto Magalhães)</div>
            </div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
