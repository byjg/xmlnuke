<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"/>
	<xsl:template match="/">
		<HTML>
			<head>
				<meta name="author" content="Haran" />
				<link rel="stylesheet" type="text/css" href="common/styles/purplehaze.css" title="Purple Haze Stylesheet" />

				<title>Purple Haze</title>
				<xmlnuke-htmlheader />
			</head>

			<body>
				<div id="top"></div>
			  
				<!-- ###### Header ###### -->

				<div id="header">
				<span class="headerTitle">Welcome to Purple Haze!</span>
				<div class="headerLinks">
					<a href="http://www.oswd.org/">OSWD</a>|
					<a href="http://www.oswd.org/browse.php">Designs</a>|
					<span>Purple Haze</span>
				</div>
				</div>

				<!-- ###### Side Boxes and Body Text ###### -->

				<xmlnuke-purplehazeblocklr />
			    
				<!-- ###### Footer ###### -->

				<div><div id="footer">  <!-- NB: outer <div> required for correct rendering in IE -->
				<a class="footerImg" href="http://validator.w3.org/check/referer">
					Valid XHTML 1.0 Strict</a>
				<a class="footerImg" href="http://jigsaw.w3.org/css-validator/validator">
					Valid CSS</a>

				<div>
					<strong>Author: </strong>
					<a class="footerCol2" href="mailto:haran@wiredcity.com.au"
					title="Email author">haran</a>
				</div>

				<div>
					<strong>XMLNuke: </strong>
					<a class="footerCol2" href="mailto:joao@xmlnuke.com"
					title="Email author">Joao Gilberto Magalhaes</a>
				</div>

				<div>
					<strong>URI: </strong>
					<span class="footerCol2">http://www.oswd.org/design/xxx/purplehaze/index.html</span>
				</div>

				<div>
					<strong>Modified: </strong>
					<span class="footerCol2">2003-03-16 23:57 -0300</span>
				</div>
				</div></div>

			</body>


		</HTML>
	</xsl:template>
	<xmlnuke-htmlbody/>
</xsl:stylesheet>
