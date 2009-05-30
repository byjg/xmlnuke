<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <HTML>
         <link href="common/styles/portal-light.css" type="text/css" rel="stylesheet" />
         <HEAD>
            <TITLE>
               <xsl:value-of select="page/meta/title" />
            </TITLE>
            <xmlnuke-keywords />
            <xmlnuke-scripts />
	    <xmlnuke-htmlheader />
         </HEAD>
         <BODY>
            <div Align="right">
               <a href="engine:xmlnuke?site=[param:site]&amp;xml=index&amp;xsl=index">Plan du site</a> | <a href="#" onclick="javascript:window.open('[param:script_name]?=[param:xml]&amp;xsl=preview&amp;site=[param:site]&amp;lang=[param:lang]&amp;module=[param:module]','','toolbar=yes,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no,width=660,height=440')">Imprimer</a> | [param:languageselector]
				</div>
            <TABLE WIDTH="100%" BORDER="0" class="MAINTITLEBACKGROUND">
               <TR>
                  <TD ALIGN="center" VALIGN="center">
                     <img src="common/imgs/logo_xmlnuke_pb.gif" />
                  </TD>
               </TR>
            </TABLE>
            <TABLE WIDTH="100%" BORDER="0" class="SUBMAINTITLEBACKGROUND">
               <TR>
                  <TD ALIGN="center" VALIGN="center">
                     <xsl:value-of select="page/meta/title" />
                  </TD>
               </TR>
            </TABLE>
            <TABLE BORDER="0">
               <TR VALIGN="TOP">
                  <TD style="WIDTH:120px">
                     <xmlnuke-menu />
                     <xmlnuke-blockleft />
                  </TD>
                  <TD WIDTH="100%">
                     <p>
                     </p>
                     <xmlnuke-tableofcontentshoriz />
                     <xmlnuke-blockcenter />
                     <p>
                     </p>
                     <hr />
                     <xmlnuke-menuhoriz />
                  </TD>
                  <xmlnuke-blockright />
               </TR>
            </TABLE>
            <div align="right">[param:xmlnuke]</div>
         </BODY>
      </HTML>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
