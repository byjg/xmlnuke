<xsl:template match="duallist">
<TABLE>
	   <TR VALIGN="MIDDLE">
	      <TD>
	         <FONT SIZE="-1"><B><xsl:value-of select="leftlist/@caption"/></B></FONT><BR/>
	         <SELECT style="width:200px" multiple="multiple">
	         <xsl:attribute name="SIZE"><xsl:value-of select="leftlist/@size"/></xsl:attribute>
	         <xsl:attribute name="name"><xsl:value-of select="leftlist/@name"/></xsl:attribute>
	         <xsl:attribute name="ID"><xsl:value-of select="leftlist/@name"/></xsl:attribute>
	         	<xsl:for-each select="leftlist/item">
                 	<OPTION>
                 		<xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                 		<xsl:value-of select="@text"/>
                 	</OPTION>
                </xsl:for-each>
	         </SELECT>
	      </TD>
	      <TD>
		<xsl:for-each select="button">
			<xsl:if test="@type='image'">
				<img>
				<xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute>
				<xsl:attribute name="onClick">moveDualList('<xsl:value-of select="@from"/>', '<xsl:value-of select="@to"/>', <xsl:value-of select="@all"/>)</xsl:attribute>
				</img>
			</xsl:if>
			<xsl:if test="@type!='image'">
		 		<input type="button">	
				<xsl:attribute name="onClick">moveDualList('<xsl:value-of select="@from"/>', '<xsl:value-of select="@to"/>', <xsl:value-of select="@all"/>)</xsl:attribute>
				<xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
				</input>
			</xsl:if>
				<BR/>
                </xsl:for-each>
	      </TD>
	      <TD>
	         <FONT SIZE="-1"><B><xsl:value-of select="rightlist/@caption"/></B></FONT><BR/>
	         <SELECT style="width:200px" multiple="multiple">
	         <xsl:attribute name="SIZE"><xsl:value-of select="rightlist/@size"/></xsl:attribute>
	         <xsl:attribute name="name"><xsl:value-of select="rightlist/@name"/></xsl:attribute>
	         <xsl:attribute name="ID"><xsl:value-of select="rightlist/@name"/></xsl:attribute>
	         	<xsl:for-each select="rightlist/item">
                 	<OPTION>
                 		<xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                 		<xsl:value-of select="@text"/>
                 	</OPTION>
                </xsl:for-each>
	         </SELECT>
	      </TD>
	   </TR>
	</TABLE>
	<xsl:value-of select="."/>
</xsl:template>
