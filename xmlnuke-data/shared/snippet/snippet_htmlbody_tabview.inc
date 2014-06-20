<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- TABVIEW -->

<xsl:template match="tabview">

	<xsl:variable name="number"><xsl:value-of select="position()" /></xsl:variable>

	<script type="text/javascript">
	$(function() {
		$("#tabview<xsl:value-of select="$number" />").tabs({
			collapsible: true,
			ajaxOptions: {
				error: function(xhr, status, index, anchor) {
					$(anchor.hash).html("Couldn't load this tab. We'll try to fix this as soon as possible.");
				}
			}
			<xsl:for-each select="tabitem">
				<xsl:if test="@default='true'">, active: <xsl:value-of select="position()" />-1</xsl:if>
			</xsl:for-each>
		}).find(".ui-tabs-nav").sortable({axis:'x'});
	});
	</script>


	<div id="tabview{$number}">

		<ul>
			<xsl:for-each select="tabitem">
				<li>
					<a>
						<xsl:attribute name="href">
							<xsl:if test="@url"><xsl:value-of select="@url" /></xsl:if>
							<xsl:if test="not(@url)">#tabview<xsl:value-of select="$number"/>-<xsl:value-of select="position()"/></xsl:if>
						</xsl:attribute>
						<xsl:value-of select="@title" /></a></li>
			</xsl:for-each>
		</ul>

		<xsl:for-each select="tabitem">
			<xsl:if test="not(@url)">
			<div id="tabview{$number}-{position()}">
				<xsl:apply-templates />
			</div>
			</xsl:if>
	    </xsl:for-each>

	</div>

</xsl:template>


