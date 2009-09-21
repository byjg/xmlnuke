<script type="text/javascript" src="common/3rdparty/jquery/js/jquery-1.3.2.min.js"></script>
<xsl:if test="//sortablelist or //calendar or //faq or //datetimebox or //uialert">
	<link type="text/css" href="common/3rdparty/jquery/css/smoothness/jquery-ui-1.7.1.custom.css" rel="stylesheet" />	
	<script type="text/javascript" src="common/3rdparty/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>
	<script type="text/javascript" src="common/3rdparty/jquery/js/jquery-ui-i18n.min.js"></script>
	<style type="text/css">
		#ui-datepicker-div { font-size: 0.8em; } 
		#faqaccordion { font-size: 80%; } 
		.ui-state-error, .ui-state-highlight { 
			margin: 0.5em;
		}
		.ui-state-error p, .ui-state-highlight p
		{
			margin: 0.8em 0 0.8em 0;
			line-height: 1.2em;
			padding-bottom: 0px;
		} 
	</style>	
</xsl:if>

<!-- Include specific JavaScript for AutoSuggest  -->
<xsl:if test="//sortablelist">
	<style type="text/css">
		<xsl:for-each select="//sortablelist"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="@name" />_list</xsl:for-each> { font-size: 80%; list-style-type: none; margin: 0; padding: 0; width: 100%; }
		<xsl:for-each select="//sortablelist"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="@name" />_list</xsl:for-each> li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; height: 1.5em; }
		html>body <xsl:for-each select="//sortablelist"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="@name" />_list</xsl:for-each> li { height: 1.5em; line-height: 1.2em; }
		.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
	</style>
	<script type="text/javascript">
	$(function() {
		$("<xsl:for-each select="//sortablelist"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="@name" />_list</xsl:for-each>").sortable({
			placeholder: 'ui-state-highlight',
			cancel: '.ui-state-disabled',
			//items: 'li:not(.ui-state-disabled)',
			update: function(event, ui) 
			{ 
				var result = $(this).sortable('toArray');
				var sName = "#" + $(this).attr("id").replace("_list", "");
				var joined = result.join("|");
				$(sName).attr("value", joined); 
				//alert($(sName).attr("value"));
			}
		}).disableSelection();
	});
	</script>
</xsl:if>

<!-- Include specific JavaScript for Calendar element -->
<xsl:if test="//calendar">
	<script type="text/javascript">
	$(function() {
		$.datepicker.setDefaults($.extend({showMonthAfterYear: false}, $.datepicker.regional[<xsl:call-template name="i18n.jquery" />]));
		<xsl:for-each select="//calendar">
		$("#<xsl:value-of select="@name" />").datepicker({
			inline: true,
			defaultDate: new Date ( "<xsl:value-of select="@month"/> 1 <xsl:value-of select="@year"/>" ),
			onSelect: function (event, ui) { $('#<xsl:value-of select="@name" />_dialog').dialog('open'); }
		});
		$('#<xsl:value-of select="@name" />_dialog').dialog({
			autoOpen: false,
			width: 600,
			buttons: {
				"Ok": function() { 
					$(this).dialog("close"); 
				} 
			}
		});
		</xsl:for-each>
	});
	</script>
</xsl:if>

<!-- Include specific JavaScript for FAQ element -->
<xsl:if test="//faq">
	<script type="text/javascript">
		$(function(){
			$("#faqaccordion").accordion({ header: "h5" }); 
		});
	</script>
</xsl:if>

<!-- Include specific JavaScript for DHTMLGoodies Calendar  -->
<xsl:if test="//datetimebox">
	<script type="text/javascript">
		$(function(){
			$.datepicker.setDefaults($.extend({showMonthAfterYear: false}, $.datepicker.regional[<xsl:call-template name="i18n.jquery" />]));
			<xsl:for-each select="//datetimebox">$("#<xsl:value-of select="@name" />").datepicker({
					changeMonth: true,
					changeYear: true,
					stepMonths: 12,
					yearRange: '<xsl:value-of select="@yearmin"/>:<xsl:value-of select="@yearmax"/>'
			});</xsl:for-each>
		});
	</script>
</xsl:if>



<!-- Include specific JavaScript for EditForm element -->
<xsl:if test="//editform">
	<link rel="stylesheet" href="common/styles/editform.css" />
	<script language='JavaScript' src='common/js/editform.js'></script>
	<script language='JavaScript' src='common/js/validate.js'></script>
	<script language='JavaScript'><xsl:attribute name="src">common/js/validate.<xsl:value-of select = "$lang" />.js</xsl:attribute></script>
</xsl:if>


<!-- Include specific JavaScript for EditList element -->
<xsl:if test="//editlist">
	<link href="common/editlist/editlist.css" type="text/css" rel="stylesheet" />
	<script language='JavaScript' src='common/js/editlist.js'></script>
	<script language='JavaScript'><xsl:attribute name="src">common/js/editlist.<xsl:value-of select = "$lang" />.js</xsl:attribute></script>
</xsl:if>


<!-- Include specific JavaScript for DualList element -->
<xsl:if test="//duallist">
  <script language="JavaScript" src="common/duallist/script.js"></script>
</xsl:if>


<!-- Include TinyMCE visual editor if have some Textarea using it -->
<xsl:if test="//memo[@visualedit='true']">
	<script type="text/javascript" src="common/3rdparty/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="common/3rdparty/tiny_mce/tiny_mce_xmlnuke.js"></script>
	<script type="text/javascript">
	<xsl:for-each select="//memo[@visualedit='true']">
		initTinyMCE("<xsl:value-of select="@name"/>", true, "<xsl:value-of select="@visualeditbasehref"/>");
	</xsl:for-each>
	</script>
</xsl:if>


<!-- Include specific JavaScript for TabView element -->
<xsl:if test="//tabview">
   <script type="text/javascript" src="common/3rdparty/tabber/tabber.js"></script>
   <link rel="stylesheet" href="common/3rdparty/tabber/tabber.css" TYPE="text/css" MEDIA="screen" />
   <style type="text/css">.tabber{display:none;}</style>
</xsl:if>

<!-- Include specific JavaScript for WebtoolKit.AIM -->
<xsl:if test="//ajaxcallback">
   <script type="text/javascript" src="common/3rdparty/webtoolkit/webtoolkit.aim.js"></script>
   <xsl:for-each select="//ajaxcallback">
	<script type="text/javascript">
		function startCallback<xsl:value-of select="@id" />() {
			area = document.getElementById('<xsl:value-of select="@id" />');
		   	var bgImage = "url('common/imgs/ajax-loader.gif')";
		 	area.style.backgroundImage = bgImage;
		 	area.style.backgroundRepeat = "no-repeat";
		 	area.style.backgroundPosition = "center center";
			area.style.display = "block";
			area.innerHTML = "<br/><br/><br/>";
			return true;
		}

		function completeCallback<xsl:value-of select="@id" />(response) {
		 	area.style.backgroundImage = "";
			document.getElementById('<xsl:value-of select="@id" />').innerHTML = response;
		}
	</script>
   </xsl:for-each>
</xsl:if>


<!-- Include specific JavaScript for Container (Div)  -->
<xsl:if test="//container">
    <style>
    #objectfoo { width: 100%; height: 100%; }
    </style>
	<script language="javascript" type="text/javascript"><![CDATA[
		function loadUrl(containerId, url)
		{
			var container = document.getElementById(containerId);
			if (container)
			{
				container.style.display = "block";
				container.innerHTML = '<'+'object id="objectfoo" name="objectfoo" type="text/html" data="'+url+'"><\/object>';
			}
			else
			{
				alert("Container '" + containerId + "' does not exists");
			}
		}
	]]></script>
</xsl:if>


<!-- Include specific JavaScript for AutoSuggest  -->
<xsl:if test="//textbox[@autosuggesturl]">
	<script type="text/javascript" src="common/3rdparty/autosuggest/js/bsn.AutoSuggest_c_2.0.js"></script>
	<link rel="stylesheet" href="common/3rdparty/autosuggest/css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
</xsl:if>

<!-- Include specific JavaScript for Poll  -->
<xsl:if test="//poll">
	<link rel="stylesheet" href="common/styles/poll.css" type="text/css" media="screen" charset="utf-8" />
</xsl:if>


<!-- Include specific JavaScript for TreeView element -->
<xsl:if test="//treeview">
   <script type="text/javascript" language="javascript" src="common/3rdparty/treeview/treeview.js"></script>
   <link rel="stylesheet" href="common/3rdparty/treeview/treeview.css" TYPE="text/css" MEDIA="screen" />
</xsl:if>

<!-- Include specific JavaScript for Flash element -->
<xsl:if test="//flash">
	<script language="javascript">AC_FL_RunContent = 0;</script>
	<script language="javascript"> DetectFlashVer = 0; </script>
	<script src="common/js/AC_RunActiveContent.js" language="javascript"></script>
</xsl:if>

<!-- Include specific JavaScript for Select List as Image element -->
<xsl:if test="//select[@imagelist]">
	<style>
		.image-select {background-color: none; padding: 1px; margin: 1px}
		.selected {background-color: black};
	</style>
	<script language="javascript" src="common/3rdparty/jquery/plugin/imageselect.js"></script>
	<script language="javascript"> 
	<xsl:for-each select="//select[@imagelist]">
	$(function() {
		$('#<xsl:value-of select="@name" />').imageSelect({
			thumbnailWidth: '<xsl:value-of select="@thumbnailwidth" />',
			notFoundImage: '<xsl:value-of select="@notfoundimage" />',
			emptyText: '<xsl:value-of select="@noimage" />'
		});
	});
	</xsl:for-each>
	</script>
</xsl:if>


<!-- Include specific JavaScript for Masked Text box -->
<xsl:if test="//textbox[@mask]">
	<script language="javascript" src="common/3rdparty/jquery/plugin/jquery.maskedinput-1.2.2.min.js"></script>
</xsl:if>


<!-- Include specific JavaScript for UI Alert -->
<xsl:if test="//uialert">
	<script type="text/javascript">
	$(function() {
		<xsl:for-each select="//uialert[contains(@type, 'dialog')]">
		$("#<xsl:value-of select="@name" />_fire").click(function() { $("#<xsl:value-of select="@name" />").dialog('open'); });
			
		$("#<xsl:value-of select="@name" />").dialog({
			<xsl:if test="contains(@type, 'modal')">modal: true,</xsl:if>
			<xsl:if test="@openaction">autoOpen: false,</xsl:if>
			<xsl:if test="button">
			buttons: {
				<xsl:for-each select="button">
				'<xsl:value-of select="@text" />': function() {
					<xsl:value-of select="text()" />
				}<xsl:if test="position()!=last()">,</xsl:if>
				</xsl:for-each>
			},
			</xsl:if>
			<xsl:if test="@autohide">
			open: function(event, ui) {
				setTimeout(function() {
				     $("#<xsl:value-of select="@name" />").dialog("close");
				}, <xsl:value-of select="@autohide" />);
			},			
			</xsl:if>
			<xsl:if test="@width">width: <xsl:value-of select="@width" />,</xsl:if>
			<xsl:if test="@height">height: <xsl:value-of select="@height" />,</xsl:if>
			bgiframe: <xsl:if test="not(@type='dialog')">true</xsl:if><xsl:if test="@type='dialog'">false</xsl:if>
		});
		</xsl:for-each>

		<xsl:for-each select="//uialert[contains(@type, 'box') and @autohide]">
		setTimeout(function() {
		     $("#<xsl:value-of select="@name" />").hide('blind', {}, 500);
		}, <xsl:value-of select="@autohide" />);
		</xsl:for-each>
	});
	</script>
</xsl:if>

