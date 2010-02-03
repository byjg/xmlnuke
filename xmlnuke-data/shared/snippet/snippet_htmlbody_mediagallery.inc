<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- mediagallery -->

<!--

<mediagallery name="">
	<media src="" thumb="" caption="" title="" width="" height="" />
	...
</mediagallery>


-->

<xsl:template match="mediagallery[@visible='true' or not(@visible)]">

	<xsl:variable name="mediagallery"><xsl:value-of select="@name" /></xsl:variable>
	<xsl:variable name="showthumbcaption"><xsl:value-of select="@showthumbcaption" /></xsl:variable>

	<ul class="mediagallery clearfix">
		<xsl:for-each select="mediaitem" >
			<li>
				<a href="{@src}" rel="prettyPhoto[{$mediagallery}]" title="{@caption}">
					<xsl:if test="@thumb">
						<img src="{@thumb}" alt="{@title}" title="{@title}">
							<xsl:if test="@width and not(@width='')"><xsl:attribute name="width"><xsl:value-of select="@width" /></xsl:attribute></xsl:if>
							<xsl:if test="@height and not(@height='')"><xsl:attribute name="height"><xsl:value-of select="@height" /></xsl:attribute></xsl:if>
						</img>
					</xsl:if>
					<xsl:if test="not(@thumb)"><xsl:value-of select="@title" /></xsl:if>
				</a>
				<xsl:if test="$showthumbcaption='true'">
					<br/><xsl:value-of select="@title" />
				</xsl:if>
			</li>
		</xsl:for-each>
	</ul>

</xsl:template>

<xsl:template match="mediagallery[not(@visible='true') and @visible]"><!-- Api --></xsl:template>


<xsl:template match="mediaitem">

	<a href="#" onclick="$.prettyPhoto.open('{@src}');" title="{@caption}">
		<xsl:if test="@thumb">
			<img src="{@thumb}" alt="{@title}" title="{@title}">
				<xsl:if test="@width and not(@width='')"><xsl:attribute name="width"><xsl:value-of select="@width" /></xsl:attribute></xsl:if>
				<xsl:if test="@height and not(@height='')"><xsl:attribute name="height"><xsl:value-of select="@height" /></xsl:attribute></xsl:if>
			</img>
		</xsl:if>
		<xsl:if test="not(@thumb)"><xsl:value-of select="@title" /></xsl:if>
	</a>

</xsl:template>


