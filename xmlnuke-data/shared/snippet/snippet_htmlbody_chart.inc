<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->

<!-- CHART -->
<!-- The implementation of this code is in snippet_htmlheader.inc -->

<xsl:template match="ChartObject">
	<div id="{Id}" style="width: {Width}px; height: {Height}px;"></div>
	
	<script>
      google.setOnLoadCallback(drawChart_<xsl:value-of select="Id" />);
      function drawChart_<xsl:value-of select="Id" />() {
        var data = google.visualization.arrayToDataTable([
          <xsl:for-each select="Serie/row">
			<xsl:if test="position()=1">
			[ <xsl:for-each select="field[contains(@name, 'data_')]"><xsl:value-of select="."/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each> ],
			</xsl:if>
			<xsl:if test="not(position()=1)">
			[ <xsl:for-each select="field[contains(@name, 'data_')]"><xsl:value-of select="."/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each> ],
			</xsl:if>
		  </xsl:for-each>
        ]);

		<xsl:variable name="chartType"><xsl:choose>
			<xsl:when test="ChartType='pie'">PieChart</xsl:when>
			<xsl:when test="ChartType='donut'">PieChart</xsl:when>
			<xsl:when test="ChartType='line'">LineChart</xsl:when>
			<xsl:when test="ChartType='bar'">BarChart</xsl:when>
			<xsl:when test="ChartType='column'">ColumnChart</xsl:when>
			<xsl:when test="ChartType='area'">AreaChart</xsl:when>
			<xsl:when test="ChartType='table'">Table</xsl:when>
			<xsl:otherwise>ColumnChart</xsl:otherwise>
		</xsl:choose></xsl:variable>

		var minValueX = <xsl:if test="MinValueX"><xsl:value-of select="MinValueX" /></xsl:if><xsl:if test="not(MinValueX)">undefined</xsl:if>;
		var minValueY = <xsl:if test="MinValueY"><xsl:value-of select="MinValueY" /></xsl:if><xsl:if test="not(MinValueY)">undefined</xsl:if>;
		var maxValueX = <xsl:if test="MaxValueX"><xsl:value-of select="MaxValueX" /></xsl:if><xsl:if test="not(MaxValueX)">undefined</xsl:if>;
		var maxValueY = <xsl:if test="MaxValueY"><xsl:value-of select="MaxValueY" /></xsl:if><xsl:if test="not(MaxValueY)">undefined</xsl:if>;

        var options = {
          title: '<xsl:value-of select="Title"/>',
          vAxis: {title: '<xsl:value-of select="TitleVertical"/>',  titleTextStyle: {color: 'black'}, minValue: minValueY, maxValue: maxValueY},
          hAxis: {title: '<xsl:value-of select="TitleHorizontal"/>',  titleTextStyle: {color: 'black'}, minValue: minValueX, maxValue: maxValueX},
		  <xsl:if test="ChartType='donut'">pieHole: 0.4,</xsl:if>
		  is3D: <xsl:if test="Is3d=1">true</xsl:if><xsl:if test="not(Is3d=1)">false</xsl:if>,
        };

        var chart = new google.visualization.<xsl:value-of select="$chartType"/>(document.getElementById('<xsl:value-of select="Id" />'));
		//google.visualization.events.addListener(chart, 'ready', function () {
		//  png = chart.getImageURI();
		//  console.log(png);
		//});
		chart.draw(data, options);
      }
	</script>

</xsl:template>

