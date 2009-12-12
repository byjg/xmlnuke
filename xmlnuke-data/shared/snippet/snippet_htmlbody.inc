<!-- META SNIPPET -->

<xmlnuke-htmlbody_basics />
<xmlnuke-htmlbody_editform />
<xmlnuke-htmlbody_editlist />
<xmlnuke-htmlbody_calendar />
<xmlnuke-htmlbody_faq />
<xmlnuke-htmlbody_duallist />
<xmlnuke-htmlbody_tabview />
<xmlnuke-htmlbody_sortablelist />
<xmlnuke-htmlbody_poll />
<xmlnuke-htmlbody_treeview />
<xmlnuke-htmlbody_uialert />
<xmlnuke-htmlbody_mediagallery />

<xsl:template name="i18n.jquery">
	<xsl:choose>
	  <xsl:when test="$lang='pt-br'">'pt-BR'</xsl:when>
	  <xsl:when test="$lang='en-us'">''</xsl:when>
	  <xsl:otherwise>'<xsl:value-of select="substring-before($lang, '-')" />'</xsl:otherwise>
	</xsl:choose>
</xsl:template>
