<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
      <html>
         <head>
            <title>-</title>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <link rel="stylesheet" href="common/styles/thingreenline.css" type="text/css" media="screen,projection" />
	    <xmlnuke-htmlheader />
         </head>
         <body>
            <div id="container">
               <div id="header">
                  <h1>
                     <a href="engine:xmlnuke?xml=home">
                        <img src="common/imgs/logo_xmlnuke.gif" border="0" />
                     </a>
                  </h1>
                  <h3>XMLNuke - A web site development framework.</h3>
               </div>
               <ul id="nav">
                  <li>
                     <a href="engine:xmlnuke?xml=home" class="active">Home</a>
                  </li>
                  <li>
                     <a href="engine:xmlnuke?xsl=rss">RSS</a>
                  </li>
                  <li>
                     <a href="engine:xmlnuke?xsl=wml">WML</a>
                  </li>
                  <li>
                     <a href="http://www.byjg.com.br/">ByJG.com.br</a>
                  </li>
               </ul>
               <br class="clear" />
               <div id="sidebar">
                  <xmlnuke-menucss />
                  <div class="sidebarfooter">
                     <!--<a href="http://www.getfirefox.com">Get FF</a>-->
                  </div>
                  <div id="sidebar_bottom">
                  </div>
                  <xmlnuke-blockleftcss />
                  <xmlnuke-blockrightcss />
               </div>
               <div id="content">
                  <xmlnuke-blockcentercss />
               </div>
            </div>
            <div id="footer">
               <p>
                  <!-- Please leave this line intact -->
								Template design by <a href="http://www.sixshootermedia.com">Six Shooter Media</a>.<br /><!-- you can delete below here -->
															Modified by Joao Gilberto Magalhães for XMLNuke project.
				
						</p>
            </div>
         </body>
      </html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
