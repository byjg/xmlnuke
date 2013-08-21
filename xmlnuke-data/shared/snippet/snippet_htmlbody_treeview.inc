<!--**
**   Description : STYLESHEET FOR GENERATION OF THE HTML Treeview
**
**   Date : 16/04/2009 - version : 1.3.1
**   Author : Jean-Michel Garnier, http://rollerjm.free.fr
**   Source is free but feel free (!) to send any comment / suggestion to garnierjm@ifrance.com
**   Updates :
**   - 07/01/2003 : add img-directory parameter
**   - 18/01/2003 : remove deploy-treeview parameter, add XSLT param-isMozilla, refactoring bc of DTD changes
**   - 05/08/2003 : fix bug when using the expanded parameter
**   - 16/04/2009 : Ported to XMLNuke
** 
-->


<!-- Change the encoding here if you need it, i.e. UTF-8 -->
<!--<xsl:output method="html" encoding="iso-8859-1" indent="yes"/>-->
<!-- ************************************ Parameters ************************************ -->
<!-- deploy-treeview, boolean - true if you want to deploy the tree-view at the first print -->
<xsl:variable name="param-deploy-treeview" select="'false'"/>
<!-- hozizontale distance in pixels between a folder and its leaves -->
<xsl:variable name="param-shift-width" select="15"/>
<!-- image source directory-->
<xsl:variable name="param-img-directory" select="'common/3rdparty/treeview/'"/>
<!-- ************************************ Variables ************************************ -->
<xsl:variable name="var-simple-quote">'</xsl:variable>
<xsl:variable name="var-slash-quote">\'</xsl:variable>
<!--
**
**  Model "treeview"
** 
**  This model transforms an XML treeview into an html treeview
**  
-->
<xsl:template match="treeview">

	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>
				<!-- Apply the template folder starting with a depth in the tree of 1-->
				<xsl:apply-templates select="folder">
					<xsl:with-param name="depth" select="1"/>
				</xsl:apply-templates>
			</td>
		</tr>
	</table>

</xsl:template>
<!--
**
**  Model "folder"
** 
**  This model transforms a folder element. Prints a plus (+) or minus (-)  image, the folder image and a title
**  
-->
<xsl:template match="folder">
	<xsl:param name="depth"/>
	<table border="0" cellspacing="0" cellpadding="0">
		<xsl:if test="@code">			<xsl:attribute name="id"><xsl:value-of select="@code"/></xsl:attribute>		</xsl:if>		<tr>
			<!-- If first level of depth, do not shift of $param-shift-width-->
			<xsl:if test="$depth>1">
				<td width="{$param-shift-width}"/>
			</xsl:if>
			<td>
				<a class="folder">
					<xsl:attribute name="onclick">						<xsl:if test="not(@action)">toggle(this)</xsl:if>						<xsl:if test="@action">toggle2(this, "<xsl:value-of select="@action" />")</xsl:if>					</xsl:attribute>
					<!-- If the treeview is unfold, the image minus (-) is displayed-->
					<xsl:if test="@expanded">
						<xsl:if test="@expanded='true'">
							<img src="{$param-img-directory}minus.gif"/>
						</xsl:if>
						<!-- plus (+) otherwise-->
						<xsl:if test="@expanded='false'">
							<img src="{$param-img-directory}plus.gif"/>
						</xsl:if>
					</xsl:if>
					<xsl:if test="not(@expanded)">
						<xsl:if test="$param-deploy-treeview = 'true'">
							<img src="{$param-img-directory}minus.gif"/>
						</xsl:if>
						<xsl:if test="$param-deploy-treeview = 'false' or not(@expanded)">
							<img src="{$param-img-directory}plus.gif"/>
						</xsl:if>
					</xsl:if>
					<img src="{$param-img-directory}{@img}">
						<!-- if the attribut alt is present-->
						<xsl:if test="@alt">
							<xsl:attribute name="title"><xsl:value-of select="@alt"/></xsl:attribute>
							<xsl:attribute name="alt"><xsl:value-of select="@alt"/></xsl:attribute>
						</xsl:if>
					</img>
					<xsl:value-of select="@title"/>
				</a>
				<!-- Shall we expand all the leaves of the treeview ? no by default-->
				<div>
					<xsl:if test="@expanded">
						<xsl:if test="@expanded='true'">
							<xsl:attribute name="style">display:block;</xsl:attribute>
						</xsl:if>
						<!-- plus (+) otherwise-->
						<xsl:if test="@expanded='false'">
							<xsl:attribute name="style">display:none;</xsl:attribute>
						</xsl:if>
					</xsl:if>
					
					<xsl:if test="not(@expanded)">
						<xsl:if test="$param-deploy-treeview = 'true'">
							<xsl:attribute name="style">display:block;</xsl:attribute>
						</xsl:if>
						<xsl:if test="$param-deploy-treeview = 'false'">
							<xsl:attribute name="style">display:none;</xsl:attribute>
						</xsl:if>
					</xsl:if>
									
					<!-- Thanks to the magic of reccursive calls, all the descendants of the present folder are gonna be built -->
					<xsl:apply-templates select="folder">
						<xsl:with-param name="depth" select="$depth+1"/>
					</xsl:apply-templates>
					<!-- print all the leaves of this folder-->
					<xsl:apply-templates select="leaf"/>
				</div>
			</td>
		</tr>
	</table>
</xsl:template>
<!--
**
**  Model "leaf"
** 
**  This model prints an image plus the name of the element
**  
-->
<xsl:template match="leaf">
	<table border="0" cellspacing="0" cellpadding="0">
		<xsl:if test="@code">			<xsl:attribute name="id"><xsl:value-of select="@code"/></xsl:attribute>		</xsl:if>		<tr>
			<td width="{$param-shift-width}"/>
			<td>
				<a><xsl:attribute name="class">leaf<xsl:if test="@selected">sel</xsl:if></xsl:attribute>
					<!-- The line is very long bu I have no choice, I called the function replace-string to replace the quotes (') by /' -->
					<!-- <xsl:attribute name="onclick">selectLeaf('<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>','<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@code"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute> -->
					<xsl:attribute name="onclick">toggleSelect(this); <xsl:value-of select="@action" /></xsl:attribute>					<!-- if it is the last leaf, print a different image for the link to the folder-->
					<xsl:choose>
						<xsl:when test="position()=last()">
							<img src="{$param-img-directory}lastlink.gif"/>
						</xsl:when>
						<xsl:otherwise>
							<img src="{$param-img-directory}link.gif"/>
						</xsl:otherwise>
					</xsl:choose>
					<img src="{$param-img-directory}{@img}">
						<!-- if the attribut alt is present-->
						<xsl:if test="@alt">
							<xsl:attribute name="title"><xsl:value-of select="@alt"/></xsl:attribute>
							<xsl:attribute name="alt"><xsl:value-of select="@alt"/></xsl:attribute>
						</xsl:if>
					</img>
					<xsl:value-of select="@title"/>
				</a>
			</td>
		</tr>
	</table>
</xsl:template>
<!--
**
**  Model "replace-string"
** 
**  reusable replace-string function **  
-->
<xsl:template name="replace-string">
	<xsl:param name="text"/>
	<xsl:param name="from"/>
	<xsl:param name="to"/>
	<xsl:choose>
		<xsl:when test="contains($text, $from)">
			<xsl:variable name="before" select="substring-before($text, $from)"/>
			<xsl:variable name="after" select="substring-after($text, $from)"/>
			<xsl:variable name="prefix" select="concat($before, $to)"/>
			<xsl:value-of select="$before"/>
			<xsl:value-of select="$to"/>
			<xsl:call-template name="replace-string">
				<xsl:with-param name="text" select="$after"/>
				<xsl:with-param name="from" select="$from"/>
				<xsl:with-param name="to" select="$to"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$text"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

