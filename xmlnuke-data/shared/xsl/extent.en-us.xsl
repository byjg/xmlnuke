<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <title>
               <xsl:value-of select="page/meta/title" />
            </title>
	    <xmlnuke-htmlheader />
         </head>
         <body marginheight="0" marginwidth="0" link="#e5e5e5" vlink="#e5e5e5" alink="#e5e5e5" bgcolor="#ffffff">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
               <tr>
                  <!-- left nav bar column -->
                  <td width="20%" bgcolor="#425a7c" valign="top">
                     <br />
                     <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
                        <table border="0" cellspacing="0" cellpadding="2" width="98%">
                           <tr>
                              <td width="100%">
                                 <font color="#e5e5e5" face="helvetica">
                                    <b>
                                       <xsl:value-of select="title" />
                                    </b>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td width="100%" bgcolor="#5272a4">
                                 <xsl:for-each select="page">
                                    <font color="#e5e5e5">
                                       <a>
                                          <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>- <xsl:value-of select="title" /></a>
                                    </font>
                                    <br />
                                 </xsl:for-each>
                              </td>
                           </tr>
                        </table>
                     </xsl:for-each>
                     <xsl:for-each select="page/blockleft">
                        <table border="0" cellspacing="0" cellpadding="2" width="98%">
                           <tr>
                              <td width="100%">
                                 <font color="#e5e5e5" face="helvetica">
                                    <b>
                                       <xsl:value-of select="title" />
                                    </b>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td width="100%" bgcolor="#5272a4">
                                 <font color="#e5e5e5">
                                    <xsl:apply-templates select="body" />
                                 </font>
                              </td>
                           </tr>
                        </table>
                     </xsl:for-each>
                  </td>
                  <!-- middle content column -->
                  <td width="60%" valign="top">
                     <br />
                     <center>
                        <font color="#425a74" size="+2">
                           <b>Slashhack - News for 31337 kiddies</b>
                        </font>
                     </center>
                     <br />
                     <!-- news box -->
                     <table border="0" cellspacing="0" cellpadding="2" width="98%" align="center">
                        <tr>
                           <td width="100%" bgcolor="#425a74">
                              <font color="#e5e5e5" face="helvetica">
                                 <b>News</b>
                              </font>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#d6d6d6" width="100%">
                              <!-- sample news item -->
                              <xsl:for-each select="page/blockcenter">
                                 <b>
                                    <xsl:attribute name="id">H<xsl:number format="1" /></xsl:attribute>
                                    <xsl:value-of select="title" />
                                    <br />
                                    <small>--<br /></small>
                                 </b>
                                 <div align="justify">
                                    <xsl:apply-templates select="body" />
                                 </div>
                                 <hr width="80%" />
                              </xsl:for-each>
                              <br />
                              <!-- end of sample news item -->
                              <br />
                           </td>
                        </tr>
                     </table>
                  </td>
                  <!-- Right Column -->
                  <td width="20%" valign="top">
                     <!-- this is to try to get the spacing the same -->
                     <center>
                        <br />
                        <font color="#ffffff" size="+2">foo</font>
                        <br />
                        <br />
                     </center>
                     <xsl:for-each select="page/blockright">
                        <table border="0" cellspacing="0" cellpadding="2" width="100%">
                           <tr>
                              <td bgcolor="#425a74">
                                 <font face="helvetica" color="#e5e5e5">
                                    <b>
                                       <xsl:value-of select="title" />
                                    </b>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td bgcolor="#d6d6d6">
                                 <xsl:apply-templates select="body" />
                              </td>
                           </tr>
                        </table>
                        <br />
                     </xsl:for-each>
                  </td>
               </tr>
            </table>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
