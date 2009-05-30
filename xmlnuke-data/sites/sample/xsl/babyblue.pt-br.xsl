<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Baby Blog</title>
            <link href="common/styles/babyblog.css" rel="stylesheet" type="text/css" media="all" />
	    <xmlnuke-htmlheader />
         </head>
         <body>
            <div id="wrap">
               <div id="header">
                  <h2>Baby Blog</h2>
               </div>
               <div id="content">
                  <h3 id="headline">
                     <xsl:value-of select="page/meta/title" />
                  </h3>
                  <p id="linein">
                     <xsl:value-of select="page/meta/abstract" />
                  </p>
                  <div id="blog">
                     <xsl:for-each select="page/blockcenter">
                        <!--<h4 class="title-date">2005/9/10</h4>-->
                        <h4 class="title-entry">
                           <a>
                              <xsl:attribute name="name">H<xsl:number format="1" /></xsl:attribute>
                           </a>
                           <xsl:value-of select="title" />
                        </h4>
                        <xsl:apply-templates select="body" />
                     </xsl:for-each>
                  </div>
               </div>
               <div id="nav-bar">
                  <div id="author">Photo</div>
                  <!--
                  <div id="achives">
                     <ul>
                        <li>2005/9</li>
                     </ul>
                  </div>
-->
                  <div id="links">
                     <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
                        <xsl:value-of select="title" />
                        <ul>
                           <xsl:for-each select="page">
                              <li>
                                 <a>
                                    <xsl:choose>
                                       <xsl:when test="starts-with(id,'url://')">
                                          <xsl:attribute name="href">
                                             <xsl:value-of select="substring-after(id, 'url://')" />
                                          </xsl:attribute>
                                       </xsl:when>
                                       <xsl:otherwise>
                                          <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
                                       </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:value-of select="title" />
                                 </a>
                              </li>
                           </xsl:for-each>
                        </ul>
                     </xsl:for-each>
                  </div>
                  <div id="cat">
                     <xsl:for-each select="page/blockleft">
                        <xsl:value-of select="title" />
                        <xsl:apply-templates select="body" />
                     </xsl:for-each>
                  </div>
               </div>
               <div id="footer">Baby Blog ver 0.1<br />Modified by João Gilberto Magalhães</div>
            </div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
