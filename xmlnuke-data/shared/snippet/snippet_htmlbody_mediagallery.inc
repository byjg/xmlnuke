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

	<ul class="mediagallerygallery clearfix">
		<xsl:for-each select="mediaitem" >
			<li>
				<a href="{@src}" rel="prettyPhoto[{$mediagallery}]" title="{@caption}">
					<xsl:if test="@thumb"><img src="{@thumb}" width="{@width}" height="{@height}" alt="{@title}" /></xsl:if>
					<xsl:if test="not(@thumb)"><xsl:value-of select="@title" /></xsl:if>
				</a>
			</li>
		</xsl:for-each>
	</ul>

</xsl:template>

<xsl:template match="mediagallery[not(@visible='true') and @visible]"><!-- Api --></xsl:template>


<xsl:template match="mediaitem">

	<a href="#" onclick="$.prettyPhoto.open('{@src}');" title="{@caption}">
		<xsl:if test="@thumb"><img src="{@thumb}" width="{@width}" height="{@height}" alt="{@title}" /></xsl:if>
		<xsl:if test="not(@thumb)"><xsl:value-of select="@title" /></xsl:if>
	</a>

</xsl:template>


