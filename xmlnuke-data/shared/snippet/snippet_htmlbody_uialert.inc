<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- UIALERT -->

<xsl:template match="uialert[contains(@type, 'dialog')]">
  
  <xsl:if test="@openaction='button'"><input id="{@name}_fire" type="button" value="{@openactiontext}" /></xsl:if>
  <xsl:if test="@openaction='url'"><a id="{@name}_fire"><xsl:value-of select="@openactiontext" /></a></xsl:if>
  <xsl:if test="@openaction='image'"><img id="{@name}_fire" src="{@openactiontext}" /></xsl:if>
  
  <div id="{@name}" title="{@title}">
  	<xsl:apply-templates select="body" />
  </div>
  
</xsl:template>



<xsl:template match="uialert[@type='boxinfo']">
  
		<div id="{@name}" class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="padding: 0pt 0.7em;"> 
				<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
			  	<xsl:apply-templates select="body" />
			  	</p>
			</div>
		</div>
		
</xsl:template>



<xsl:template match="uialert[@type='boxalert']">

		<div id="{@name}" class="ui-widget">
			<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;"> 
				<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span> 
			  	<xsl:apply-templates select="body" />
			  	</p>
			</div>
		</div>
  
</xsl:template>