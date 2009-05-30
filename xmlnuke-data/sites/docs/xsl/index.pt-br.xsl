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
	    <style>
		    body {font-family: Trebuchet, Verdana, Arial; font-size: 12px;}
		    td {font-family: Trebuchet, Verdana, Arial; font-size: 12px;}
           </style>
	    <xmlnuke-htmlheader />
	 </head>
         <body>
            <center>
               <table border="0" width="750" cellpadding="0" cellspacing="1">
                  <tr>
                     <td align="right">
                        <a href="module:search">Procurar</a> | [param:LANGUAGESELECTOR]
                     </td>
                  </tr>
               </table>
               <table border="0" width="750" cellpadding="0" cellspacing="1" bgcolor="silver">
                  <tr>
                     <td>
                        <table border="0" width="750" cellpadding="20" cellspacing="0" bgcolor="white">
                           <tr>
                              <td>
                                 <img src="common/imgs/logo_xmlnuke_pb.gif" />
                                 <br />
                                 <!-- about -->
                                 <!--
                                 <h2>
                                    <xsl:value-of select="page/meta/title" />
                                 </h2>
                                 <p align="justify">
                                    <xsl:value-of select="page/meta/abstract" />
                                 </p>
-->
                                 <!-- toc -->
                                 <h2>
                                    Menu de Opções
                                 </h2>
                                 <xsl:for-each select="xmlindex/group[keyword!='all']">
                                    <h3>
                                       <xsl:value-of select="position()" />. <xsl:value-of select="title" /></h3>
                                    <dl>
                                       <xsl:for-each select="page">
                                          <dd>
                                             <xsl:value-of select="position()" />. <a><xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute><xsl:value-of select="title" /></a></dd>
                                       </xsl:for-each>
                                    </dl>
                                 </xsl:for-each>
                              </td>
                           </tr>
                        </table>
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
