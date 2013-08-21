<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <HTML>
         <link href="common/styles/portal-light.css" type="text/css" rel="stylesheet" />
         <HEAD>
            <TITLE>Demo du plan du site</TITLE>
         </HEAD>
         <BODY>
            <H1>Demo du plan du site</H1>
            <H2>Index</H2>
            <xsl:for-each select="xmlindex/group">
               <TABLE BORDER="0" COLspan="1" CELLPADDING="1" style="WIDTH:620px">
                  <TR>
                     <TD ColSpan="3">
                        <b>
                           <xsl:value-of select="title" />
                        </b>
                     </TD>
                  </TR>
                  <xsl:for-each select="page">
                     <TR VALIGN="TOP">
                        <TD ALIGN="RIGHT" style="WIDTH:20px"> -</TD>
                        <TD style="WIDTH:150px">
                           <a>
                              <xsl:attribute name="href">engine:xmlnuke?xml=<xsl:value-of select="id" /></xsl:attribute>
                              <xsl:value-of select="title" />
                           </a>
                        </TD>
                        <TD style="WIDTH:450px">
                           <i>
                              <xsl:value-of select="summary" />
                           </i>
                        </TD>
                     </TR>
                  </xsl:for-each>
               </TABLE>
               <p />
            </xsl:for-each>
            <p>
               <a href="admin:engine?site=[param:site]">
                  <b>Index de l'administration</b>
               </a>
            </p>
            <p>
               <b>[param:languageselector]</b>
            </p>
         </BODY>
      </HTML>
   </xsl:template>
</xsl:stylesheet>