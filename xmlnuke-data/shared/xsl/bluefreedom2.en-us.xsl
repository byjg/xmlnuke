<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   <xsl:output method="xml" />
   <xsl:template match="/">
	<html>
		<head>
			<title><xsl:value-of select="page/meta/title" /></title>
			<meta http-equiv="Content-Language" content="English" />
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<link rel="stylesheet" type="text/css" href="common/styles/bluefreedom2.css" media="screen" />
			<xmlnuke-htmlheader />
		</head>
		<body>
			<div id="wrap">

				<div id="top"></div>

				<div id="content">

					<div class="header">
					<h1><a href="engine:xmlnuke">XMLNuke</a></h1>
					<h2><xsl:value-of select="page/meta/title" /></h2>
					</div>

					<div class="breadcrumbs">
						<a href="#">Home</a> &#183; <xsl:value-of select="page/meta/title" />
					</div>

					<div class="middle">
						<xmlnuke-blockcentercss2 />			
					</div>
		
					<div class="right">
						<xmlnuke-menucss2 />
						<xmlnuke-blockleftcss2 />
						<xmlnuke-blockrightcss2 />
					</div>

					<div id="clear"></div>

				</div>

				<div id="bottom"></div>

			</div>

			<div id="footer">
				Design by <a href="http://www.minimalistic-design.net">Minimalistic Design</a>
			</div>
		</body>
	</html>
   </xsl:template>
   <xmlnuke-htmlbody />
</xsl:stylesheet>
