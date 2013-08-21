<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <!-- Page Title -->
            <title>
               <xsl:value-of select="page/meta/title" />
            </title>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
            <meta http-equiv="Content-Style-Type" content="text/css" />
            <!-- Update these tags -->
            <meta name="description" content="This is the Evolver open source web design. Use anyway you see fit." />
            <meta name="keywords" content="Evolver, Rialto, open source web design, oswd, free web template" />
      <xmlnuke-htmlheader />
            <!-- Dynamic resizing tip#1: Change margin: 30px; to margin: 0px; then scroll down to Dynamic resizing tip#2 -->
            <style type="text/css">
body {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 12px;
 font-weight: normal;
 margin: 30px;
 padding: 0px;
 background-color: #9FAC9F;
 color: #5A6F5A;
}

h1 {
 font-family: Times New Roman, serif;
 font-size: 26px;
 font-weight: normal;
 color: #e9f2fc;
 background-color: transparent;
 padding: 0px;
 margin: 0px;
 }

h2 {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 14px;
 font-weight: bold;
 line-height: 14px;
 color: #5A6F5A;
 background-color: transparent;
 }

 h2.titletext {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 14px;
 font-weight: bold;
 line-height: 14px;
 border-bottom: 1px solid #769176;
 color: #5A6F5A;
 background-color: transparent;
 }

p {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 12px;
 font-weight: normal;
 padding-left: 15px;
 line-height: 18px;
 color: #5A6F5A;
 background-color: transparent;
 }

a {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 12px;
 font-weight: normal;
 text-decoration: underline;
 color: #5A6F5A;
 background-color: transparent;
 }

a:visited {
 text-decoration: underline;
 color: #5A6F5A;
 background-color: transparent;
 }

a.navlink {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 11px;
 font-weight: normal;
 text-decoration: none;
 color: #e9f2fc;
 background-color: transparent;
 }

a.navlink:visited {
 text-decoration: none;
 color: #e9f2fc;
 background-color: transparent;
 }

a.newslink {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 10px;
 font-weight: bold;
 line-height: 10px;
 text-decoration: none;
 color: #5A6F5A;
 background-color: transparent;
 }

a.newslink:visited {
 text-decoration: none;
 color: #900020;
 color: #5A6F5A;
 background-color: transparent;
 }

a.newslink:hover {
 text-decoration: underline;
 }

li {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 12px;
 font-weight: normal;
 line-height: 18px;
 padding-bottom: 7px;
 color: #5A6F5A;
 background-color: transparent;
 }

.navcell {
 padding: 3px 7px 3px 7px;
 }

.newsheader {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 10px;
 font-weight: normal;
 line-height: 10px;
 padding-left: 5px;
 color: #e9f2fc;
 background-color: #6C856C;
 }

.newscell {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 10px;
 font-weight: normal;
 line-height: 10px;
 padding-top: 7px;
 padding-left: 5px;
 padding-right: 5px;
 padding-bottom: 9px;
 color: #5A6F5A;
 background-color: #9FAC9F;
 }

 .navinput {
  vertical-align: middle;
  width: 120px;
  color: #5A6F5A;
  background-color: #e9f2fc;
 }

 .navbutton {
  position: relative;
  left: 3px;
  width: 30px;
  background-color: #5A6F5A;
  color: #e9f2fc;
  font-family: Verdana, Lucida, helvetica, arial, sans-serif;
  font-size: 10px;
  font-weight: normal;
  vertical-align: middle;
 }

.update {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 10px;
 font-weight: normal;
 line-height: 14px;
 color: #e9f2fc;
 background-color: transparent;
 }

.copyright {
 font-family: Verdana, Lucida, helvetica, arial, sans-serif;
 font-size: 10px;
 font-weight: normal;
 line-height: 10px;
 color: #464c64;
 background-color: transparent;
 }
</style>
         </head>
         <body>
            <!-- Outer Border -->
            <!-- Dynamic resizing tip#2: Change width="700" to width="100%" -->
            <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" align="center" width="700">
               <tr>
                  <td>
                     <table cellspacing="0" cellpadding="1" border="0" bgcolor="#cccccc" width="100%">
                        <tr>
                           <td>
                              <table cellspacing="0" cellpadding="2" border="0" bgcolor="#ffffff" width="100%">
                                 <tr>
                                    <td>
                                       <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                          <tr>
                                             <td>
                                                <table cellspacing="0" cellpadding="1" border="0" bgcolor="#7b917b" width="100%">
                                                   <tr>
                                                      <td>
                                                         <table cellspacing="1" cellpadding="0" border="0" width="100%">
                                                            <tr>
                                                               <td>
                                                                  <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                                                     <tr>
                                                                        <td style="padding-left: 20px" align="left" valign="bottom">
                                                                           <!-- Page Title -->
                                                                           <br />
                                                                           <h1>*evolver</h1>
                                                                           <br />
                                                                        </td>
                                                                        <!-- Navigation Bar -->
                                                                        <td align="right" valign="bottom">
                                                                           <table cellspacing="1" cellpadding="2" border="0" bgcolor="#5A6F5A" width="60%">
                                                                              <tr>
                                                                                 <td bgcolor="#9FAC9F" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">home</a>
                                                                                 </td>
                                                                                 <td bgcolor="#6C856C" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">diary</a>
                                                                                 </td>
                                                                                 <td bgcolor="#6C856C" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">photos</a>
                                                                                 </td>
                                                                                 <td bgcolor="#6C856C" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">linux</a>
                                                                                 </td>
                                                                                 <td bgcolor="#6C856C" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">files</a>
                                                                                 </td>
                                                                                 <td bgcolor="#6C856C" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">friends</a>
                                                                                 </td>
                                                                                 <td bgcolor="#6C856C" class="navcell" align="center">
                                                                                    <a class="navlink" href="index.html">links</a>
                                                                                 </td>
                                                                              </tr>
                                                                           </table>
                                                                        </td>
                                                                     </tr>
                                                                  </table>
                                                               </td>
                                                            </tr>
                                                            <!-- Page Middle -->
                                                            <tr>
                                                               <td>
                                                                  <table cellspacing="1" cellpadding="2" border="0" bgcolor="#5A6F5A" width="100%">
                                                                     <tr>
                                                                        <td bgcolor="#cccccc" style="padding-left: 10px">
                                                                           <table cellpadding="2" cellspacing="5" border="0" width="100%">
                                                                              <tr>
                                                                                 <!-- Adjusting the width of the following cell, adjust the width of *all* left-hand boxes (search, news, etc.) uniformly -->
                                                                                 <td valign="top" width="185" style="padding-top: 12px">
                                                                                    <!-- Search Section -->
                                                                                    <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                                                                       <tr>
                                                                                          <td>
                                                                                             <table cellspacing="0" cellpadding="1" border="0" bgcolor="#cccccc" width="100%">
                                                                                                <tr>
                                                                                                   <td>
                                                                                                      <table cellspacing="0" cellpadding="2" border="0" bgcolor="#ffffff" width="100%">
                                                                                                         <tr>
                                                                                                            <td>
                                                                                                               <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                                                                                                  <tr>
                                                                                                                     <td>
                                                                                                                        <table cellspacing="2" cellpadding="2" border="0" bgcolor="#6C856C" width="100%">
                                                                                                                           <tr>
                                                                                                                              <td class="newsheader">
                                                                                                                                 <strong>simple search</strong>
                                                                                                                              </td>
                                                                                                                           </tr>
                                                                                                                           <tr>
                                                                                                                              <td class="newscell">
                                                                                                                                 <strong>enter some keywords</strong>
                                                                                                                                 <br />
                                                                                                                                 <br />
                                                                                                                                 <!-- Search Form -->
                                                                                                                                 <form method="post" action="module:Search?action=search">
                                                                                                                                    <input size="18" name="query" class="navinput" />
                                                                                                                                    <input type="submit" value="go" class="navbutton" />
                                                                                                                                 </form>
                                                                                                                              </td>
                                                                                                                           </tr>
                                                                                                                        </table>
                                                                                                                     </td>
                                                                                                                  </tr>
                                                                                                               </table>
                                                                                                            </td>
                                                                                                         </tr>
                                                                                                      </table>
                                                                                                   </td>
                                                                                                </tr>
                                                                                             </table>
                                                                                          </td>
                                                                                       </tr>
                                                                                    </table>
                                                                                    <br />
                                                                                    <!-- News Section -->
                                                                                    <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword] | page/group[keyword='all']">
                                                                                       <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                                                                          <tr>
                                                                                             <td>
                                                                                                <table cellspacing="0" cellpadding="1" border="0" bgcolor="#cccccc" width="100%">
                                                                                                   <tr>
                                                                                                      <td>
                                                                                                         <table cellspacing="0" cellpadding="2" border="0" bgcolor="#ffffff" width="100%">
                                                                                                            <tr>
                                                                                                               <td>
                                                                                                                  <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                                                                                                     <tr>
                                                                                                                        <td>
                                                                                                                           <table cellspacing="2" cellpadding="2" border="0" bgcolor="#6C856C" width="100%">
                                                                                                                              <tr>
                                                                                                                                 <td class="newsheader">
                                                                                                                                    <strong>
                                                                                                                                       <xsl:value-of select="title" />
                                                                                                                                    </strong>
                                                                                                                                 </td>
                                                                                                                              </tr>
                                                                                                                              <tr>
                                                                                                                                 <td class="newscell">
                                                                                                                                    <xsl:for-each select="page">
                                                                                                                                       <p>
                                                                                                                                          <a>
                                                                                                                                             <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>- <xsl:value-of select="title" /></a>
                                                                                                                                       </p>
                                                                                                                                    </xsl:for-each>
                                                                                                                                 </td>
                                                                                                                              </tr>
                                                                                                                           </table>
                                                                                                                        </td>
                                                                                                                     </tr>
                                                                                                                  </table>
                                                                                                               </td>
                                                                                                            </tr>
                                                                                                         </table>
                                                                                                      </td>
                                                                                                   </tr>
                                                                                                </table>
                                                                                             </td>
                                                                                          </tr>
                                                                                       </table>
                                                                                    </xsl:for-each>
                                                                                    <xsl:for-each select="page/blockleft | page/blockright">
                                                                                       <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                                                                          <tr>
                                                                                             <td>
                                                                                                <table cellspacing="0" cellpadding="1" border="0" bgcolor="#cccccc" width="100%">
                                                                                                   <tr>
                                                                                                      <td>
                                                                                                         <table cellspacing="0" cellpadding="2" border="0" bgcolor="#ffffff" width="100%">
                                                                                                            <tr>
                                                                                                               <td>
                                                                                                                  <table cellspacing="0" cellpadding="1" border="0" bgcolor="#5a6f5a" width="100%">
                                                                                                                     <tr>
                                                                                                                        <td>
                                                                                                                           <table cellspacing="2" cellpadding="2" border="0" bgcolor="#6C856C" width="100%">
                                                                                                                              <tr>
                                                                                                                                 <td class="newsheader">
                                                                                                                                    <strong>
                                                                                                                                       <xsl:value-of select="title" />
                                                                                                                                    </strong>
                                                                                                                                 </td>
                                                                                                                              </tr>
                                                                                                                              <tr>
                                                                                                                                 <td class="newscell">
                                                                                                                                    <xsl:apply-templates select="body" />
                                                                                                                                 </td>
                                                                                                                              </tr>
                                                                                                                           </table>
                                                                                                                        </td>
                                                                                                                     </tr>
                                                                                                                  </table>
                                                                                                               </td>
                                                                                                            </tr>
                                                                                                         </table>
                                                                                                      </td>
                                                                                                   </tr>
                                                                                                </table>
                                                                                             </td>
                                                                                          </tr>
                                                                                       </table>
                                                                                    </xsl:for-each>
                                                                                    <!-- Add more left-hand tables here if you like -->
                                                                                 </td>
                                                                                 <!-- Content -->
                                                                                 <td valign="top">
                                                                                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                                                                       <tr>
                                                                                          <td bgcolor="#cccccc" style="padding-left: 10px; padding-top: 12px; padding-right: 15px">
                                                                                             <xsl:for-each select="page/blockcenter">
                                                                                                <h2 class="titletext">
                                                                                                   <xsl:attribute name="id">H<xsl:number format="1" /></xsl:attribute>
                                                                                                   <xsl:value-of select="title" />
                                                                                                </h2>
                                                                                                <xsl:apply-templates select="body" />
                                                                                             </xsl:for-each>
                                                                                          </td>
                                                                                       </tr>
                                                                                    </table>
                                                                                 </td>
                                                                              </tr>
                                                                           </table>
                                                                        </td>
                                                                     </tr>
                                                                     <!-- Last Update -->
                                                                     <tr>
                                                                        <td align="right" class="update" bgcolor="#6C856C">
  Last Update: 10 October 2004<br />
Ported to XMLNuke by JG (João Gilberto Magalhães)
</td>
                                                                     </tr>
                                                                  </table>
                                                               </td>
                                                            </tr>
                                                            <!-- Copyright Info -->
                                                            <tr>
                                                               <td align="right" class="copyright">
  (c) 2003 Your Name
</td>
                                                            </tr>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </table>
                                             </td>
                                          </tr>
                                       </table>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
