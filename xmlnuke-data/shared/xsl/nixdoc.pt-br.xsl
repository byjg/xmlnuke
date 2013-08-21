<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>
               <xsl:value-of select="page/meta/title" />
            </title>
	    <xmlnuke-htmlheader />
         </head>
         <body>
            <center>
               <table border="0" width="750" cellpadding="0" cellspacing="1" bgcolor="silver">
                  <tr>
                     <td>
                        <table border="0" width="750" cellpadding="20" cellspacing="0" bgcolor="white">
                           <tr>
                              <td>
                                 <img src="common/imgs/logo_xmlnuke_pb.gif" />
                                 <br />
                                 <!-- about -->
                                 <h2>
                                    <xsl:value-of select="page/meta/title" />
                                 </h2>
                                 <p align="justify">
                                    <xsl:value-of select="page/meta/abstract" />
                                 </p>
                                 <!--<p align="right">by misfit (at linuxbr dot org)</p>-->
                                 <!-- toc -->
                                 <h2>
                                    Table of contents
                                 </h2>
                                 <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword]">
                                    <h3>
                                       <xsl:value-of select="position()" />. <xsl:value-of select="title" /></h3>
                                    <dl>
                                       <xsl:for-each select="page">
                                          <dd>
                                             <xsl:value-of select="position()" />. <a><xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute><xsl:value-of select="title" /></a></dd>
                                       </xsl:for-each>
                                    </dl>
                                 </xsl:for-each>
                                 <h3>This Page</h3>
                                 <dl>
                                    <xsl:for-each select="page/blockcenter">
                                       <dd>
                                          <a>
                                             <xsl:attribute name="href">#H<xsl:number format="1" /></xsl:attribute>
                                             <xsl:value-of select="title" />
                                          </a>
                                       </dd>
                                    </xsl:for-each>
                                 </dl>
                                 <br />
                                 <xmlnuke-blockcentercss />
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <table border="0" width="750" cellpadding="0" cellspacing="0">
                  <tr>
                     <td width="20%">
                        <div align="left">
                           <b>
                              <xmlnuke-navigate_previous />
                           </b>
                        </div>
                     </td>
                     <td width="60%">
                        <div align="center">
                           <b>
                              <a href="engine:xmlnuke?xml=index&amp;xsl=index">Home</a>
                           </b>
                        </div>
                     </td>
                     <td width="20%">
                        <div align="right">
                           <b>
                              <xmlnuke-navigate_next />
                           </b>
                        </div>
                     </td>
                  </tr>
               </table>
               <br />
            </center>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
