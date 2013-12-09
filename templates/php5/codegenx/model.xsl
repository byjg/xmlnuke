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
			</xsl:variable>
			<xsl:variable name="PackageName">
				<xsl:call-template name="upperCase">
					<xsl:with-param name="textToTransform" select="$package" />
				</xsl:call-template>
			</xsl:variable>&lt;?php
//==============================================================================
//===   <xsl:value-of select="$tablename" />model.class.php
//=== 
//=== Essa classe é gerada automaticamente. 
//==============================================================================

//{@@@[//CustomInclude
//CustomInclude]}@@@

/**
 * @Xmlnuke:NodeName <xsl:value-of select="$tablename" />
 */
class <xsl:value-of select="$ClassName" />Model extends <xsl:value-of select="$package" />BaseModel
{

	const __TABLENAME__ = '<xsl:value-of select="$tablename" />';

	//-- ---------------------------------------------

	<xsl:for-each select="column">
	protected $_<xsl:value-of select="@name" /> = '';
	</xsl:for-each>

	//-- ---------------------------------------------

	<xsl:for-each select="column">
	const <xsl:value-of select="translate(@name, '&lower;', '&upper;')" /> = '<xsl:value-of select='@name' />';
	</xsl:for-each>

	//-- ---------------------------------------------

	<xsl:for-each select="column"><xsl:variable name="field"><xsl:call-template name="upperCase"><xsl:with-param name="textToTransform" select="@name" /></xsl:call-template></xsl:variable>
	/**
	 * Getter Method
	 * <xsl:value-of select="@description" />
	 * return <xsl:value-of select="@type" />
	 */
	public function get<xsl:value-of select="$field" />()
	{
		return $this->_<xsl:value-of select="@name" />;
	}

	/**
	 * Setter Method
	 * <xsl:value-of select="@description" />
	 * @param <xsl:value-of select="@type" /> $value
	 */
	public function set<xsl:value-of select="$field" />($value)
	{
		$this->_<xsl:value-of select="@name" /> = $value;
	}
	</xsl:for-each>

	//-- ---------------------------------------------

	<xsl:for-each select="column[(contains(@type, 'char') or contains(@type, 'CHAR')) and @size!='MAX']">
	const <xsl:value-of select="translate(@name, '&lower;', '&upper;')" />_SIZE = <xsl:value-of select="@size" />;</xsl:for-each>

	// -----------------------------------------------
	// Put Bellow your personalized code. 
	// -----------------------------------------------

	//{@@@[//CustomValues
	//CustomValues]}@@@

}
		</xsl:for-each>
	</xsl:template>

	
	
	
	
	
	
	
	
	
	
	
	
	<!--
	Functions
	-->
	<xsl:template name="convert_data_type">
		<xsl:param name="sqltype"/>
		<xsl:choose>
			<xsl:when test="contains($sqltype, 'nvarchar')"> string </xsl:when>
			<xsl:when test="contains($sqltype, 'char')"> string </xsl:when>
			<xsl:when test="contains($sqltype, 'datetime')"> DateTime? </xsl:when>
			<xsl:when test="contains($sqltype, 'decimal')"> decimal? </xsl:when>
			<xsl:when test="contains($sqltype, 'xml')"> string </xsl:when>
			<xsl:when test="contains($sqltype, 'binary')"> System.IO.MemoryStream </xsl:when>
			<xsl:otherwise> <xsl:value-of select="$sqltype"/>? </xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="convert_data_type2">
		<xsl:param name="sqltype"/>
		<xsl:choose>
			<xsl:when test="contains($sqltype, 'nvarchar')">string</xsl:when>
			<xsl:when test="contains($sqltype, 'char')">string</xsl:when>
			<xsl:when test="contains($sqltype, 'datetime')">DateTime</xsl:when>
			<xsl:when test="contains($sqltype, 'decimal')">Decimal</xsl:when>
			<xsl:when test="contains($sqltype, 'xml')">string</xsl:when>
			<xsl:when test="contains($sqltype, 'binary')">System.IO.MemoryStream</xsl:when>
			<xsl:otherwise><xsl:value-of select="$sqltype"/></xsl:otherwise>
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
