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
			     <a><xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="$xml" />&amp;xsl=xmlword</xsl:attribute>Salvar como Word</a> | <a href="module:search">Procurar</a> | [param:LANGUAGESELECTOR]
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
				 <h2><xsl:value-of select="page/group[keyword=//page/meta/groupkeyword]/title" /><br/><xsl:value-of select="page/meta/title" /></h2>
				 
				 <p align="justify">
                                    <i><xsl:value-of select="page/meta/abstract" /></i></p><!--<p align="right">by misfit (at linuxbr dot org)</p>--><!-- toc -->

				 <p>
                                 <xsl:for-each select="page/group[keyword=//page/meta/groupkeyword]">
                                    <b>Topicos</b><br/>
                                       <xsl:for-each select="page">
                                             <xsl:value-of select="position()" />. <a>
                                                <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
                                                <xsl:value-of select="title" /></a><br/>
                                       </xsl:for-each>
				 </xsl:for-each>
				 </p>

				 <p> 
				    <b>Nesta pagina</b><br/>
                                    <xsl:for-each select="page/blockcenter">
                                          <a>
                                             <xsl:attribute name="href">#H<xsl:number format="1" /></xsl:attribute>
                                             <xsl:value-of select="title" />
                                          </a><br/>
                                    </xsl:for-each>
				 </p>
				 
				 <br />
				 <xmlnuke-blockcentercss />
			     </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <xsl:if test="not(page/meta/groupkeyword='all')">
                  <table border="0" width="750" cellpadding="0" cellspacing="0">
                     <tr>
                        <td width="40%">
                           <div align="left">
                              <b>
                                 <xmlnuke-navigate_previous />
                              </b>
                           </div>
                        </td>
                        <td width="20%" valign="top">
                           <div align="center">
                              <b>
                                 <a href="engine:xmlnuke?xml=index&amp;xsl=index">Home</a>
                              </b>
                           </div>
                        </td>
                        <td width="40%">
                           <div align="right">
                              <b>
                                 <xmlnuke-navigate_next />
                              </b>
                           </div>
                        </td>
                     </tr>
                  </table>
               </xsl:if>
               <br />
            </center>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
