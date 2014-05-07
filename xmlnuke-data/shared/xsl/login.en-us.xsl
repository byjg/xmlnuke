<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

   <xsl:output method="xml"
		omit-xml-declaration="yes"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
		indent="yes"/>

	<xsl:template match="/">


<html>

	<head>
		<xmlnuke-htmlheader />
		<style>
	body {
		background: #222 url(http://cssdeck.com/uploads/media/items/9/9mLae5p.png) repeat top left;
		padding: 1px;
	}

	#login {
		width: 440px;
		position: relative;
		margin: 50px auto;
	}

	h2 {
		color: #ededed;
		font: 28px/18px "Segoe UI";
		position: relative;
		text-align: center;
		margin-top: 30px;
	}

	.boxCont {
		width: 400px;
		padding: 15px;
		border: 5px solid #ccc;

		-webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.3), inset 0px 0px 0px 1px white;
		-moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.3), inset 0px 0px 0px 1px white;
		box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.3), inset 0px 0px 0px 1px white;

		-webkit-border-radius: px;
		-moz-border-radius: 4px;
		border-radius: 4px;

		background: #ffffff;
		background: -moz-linear-gradient(top,  #ffffff 0%, #d3efff 100%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#d3efff));
		background: -webkit-linear-gradient(top,  #ffffff 0%,#d3efff 100%);
		background: -o-linear-gradient(top,  #ffffff 0%,#d3efff 100%);
		background: -ms-linear-gradient(top,  #ffffff 0%,#d3efff 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#d3efff',GradientType=0 );
		background: linear-gradient(to bottom,  #ffffff 0%,#d3efff 100%);

		overflow: hidden;
	}

	/* Shadow */
	.boxCont:after {
		content: '';
		position: absolute;
		bottom: -20px;
		left: 0;
		width: 100%;

		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;

		-webkit-box-shadow: 0px 0px 8px 5px rgba(0,0,0,0.5);
		-moz-box-shadow: 0px 0px 8px 5px rgba(0,0,0,0.5);
		box-shadow: 0px 0px 8px 5px rgba(0,0,0,0.5);
	}

	.boxCont div {
		overflow: hidden;
	}

	/* Input */
	input[type="text"], input[type="password"], input[type="image"], .input {
		padding: 14px 10px;
		width: 70%;
		border: 1px solid #ccc;
		display: block;
		float: right;

		background: #d3efff;
		background: -moz-linear-gradient(top,  #d3efff 0%, #ffffff 55%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#d3efff), color-stop(55%,#ffffff));
		background: -webkit-linear-gradient(top,  #d3efff 0%,#ffffff 55%);
		background: -o-linear-gradient(top,  #d3efff 0%,#ffffff 55%);
		background: -ms-linear-gradient(top,  #d3efff 0%,#ffffff 55%);
		background: linear-gradient(top,  #d3efff 0%,#ffffff 55%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#d3efff', endColorstr='#ffffff',GradientType=0 );

		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
	}

	input[type="password"] {
		margin-top: 5px;
	}

	/* Buttons */
	input.btn {
		display: inline-block;
		text-decoration: none;
		font: 14px/18px Arial, sans-serif;
		color: white;
		padding: 12px 40px;
		margin: 15px 0 0;
		float: left;
		cursor: pointer;

		background-color: #0064cd;
		background-repeat: repeat-x;
		background-image: -khtml-gradient(linear, left top, left bottom, from(#049cdb), to(#0064cd));
		background-image: -moz-linear-gradient(top, #049cdb, #0064cd);
		background-image: -ms-linear-gradient(top, #049cdb, #0064cd);
		background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #049cdb), color-stop(100%, #0064cd));
		background-image: -webkit-linear-gradient(top, #049cdb, #0064cd);
		background-image: -o-linear-gradient(top, #049cdb, #0064cd);
		background-image: linear-gradient(to bottom, #049cdb, #0064cd);
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#049cdb', endColorstr='#0064cd', GradientType=0);

		border: 1px solid #ccc;
		border-color: #0064cd #0064cd #003f81;
		border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);

		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;

		-webkit-transition: all .2s ease;
		-moz-transition: all .2s ease;
		-ms-transition: all .2s ease;
		-o-transition: all .2s ease;
		transition: all .2s ease;
	}

	input.btn.right {
		float: right;
	}

	input.btn:hover {
		background-position: 0 -15px;
		text-decoration: none;
	}

	input.btn:active {
		-webkit-box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25),0 1px 2px rgba(0, 0, 0, 0.05);
		-moz-box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25),0 1px 2px rgba(0, 0, 0, 0.05);
		box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25),0 1px 2px rgba(0, 0, 0, 0.05);
	}

	a#forgotpass {
		color: #ccc;
		text-decoration: none;
		font: 12px/18px Arial, sans-serif;
		margin-top: 20px;
		display: block;
	}

	label {
		float: left;
		font: 14px/18px Arial, sans-serif;
		color: #333;
		padding: 16px 0 0 0;
	}

	.ui-widget {
		font-size: 0.8em;
		margin-bottom: 15px;
	}
	</style>
</head>
	<body>
		<div id="login">
			<h2><xsl:value-of select="page/meta/title" /></h2>
			<form class="boxCont" method="POST">
				<xmlnuke-blockcentercss2 />
			</form>

			<xsl:if test="//Login/CanRetrievePassword='true'">
			<a href="module:Xmlnuke.Login?action=action.FORGOTPASSWORD&amp;ReturnUrl={//Login/ReturnUrl}" id="forgotpass"><xsl:value-of select="//l10n/LOGINFORGOTMESSAGE" /></a>
			</xsl:if>
		</div>
	</body>
</html>

	</xsl:template>

	<!-- -->

	<xsl:template match="Login[not(Action)]">
		<input type="hidden" id="Action" name="Action" value="{NextAction}" />
		<div>
			<label for="userName"><xsl:value-of select="//l10n/LABELUSERNAME" /></label>
			<input id="userName" type="text" name="Username" value="{Username}" placeholder="{//l10n/PLACEHOLDERUSERNAME}" />
		</div>

		<div>
			<label for="password"><xsl:value-of select="//l10n/LABELPASSWORD" /></label>
			<input id="password" type="password" name="password" placeholder="{//l10n/PLACEHOLDERPASSWORD}" />
		</div>

		<div>
			<input type="submit" id="signIn" name="signIn" value="{//l10n/TXT_LOGIN}" class="btn left" />
			<xsl:if test="CanRegister='true'">
			<input type="button" id="signUp" name="signUp" value="{//l10n/CREATEUSERBUTTON}" class="btn right"
				onclick="$('#Action').val('action.NEWUSER'); $('.boxCont').submit();" />
			</xsl:if>
		</div>
	</xsl:template>


	<!-- -->

	<xsl:template match="Login[Action='action.FORGOTPASSWORD']">
		<input type="hidden" name="Action" value="{NextAction}" />
		<div>
			<label for="Email"><xsl:value-of select="//l10n/LABEL_EMAIL" /></label>
			<input id="Email" type="text" name="Email" value="{Email}" placeholder="{//l10n/PLACEHOLDEREMAIL}" />
		</div>

		<div>
			<input type="submit" id="retrieve" name="retrieve" value="{//l10n/FORGOTPASSBUTTON}" class="btn left" />
		</div>
	</xsl:template>

	<!-- -->

	<xsl:template match="Login[Action='action.NEWUSER']">
		<input type="hidden" name="Action" value="{NextAction}" />
		<div>
			<label for="Username"><xsl:value-of select="//l10n/LABEL_LOGIN" /></label>
			<input id="Username" type="text" name="Username" value="{Username}" placeholder="{//l10n/PLACEHOLDERUSERNAME}" />
		</div>
		<div>
			<label for="Name"><xsl:value-of select="//l10n/LABEL_NAME" /></label>
			<input id="Name" type="text" name="Name" value="{Name}" placeholder="{//l10n/PLACEHOLDERENAME}" />
		</div>
		<div>
			<label for="Email"><xsl:value-of select="//l10n/LABEL_EMAIL" /></label>
			<input id="Email" type="text" name="Email" value="{Email}" placeholder="{//l10n/PLACEHOLDEREMAIL}" />
		</div>
		<div>
			<label for="password"><xsl:value-of select="//l10n/LABELPASSWORD" /></label>
			<span class="input"><xsl:value-of select="//l10n/CREATEUSERPASSWORDMSG" /></span>
		</div>
		<div>
			<label for="Captcha"><xsl:value-of select="//l10n/CAPTCHALABEL" /></label>
			<img class="input" src="imagevalidate.php" />
			<input id="imagevalidate" type="text" name="imagevalidate" value="" placeholder="{//l10n/CAPTCHAPLACEHOLDER}" />
		</div>

		<div>
			<input type="submit" id="create" name="create" value="{//l10n/CREATEUSERBUTTON}" class="btn left" />
		</div>
	</xsl:template>

	<!-- -->

	<xsl:template match="Login[Action='action.RESETPASSWORD']">
		<input type="hidden" name="Action" value="{NextAction}" />
		<input type="hidden" name="ResetToken" value="{ResetToken}" />
		<input type="hidden" name="Username" value="{Username}" />
		<input type="hidden" name="ReturnUrl" value="{ReturnUrl}" />

		<div>
			<label for="password"><xsl:value-of select="//l10n/LABELUSERNAME" /></label>
			<span class="input"><xsl:value-of select="Username" /></span>
		</div>

		<div>
			<label for="password"><xsl:value-of select="//l10n/LABELPASSWORD" /></label>
			<input id="password" type="password" name="password" placeholder="{//l10n/PLACEHOLDERPASSWORD}" />
		</div>

		<div>
			<label for="password"><xsl:value-of select="//l10n/LABELRETYPEPASSWORD" /></label>
			<input id="password2" type="password" name="password2" placeholder="{//l10n/PLACEHOLDERRETYPEPASSWORD}" />
		</div>

		<div>
			<input type="submit" id="create" name="create" value="{//l10n/RESETPASSBUTTON}" class="btn left" />
		</div>
	</xsl:template>

	<!-- -->

	<xmlnuke-htmlbody />

</xsl:stylesheet>

