<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- TABVIEW -->

<xsl:template match="tabview">

	<xsl:variable name="number"><xsl:value-of select="position()" /></xsl:variable>

	<script type="text/javascript">
	$(function() {
		$("#tabview<xsl:value-of select="$number" />").tabs({
			collapsible: true
			<xsl:for-each select="tabitem">
				<xsl:if test="@default='true'">, selected: <xsl:value-of select="position()" />-1</xsl:if>
			</xsl:for-each>
		}).find(".ui-tabs-nav").sortable({axis:'x'});
	});
	</script>


	<div id="tabview{$number}">

		<ul>
			<xsl:for-each select="tabitem">
				<li><a href="#tabview{$number}-{position()}"><xsl:value-of select="@title" /></a></li>
			</xsl:for-each>
		</ul>

		<xsl:for-each select="tabitem">
			<div id="tabview{$number}-{position()}">
				<xsl:apply-templates />
			</div>
	    </xsl:for-each>

	</div>

</xsl:template>


