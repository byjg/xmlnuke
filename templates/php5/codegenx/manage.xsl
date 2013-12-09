<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet [
	<!ENTITY lower "abcdefghijklmnopqrstuvwxyz">
	<!ENTITY upper "ABCDEFGHIJKLMNOPQRSTUVWXYZ">
]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:msxsl="urn:schemas-microsoft-com:xslt" exclude-result-prefixes="msxsl">
	<xsl:param name="tablename"></xsl:param>
	<xsl:param name="package"></xsl:param>
	<xsl:param name="include"></xsl:param>
	<xsl:param name="extends"></xsl:param>


	<xsl:output method="text"/>

	<xsl:template match="database">
		<xsl:for-each select="table[@name=$tablename]">
			<xsl:variable name="ClassName">
				<xsl:call-template name="upperCase">
					<xsl:with-param name="textToTransform" select="@name" />
				</xsl:call-template>
			</xsl:variable>&lt;?php
//==============================================================================
//===   manage<xsl:value-of select="$tablename" />.class.php
//=== 
//=== Essa classe é gerada automaticamente. 
//==============================================================================


ModuleFactory::IncludePhp("<xsl:value-of select="$include" />", "_includelist.php");
//{@@@[//CustomInclude
//CustomInclude]}@@@

class <xsl:value-of select="$ClassName" /> extends <xsl:value-of select="$extends" />
{

	public function useCache()
	{
		return false;
	}
	public function  getAccessLevel()
    {
          return AccessLevel::OnlyAdmin;
    }

    public function getRole()
    {
           //return SrzdRoles::ANYONE;
    }

	public function CreatePage()
	{
		$this->WordCollection();
		$this->createXmlnukeDocument("TITLE");

		$op = $this->_context->ContextValue("op");
		if ($op == "1")
		{
			//
		}
		else
		{
			$this->Edit<xsl:value-of select="$ClassName" />();
		}

		return $this->defaultXmlnukeDocument;
	}


	/**
	 * Usar o XmlnukeCrud para gerenciar a edição de uma tabela.
	 */
	public function Edit<xsl:value-of select="$ClassName" />()
	{
		$block = new XmlBlockCollection($this->_words->Value("TITLE"), BlockPosition::Center);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
		
		//$this->setTitlePage($this->myWords->Value("TITLE"));
		//$this->setHelp($this->myWords->Value("DESCRIPTION"));

		//$this->addMenuOption($this->myWords->Value("SOMEOPTION"), "module:" . $this->_moduleName . "?op=1", null);
		//$this->addMenuOption($this->myWords->Value("SOMEOPTION2"), "module:" . $this->_moduleName . "?op=2" , null);

		$uiedit = new <xsl:value-of select="$ClassName" />UIEdit($this->_context);

		$fields = new CrudFieldCollection();
		<xsl:for-each select="column">
			<xsl:variable name="FieldName">
				<xsl:call-template name="upperCase">
					<xsl:with-param name="textToTransform" select="@name" />
				</xsl:call-template>
			</xsl:variable>
			<xsl:variable name="FieldUpper">
				<xsl:value-of select="translate(@name, '&lower;', '&upper;')" />
			</xsl:variable>			
		$field = $uiedit->crudField<xsl:value-of select="$FieldName" />();
		$fields->addCrudField($field);
		</xsl:for-each>
		
		$crud = new XmlnukeCrudDB
		(
			$this->_context, 
			$fields, 
			$this->_words->Value("PROCESSPAGE_TITLE"), 
			"module:" . $this->_moduleName, 
			null, 
			'<xsl:value-of select="@name" />', 
			<xsl:value-of select="$ClassName" />DB::DataBaseName()
		);
		$crud->setPermissions(true, false, true, true);
		$block->addXmlnukeObject($process);
	}
}
		</xsl:for-each>
	</xsl:template>

	
	
	
	
	
	
	
	
	
	
	
	
	<!--
	Functions
	-->
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
