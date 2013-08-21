<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
 xmlns:msxsl="urn:schemas-microsoft-com:xslt"
 xmlns:user="urn:my-scripts"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" > 
 
<xsl:template match="/">
  <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:html="http://www.w3.org/TR/REC-html40">

    <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
       <Alignment ss:Vertical="Bottom"/>
       <Borders/>
       <Font ss:FontName="Arial" ss:Size="10" ss:Color="#000000"/>
       <Interior/>
       <NumberFormat/>
       <Protection/>
      </Style>
      <Style ss:ID="sHeader">
        <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
        <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font x:Family="Swiss" ss:Size="11" ss:Color="#000000" ss:Bold="1"/>
        <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="sData">
        <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
        <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
    </Styles>


		 <Worksheet ss:Name="XMLNuke">
		  <Table x:FullColumns="1" x:FullRows="1"><!-- ss:ExpandedColumnCount="3" ss:ExpandedRowCount="2"  -->
		   <Column ss:AutoFitWidth="1" ss:Width="103.5" ss:Span="255"/>
		   <Row>
			  <xsl:for-each select="//row[1]/field">
			    <Cell ss:StyleID="sHeader"><Data ss:Type="String"><xsl:value-of select="@name" /></Data></Cell>
			  </xsl:for-each> 
		   </Row>
		   
		   <xsl:for-each select="//row">
		   <Row ss:AutoFitHeight="1" >
		   	  <xsl:for-each select="field">
		   	  	<xsl:if test="string(number(.))='NaN'">
			    	<Cell ss:StyleID="sData"><Data ss:Type="String"><xsl:value-of select="." /></Data></Cell>
			    </xsl:if>
		   	  	<xsl:if test="string(number(.))!='NaN'">
			    	<Cell ss:StyleID="sData"><Data ss:Type="Number"><xsl:value-of select="." /></Data></Cell>
			    </xsl:if>
			  </xsl:for-each> 
		   </Row>
		  </xsl:for-each>
		  </Table>
		  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
		   <PageSetup>
		    <Header x:Margin="0.49212598499999999"/>
		    <Footer x:Margin="0.49212598499999999"/>
		    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
		     x:Right="0.78740157499999996" x:Top="0.984251969"/>
		   </PageSetup>
		   <Selected/>
		   <ProtectObjects>False</ProtectObjects>
		   <ProtectScenarios>False</ProtectScenarios>
		  </WorksheetOptions>
		 </Worksheet>
		</Workbook>
   </xsl:template>
</xsl:stylesheet>
