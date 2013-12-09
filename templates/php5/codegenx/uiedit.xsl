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
//===   <xsl:value-of select="$tablename" />uiedit.class.php
//=== 
//=== Essa classe é gerada automaticamente. 
//==============================================================================


//{@@@[//CustomInclude
//CustomInclude]}@@@

class <xsl:value-of select="$ClassName" />UIEdit extends <xsl:value-of select="$package" />BaseUIEdit
{
	// &lt;editor-fold defaultstate="collapsed" desc="Properties"&gt;
	/**
	 * @var Context
	 */
	protected $_context;

	/**
	 * @var LanguageCollection
	 */
	protected $_myWords;

	/**
	 * @var <xsl:value-of select="$ClassName" />Model
	 */
	protected $_model;
	
	protected $_readOnlyDelimiter = "  ";
	
	protected $_dateFormat = "";
	// &lt;/editor-fold&gt;

	/**
	 * @param Context $context
	 * @param LanguageCollection $myWords
	 * @param <xsl:value-of select="$ClassName" />Model $model
	 */
	public function __construct($model = null, $myWords = null)
	{
		$this->_context = Context::getInstance();
		$this->_dateFormat = $this->_context->Language()->getDateFormat();
		
		if ($myWords == null)
		{
			$myWords = LanguageFactory::GetLanguageCollection($this->_context, LanguageFileTypes::OBJECT, "table_<xsl:value-of select="$tablename" />");
		}
		$this->_myWords = $myWords;
		if ($model == null)
		{
			$model = new <xsl:value-of select="$ClassName" />Model();
		}
		$this->_model = $model;
	}
	
	// &lt;editor-fold defaultstate="collapsed" desc="Default Getters and Setters"&gt;
	/**
	 * Devolve a coleção de linguagem utilizada no sistema.
	 * @return LanguageCollection
	 */
	public function getLanguageCollection()
	{
		return $this->_myWords;
	}

	/**
	 * Obtem o delimitador de Readonly
	 * @return string
	 */
	public function getReadonlyDelimiter()
	{
		return $this->_readOnlyDelimiter;
	}

	/**
	 * Define o Delimitador de Readonly
	 */
	public function setReadonlyDelimiter($left, $right)
	{
		return $this->_readOnlyDelimiter = $left . $right;
	}

	/**
	 * Obtem o delimitador de Readonly
	 * @return DATEFORMAT
	 */
	public function getDateFormat()
	{
		return $this->_dateFormat;
	}

	/**
	 * Define o Delimitador de Readonly
	 * @param DATEFORMAT $value
	 */
	public function setDateFormat($value)
	{
		return $this->_dateFormat = $value;
	}
	// &lt;/editor-fold&gt;

	<xsl:for-each select="column">
		<xsl:variable name="FieldName">
			<xsl:call-template name="upperCase">
				<xsl:with-param name="textToTransform" select="@name" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:variable name="FieldUpper">
			<xsl:value-of select="translate(@name, '&lower;', '&upper;')" />
		</xsl:variable>
		<xsl:variable name="ViewSize">
		<xsl:choose>
			<xsl:when test="contains(@type, 'date') or contains(@type, 'DATE')">10</xsl:when>
			<xsl:when test="@size and not(contains(@size, ','))">
				<xsl:if test="@size &lt; 40"><xsl:value-of select="@size" /></xsl:if>
				<xsl:if test="not(@size &lt; 40)">40</xsl:if>
			</xsl:when>
			<xsl:otherwise>5</xsl:otherwise>
		</xsl:choose>
		</xsl:variable>
	// &lt;editor-fold defaultstate="collapsed" desc="Métodos de <xsl:value-of select="@name" />"&gt;
	/**
	 * Obter um TextBox de <xsl:value-of select="$FieldName" />
	 * @param bool $readonly
	 * @param integer $size
	 * @return XmlInputTextBox
	 */
	public function textBox<xsl:value-of select="$FieldName" />($readonly = false, $size = <xsl:value-of select="$ViewSize" />)
	{
		$obj = new XmlInputTextBox($this->_myWords->Value("<xsl:value-of select="$FieldUpper" />"), <xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />, $this->_model->get<xsl:value-of select="$FieldName" />(), $size);
		$obj->setReadOnlyDelimeters($this->getReadonlyDelimiter());
		<xsl:if test="@required='true'">
		$obj->setRequired(true);
		</xsl:if>
		$obj->setReadOnly($readonly);
		<xsl:if test="@size and not(contains(@size, ','))">
		$obj->setMaxLength(<xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />_SIZE);
		</xsl:if>
		<xsl:choose>
			<xsl:when test="contains(@type, 'int') or contains(@type, 'decimal') or contains(@type, 'INT') or contains(@type, 'DECIMAL')">$obj->setDataType(INPUTTYPE::NUMBER);</xsl:when>
			<xsl:when test="contains(@type, 'date') or contains(@type, 'DATE')">$obj->setDataType(INPUTTYPE::DATE);</xsl:when>
			<xsl:otherwise>$obj->setDataType(INPUTTYPE::TEXT);</xsl:otherwise>
		</xsl:choose>

		return $obj;
	}	
	<xsl:if test="contains(@type, 'date') or contains(@type, 'DATE')">
	/**
	 * Obter um InputDateTime de <xsl:value-of select="$FieldName" />
	 * @param bool $time
	 * @return XmlInputDateTime
	 */
	public function dateBox<xsl:value-of select="$FieldName" />($time = false)
	{
		$value = explode(" ", $this->_model->get<xsl:value-of select="$FieldName" />());
		$dateValue = $value[0];
		$timeValue = ( (count($value) > 1) &amp;&amp; $time ? $value[1] : "" );
		
		$obj = new XmlInputDateTime($this->_myWords->Value("<xsl:value-of select="$FieldUpper" />"), <xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />, $this->getDateFormat(), $time, $dateValue, $timeValue);

		return $obj;
	}
	</xsl:if>
	<xsl:if test="@type='text'">
	/**
	 * Obter um Memo de <xsl:value-of select="$FieldName" />
	 * @param bool $readonly
	 * @return XmlInputMemo
	 */
	public function memo<xsl:value-of select="$FieldName" />($readonly = false)
	{
		$obj = new XmlInputMemo($this->_myWords->Value("<xsl:value-of select="$FieldUpper" />"), <xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />, $this->_model->get<xsl:value-of select="$FieldName" />());
		$obj->setReadOnly($readonly);

		return $obj;
	}
	</xsl:if>
	/**
	 * Obter um CrudField de <xsl:value-of select="$FieldName" />
	 * @param bool $visible
	 * @param bool $required
	 * @param integer $viewSize
	 * @return CrudField
	 */
	public function crudField<xsl:value-of select="$FieldName" />($visible = true, $required = <xsl:if test="@required='true'">true</xsl:if><xsl:if test="not(@required='true')">false</xsl:if>, $viewSize = <xsl:value-of select="$ViewSize" />)
	{
		$field = CrudField::FactoryMinimal("<xsl:value-of select="@name" />", $this->_myWords->Value("<xsl:value-of select="$FieldUpper" />"), $viewSize, $visible, $required);
		<xsl:if test="@primaryKey and @primaryKey='true'">$field->key = true;
		</xsl:if>
		<xsl:if test="@autoIncrement and @autoIncrement='true'">$field->editable = false;
		</xsl:if>
		<xsl:if test="not(@autoIncrement and @autoIncrement='true')">$field->editable = true;
		</xsl:if>
		<xsl:if test="@size and not(contains(@size, ','))">
		$field->maxLength = <xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />_SIZE;
		</xsl:if>
		<xsl:choose>
			<xsl:when test="contains(@type, 'int') or contains(@type, 'decimal') or contains(@type, 'INT') or contains(@type, 'DECIMAL')">$field->dataType = INPUTTYPE::NUMBER;</xsl:when>
			<xsl:when test="contains(@type, 'date') or contains(@type, 'DATE')">$field->dataType = INPUTTYPE::DATE;</xsl:when>
			<xsl:otherwise>$field->dataType = INPUTTYPE::TEXT;</xsl:otherwise>
		</xsl:choose>
		return $field;
	}

	/**
	 * Obter um EditListField de <xsl:value-of select="$FieldName" />
	 * @return EditListField
	 */
	public function editListField<xsl:value-of select="$FieldName" />()
	{
		$field = new EditListField();
		$field->fieldData = <xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />;
		$field->editlistName = $this->_myWords->Value("<xsl:value-of select="$FieldUpper" />");
		return $field;
	}
	// &lt;/editor-fold&gt;
	</xsl:for-each>



	// &lt;editor-fold defaultstate="collapsed" desc="easyList"&gt;
	<xsl:for-each select="foreign-key">
		<xsl:variable name="FieldName">
			<xsl:call-template name="upperCase">
				<xsl:with-param name="textToTransform" select="reference/@local" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:variable name="FieldUpper">
			<xsl:value-of select="translate(reference/@local, '&lower;', '&upper;')" />
		</xsl:variable>
		<xsl:variable name="Field">
			<xsl:value-of select="reference/@local" />
		</xsl:variable>
	/**
	 * Obter um EasyList de <xsl:value-of select="$FieldName" />
	 * @param array $array
	 * @param bool $readonly
	 * @param EasyListType $listType
	 * @return XmlEasyList
	 */
	public function easyList<xsl:value-of select="$FieldName" />($array, $readonly = false, $listType = null)
	{
		if ($listType == null)
		{
			$listType = EasyListType::SELECTLIST;
		}
		$obj = new XmlEasyList($listType, <xsl:value-of select="$ClassName" />Model::<xsl:value-of select="$FieldUpper" />, $this->_myWords->Value("<xsl:value-of select="$FieldUpper" />"), $array, $this->_model->get<xsl:value-of select="$FieldName" />());
		$obj->setReadOnlyDelimeters($this->getReadonlyDelimiter());
		$obj->setReadOnly($readonly);
		<xsl:if test="//table[@name=$tablename]/column[@name=$Field]/@required='true'">$obj->setRequired(true);</xsl:if>
		return $obj;
	}
	</xsl:for-each>
	// &lt;/editor-fold&gt;

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
