<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- POLL -->

<xsl:template match="poll">

<xsl:if test="error">
	<i><xsl:value-of select="error" /></i>
</xsl:if>

<xsl:if test="not(error)">
	<form method="post" id="pollform" action="{@url}">
	<input name="xmlnuke_poll" type="hidden" value="{@name}" />
	<input name="xmlnuke_polllang" type="hidden" value="{@lang}" />
	
	<xsl:variable name="opttype">
		<xsl:if test="@multiple='true'">
			checkbox
		</xsl:if>
		
		<xsl:if test="not(@multiple='true')">
			radio
		</xsl:if>
	</xsl:variable>
	
	<fieldset>
		<dl>
			<dt style="clear:both;width:99%;float:left;text-align:left;text-weight:bold;margin:0 0 0.5em 0.25em;">
				<b><xsl:value-of select="question" /></b>
			</dt>
			<xsl:if test="@active='true'">
				<xsl:for-each select="answer">
					<dt>
						<input name="xmlnuke_pollanswer" type="{$opttype}" value="{@code}" />
					</dt>
					<dd>
						<xsl:value-of select="text()" />
					</dd>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="not(@active='true')">
				<input type="hidden" name="xmlnuke_pollanswer" value="NONE" />
			</xsl:if>
			<dt><img src="common/spacer.gif" width="1" height="1"/></dt><dd><input class="botao" type="submit" value="{@sendbtn}" /></dd>
		</dl>
	</fieldset>

	</form>
</xsl:if>

</xsl:template>