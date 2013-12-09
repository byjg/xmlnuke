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
			</xsl:variable>&lt;?php
//==============================================================================
//===   <xsl:value-of select="$tablename" />db.class.php
//=== 
//=== Essa classe é gerada automaticamente. 
//==============================================================================


//{@@@[//CustomInclude
//CustomInclude]}@@@

class <xsl:value-of select="$ClassName" />DB extends <xsl:value-of select="$package" />BaseDBAccess
{

	/**
	 * Construtor
	 * @param Context $context
	 */
	public function __construct()
	{
		parent::__construct(Context::getInstance());
	}
	
	////// Primary Keys ///////////////////////////////////////////////
	
	/**
	 * Obter um <xsl:value-of select="$ClassName" />Model pela chave primária
	 * <xsl:for-each select="column[@primaryKey='true']">
	 * @param <xsl:value-of select="@type"/> $<xsl:value-of select="@name"/></xsl:for-each>
	 * @return <xsl:value-of select="$ClassName" />Model
	 */
	public function obterPorId(<xsl:for-each select="column[@primaryKey='true']"><xsl:if test="position()!=1">, </xsl:if>$<xsl:value-of select="@name"/></xsl:for-each>)
	{
		$model = new <xsl:value-of select="$ClassName" />Model();
		
		$sql  = "select * from " . <xsl:value-of select="$ClassName" />Model::__TABLENAME__;
		$sql .= " where ";
		<xsl:for-each select="column[@primaryKey='true']">
		$sql .= "  <xsl:if test="position()!=1"> and </xsl:if> <xsl:value-of select="@name"/> = [[<xsl:value-of select="@name"/>]] ";</xsl:for-each>

		$param = array();
		<xsl:for-each select="column[@primaryKey='true']">
		$param['<xsl:value-of select="@name"/>'] = $<xsl:value-of select="@name"/>;</xsl:for-each>

		$it = $this->getIterator($sql, $param);		
		$model->bindIterator($it);
		
		return $model;
	}
	
	
	/**
	 * Obter Todos os Registros
	 * @returns IIterator
	 */
	public function obterTodos()
	{
		$sql = $this->baseSQLQuery();
		return $this->getIterator($sql, $param);
	}

	////// Protected Functions ///////////////////////////////////////////////

	/**
	 * Obtém o SQL base que será utilizado nas demais consultas
 	 * @returns string
	 */
	protected function baseSQLQuery()
	{
		$sql  = "select * from " . <xsl:value-of select="$ClassName" />Model::__TABLENAME__;
		//{@@@[//CustomBaseInnerJoin
		//CustomBaseInnerJoin]}@@@
		$sql .= " where 1=1 ";
		//{@@@[//CustomBaseWhere
		//CustomBaseWhere]}@@@

		return $sql;
	}

	////// Foreign Keys ///////////////////////////////////////////////

	<xsl:for-each select="foreign-key">
		<xsl:variable name="ForeignClass">
			<xsl:call-template name="upperCase">
				<xsl:with-param name="textToTransform" select="@foreignTable" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:variable name="foreignTable">
			<xsl:value-of select="@foreignTable"/>
		</xsl:variable>

	/**
	 * Obter um coleção de <xsl:value-of select="$ClassName" />Model por <xsl:value-of select="@local"/>
	 * <xsl:for-each select="reference"><xsl:variable name="foreign"><xsl:value-of select="@foreign"/></xsl:variable>
	 * @param <xsl:value-of select="//table[@name=$foreignTable]/column[@name=$foreign]/@type"/> $<xsl:value-of select="@local"/></xsl:for-each>
	 * @return IIterator
	 */
	public function obterPor<xsl:for-each select="reference">
		<xsl:call-template name="upperCase">
			<xsl:with-param name="textToTransform" select="@local" />
		</xsl:call-template>
	</xsl:for-each>(<xsl:for-each select="reference"><xsl:if test="position()!=1">, </xsl:if>$<xsl:value-of select="@local"/></xsl:for-each>)
	{
		$param = array();

		$sql = $this->baseSQLQuery();
		<xsl:for-each select="reference">
		if (!empty($<xsl:value-of select="@local"/>))
		{
			$sql .= "   and <xsl:value-of select="@local"/> = [[<xsl:value-of select="@local"/>]] ";
			$param['<xsl:value-of select="@local"/>'] = $<xsl:value-of select="@local"/>;
		}
		</xsl:for-each>
		$it = $this->getIterator($sql, $param);
		
		return $it;
	}
	</xsl:for-each>

	////// Alternate Keys ///////////////////////////////////////////////

	<xsl:for-each select="unique">
	/**
	 * Obter um <xsl:value-of select="$ClassName" />Model sua chave alternativa (AK)
	 * <xsl:for-each select="unique-column"><xsl:variable name="column"><xsl:value-of select="@name"/></xsl:variable>
	 * @param <xsl:value-of select="//table[@name=$tablename]/column[@name=$column]/@type"/> $<xsl:value-of select="@name"/></xsl:for-each>
	 * @return <xsl:value-of select="$ClassName" />Model
	 */
	public function obterPor<xsl:for-each select="unique-column">
		<xsl:call-template name="upperCase">
			<xsl:with-param name="textToTransform" select="@name" />
		</xsl:call-template>
	</xsl:for-each>(<xsl:for-each select="unique-column"><xsl:if test="position()!=1">, </xsl:if>$<xsl:value-of select="@name"/></xsl:for-each>)
	{
		$param = array();

		$sql = $this->baseSQLQuery();
		<xsl:for-each select="unique-column">
		if (!empty($<xsl:value-of select="@name"/>))
		{
			$sql .= "   and <xsl:value-of select="@name"/> = [[<xsl:value-of select="@name"/>]] ";
			$param['<xsl:value-of select="@name"/>'] = $<xsl:value-of select="@name"/>;
		}
		</xsl:for-each>
		$it = $this->getIterator($sql, $param);	
		$model = new <xsl:value-of select="$ClassName" />Model();
		$model->bindIterator($it);
		
		return $model;
	}
	</xsl:for-each>

	////// Indexes ///////////////////////////////////////////////

	<xsl:for-each select="index">
	/**
	 * Obter uma coleção através da consulta nos seus índices
	 * <xsl:for-each select="index-column"><xsl:variable name="column"><xsl:value-of select="@name"/></xsl:variable>
	 * @param <xsl:value-of select="//table[@name=$tablename]/column[@name=$column]/@type"/> $<xsl:value-of select="@name"/></xsl:for-each>
	 * @return IIterator
	 */
	public function obterPor<xsl:for-each select="index-column">
		<xsl:call-template name="upperCase">
			<xsl:with-param name="textToTransform" select="@name" />
		</xsl:call-template>
	</xsl:for-each>(<xsl:for-each select="index-column"><xsl:if test="position()!=1">, </xsl:if>$<xsl:value-of select="@name"/></xsl:for-each>)
	{
		$param = array();

		$sql = $this->baseSQLQuery();
		<xsl:for-each select="index-column">
		if (!empty($<xsl:value-of select="@name"/>))
		{
			$sql .= "   and <xsl:value-of select="@name"/> = [[<xsl:value-of select="@name"/>]] ";
			$param['<xsl:value-of select="@name"/>'] = $<xsl:value-of select="@name"/>;
		}
		</xsl:for-each>
		$it = $this->getIterator($sql, $param);		
		
		return $it;
	}
	</xsl:for-each>

	////// Gravar ///////////////////////////////////////////////

	/**
	 * Gravar um <xsl:value-of select="$ClassName" />Model
	 *
	 * @param <xsl:value-of select="$ClassName" />Model $model
	 */
	public function gravar<xsl:value-of select="$ClassName" />($model)
	{
		$param = array();
		$campos = array();
		<xsl:for-each select="column[not(@autoIncrement='true')]">
		$campos[<xsl:value-of select="$ClassName" />Model::<xsl:value-of select="translate(@name, '&lower;', '&upper;')" />] = array(<xsl:call-template name="convert_data_type"><xsl:with-param name="sqltype" select="@type" /></xsl:call-template>, $model->get<xsl:call-template name="upperCase"><xsl:with-param name="textToTransform" select="@name" /></xsl:call-template>());</xsl:for-each>
		
		$isInsert = <xsl:for-each select="column[@primaryKey='true']"><xsl:if test="position()!=1"> || </xsl:if>($model->get<xsl:call-template name="upperCase"><xsl:with-param name="textToTransform" select="@name" /></xsl:call-template>() == "")<xsl:if test="position()=last()">;</xsl:if></xsl:for-each>

		//{@@@[//CustomPreProcess
		//CustomPreProcess]}@@@

		// Insert
		if ($isInsert)
		{
			//{@@@[//BeforeInsert
			//BeforeInsert]}@@@

			$sql = $this->getSQLHelper()->generateSQL(<xsl:value-of select="$ClassName" />Model::__TABLENAME__, $campos, $param, SQLType::SQL_INSERT, "");
			return $this->executeSql($sql, $param, true);
		}
		// Atualiza
		else 
		{
			$filter = <xsl:for-each select="column[@primaryKey='true']"><xsl:if test="position()!=1"> and " . </xsl:if><xsl:value-of select="$ClassName" />Model::<xsl:value-of select="translate(@name, '&lower;', '&upper;')" /> . " = [[id<xsl:value-of select="position()" />]]</xsl:for-each> ";
			<xsl:for-each select="column[@primaryKey='true']">
			$param["id<xsl:value-of select="position()" />"] = $model->get<xsl:call-template name="upperCase"><xsl:with-param name="textToTransform" select="@name" /></xsl:call-template>();</xsl:for-each>

			//{@@@[//BeforeUpdate
			//BeforeUpdate]}@@@

			$sql = $this->getSQLHelper()->generateSQL(<xsl:value-of select="$ClassName" />Model::__TABLENAME__, $campos, $param, SQLType::SQL_UPDATE, $filter);
			$this->executeSQL($sql, $param);
			return true;
		}
	}

	// Falta -- Obter Todos + Array

	////// CUSTOM CODE ///////////////////////////////////////////////
	
	//{@@@[//CustomCode
	//CustomCode]}@@@

}
		</xsl:for-each>
	</xsl:template>

	
	
	
	
	
	
	
	
	
	
	
	
	<!--
	Functions
	-->
	<xsl:template name="convert_data_type">
		<xsl:param name="sqltype"/>
		<xsl:choose>
			<xsl:when test="contains($sqltype, 'int')">SQLFieldType::Number</xsl:when>
			<xsl:when test="contains($sqltype, 'number')">SQLFieldType::Number</xsl:when>
			<xsl:when test="contains($sqltype, 'decimal')">SQLFieldType::Number</xsl:when>
			<xsl:when test="contains($sqltype, 'numeric')">SQLFieldType::Number</xsl:when>
			<!--<xsl:when test="contains($sqltype, 'date')">SQLFieldType::Date</xsl:when>-->
			<xsl:otherwise>SQLFieldType::Text</xsl:otherwise>
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
