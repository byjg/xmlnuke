<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet [
	<!ENTITY lower "abcdefghijklmnopqrstuvwxyz">
	<!ENTITY upper "ABCDEFGHIJKLMNOPQRSTUVWXYZ">
]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:msxsl="urn:schemas-microsoft-com:xslt" exclude-result-prefixes="msxsl">
	<xsl:param name="tablename"></xsl:param>
	<xsl:param name="package"></xsl:param>


	<xsl:output method="text"/>

	<xsl:template match="database">
		<xsl:for-each select="table[@name=$tablename]">
			<xsl:variable name="ClassName">
				<xsl:call-template name="upperCase">
					<xsl:with-param name="textToTransform" select="@name" />
				</xsl:call-template>
			</xsl:variable>&lt;?xml version="1.0"?&gt;
&lt;anydataset&gt;
   &lt;row&gt;
      &lt;field name="LANGUAGE"&gt;en-us&lt;/field&gt;
      &lt;field name="TABLENAME"&gt;<xsl:value-of select="$tablename" />&lt;/field&gt;
	<xsl:for-each select="column">
		<xsl:variable name="FieldName">
			<xsl:call-template name="upperCase">
				<xsl:with-param name="textToTransform" select="@name" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:variable name="FieldUpper">
			<xsl:value-of select="translate(@name, '&lower;', '&upper;')" />
		</xsl:variable>&lt;field name="<xsl:value-of select="$FieldUpper" />"&gt;<xsl:value-of select="$FieldName" />&lt;/field&gt;
	</xsl:for-each>
   &lt;/row&gt;
   &lt;row&gt;
      &lt;field name="LANGUAGE"&gt;pt-br&lt;/field&gt;
      &lt;field name="TABLENAME"&gt;<xsl:value-of select="$tablename" />&lt;/field&gt;
	<xsl:for-each select="column">
		<xsl:variable name="FieldName">
			<xsl:call-template name="upperCase">
				<xsl:with-param name="textToTransform" select="@name" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:variable name="FieldUpper">
			<xsl:value-of select="translate(@name, '&lower;', '&upper;')" />
		</xsl:variable>&lt;field name="<xsl:value-of select="$FieldUpper" />"&gt;<xsl:value-of select="$FieldName" />&lt;/field&gt;
	</xsl:for-each>
   &lt;/row&gt;
&lt;/anydataset&gt;

		</xsl:for-each>
	</xsl:template>




	
	
	
	
	
	
	
	
	
	
	<!--
	Functions
	-->
	<xsl:template name="convert_data_type">
		<xsl:param name="sqltype"/>
		<xsl:choose>
			<xsl:when test="contains($sqltype, 'nvarchar')">string</xsl:when>
			<xsl:when test="contains($sqltype, 'char')">string</xsl:when>
			<xsl:when test="contains($sqltype, 'datetime')">DateTime</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$sqltype"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="convert_data_type2">
		<xsl:param name="sqltype"/>
		<xsl:choose>
			<xsl:when test="contains($sqltype, 'nvarchar')">String</xsl:when>
			<xsl:when test="contains($sqltype, 'char')">String</xsl:when>
			<xsl:when test="contains($sqltype, 'datetime')">DateTime</xsl:when>
			<xsl:when test="contains($sqltype, 'decimal')">Decimal</xsl:when>
			<xsl:when test="contains($sqltype, 'xml')">String</xsl:when>
			<xsl:when test="contains($sqltype, 'binary')">__ERROR__</xsl:when>
			<xsl:when test="contains($sqltype, 'int')">Int32</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$sqltype"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="upperCase">
		<xsl:param name="textToTransform" />
		<xsl:variable name="head">
			<xsl:choose>
				<xsl:when test="contains($textToTransform, '_')">
					<xsl:value-of select="substring-before($textToTransform, '_')" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$textToTransform" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="tail" select="substring-after($textToTransform, '_')" />
		<xsl:variable name="firstTransform"	select="concat(translate(substring($head, 1, 1),'&lower;', '&upper;'),substring($head, 2))" />
		<xsl:choose>
			<xsl:when test="$tail">
				<xsl:value-of select="$firstTransform" />
				<xsl:call-template name="upperCase">
					<xsl:with-param name="textToTransform" select="$tail" />
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$firstTransform" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
</xsl:stylesheet>
