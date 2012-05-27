<script type="text/javascript" src="common/3rdparty/jquery/jquery-1.6.1.min.js" />
<script type="text/javascript" src="common/js/xmlnuke.js"></script>
<link type="text/css" href="common/xmlnuke.css" rel="stylesheet" />

<!-- UI JavaScript Object -->
<xsl:if test="//sortablelist or //calendar or //faq or //datetimebox or //uialert or //mediagallery or //tabview or //progressbar or //textbox[@autosuggesturl]">

	<link type="text/css" href="common/3rdparty/jquery/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" />
	<script type="text/javascript" src="common/3rdparty/jquery/jquery-ui-1.8.13.custom.min.js" />
	<script type="text/javascript" src="common/3rdparty/jquery/jquery-ui-i18n.min.js" />
</xsl:if>

<!-- Include specific JavaScript for Sortable  -->
<xsl:if test="//sortablelist">
	<style type="text/css">
		<xsl:for-each select="//sortablelist/column"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="parent::node()/@name" />_<xsl:value-of select="@id" />_list</xsl:for-each> { font-size: 80%; list-style-type: none; margin: 0; padding: 0; }
		<xsl:for-each select="//sortablelist/column"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="parent::node()/@name" />_<xsl:value-of select="@id" />_list  .ui-state-default</xsl:for-each> { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; height: 1.5em; }
		html>body <xsl:for-each select="//sortablelist/column"><xsl:if test="not(position()=1)">,</xsl:if>#<xsl:value-of select="parent::node()/@name" />_<xsl:value-of select="@id" />_list .ui-state-default</xsl:for-each> { height: 1.5em; line-height: 1.2em; }
		.ui-state-highlight { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
		.ui-state-highlight * { visibility: hidden; }

		.portlet { margin: 0 1em 1em 0; }
		.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
		.portlet-header .ui-icon { float: right; }
		.portlet-content { padding: 0.4em; }
	</style>
	<script type="text/javascript">
	function updateSortable(event, ui)
	{
		var result = $(this).sortable('toArray');
		var sName = "#" + $(this).attr("id").replace("_list", "");
		var joined = result.join("|");
		$(sName).attr("value", joined);
		//alert($(sName).attr("value"));
	}

	$(function() {
		$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
			.find(".portlet-header")
				.addClass("ui-widget-header ui-corner-all")
				.prepend('<span class="ui-icon ui-icon-minusthick"></span>')
				.end()
			.find(".portlet-content");

		$(".portlet-header .ui-icon").click(function() {
			$(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
			$(this).parents(".portlet:first").find(".portlet-content").toggle();
		});

		<xsl:for-each select="//sortablelist/column">
		$("#<xsl:value-of select="parent::node()/@name" />_<xsl:value-of select="@id" />_list").sortable({
			placeholder: 'ui-state-highlight',
			cancel: '.ui-state-disabled',
			connectWith: '.<xsl:value-of select="parent::node()/@connectkey" />',
			//items: 'li:not(.ui-state-disabled)',
			update: updateSortable
		}).disableSelection();
		</xsl:for-each>
	});
	</script>
</xsl:if>

<!-- Include specific JavaScript for Calendar element -->
<xsl:if test="//calendar">
	<style>
		.bgcaltype   { background-color: black !important; }
		
		.bgcaltype1  { background-color: #33E6D9 !important; }
		.bgcaltype2  { background-color: #FFA600 !important; }
		.bgcaltype3  { background-color: #A64B00 !important; }
		.bgcaltype4  { background-color: #8CCCF2 !important; }
		.bgcaltype5  { background-color: #ED0000 !important; }
		.bgcaltype6  { background-color: #A6FF00 !important; }
		.bgcaltype7  { background-color: #8C19A3 !important; }
		.bgcaltype8  { background-color: #00AAE6 !important; }
		.bgcaltype9  { background-color: #5CF22C !important; }
		.bgcaltype10 { background-color: #FF6600 !important; }
		.bgcaltype11 { background-color: #806600 !important; }
		.bgcaltype12 { background-color: #0057D9 !important; }
		
		.bgcaltype13 { background-color: #8DD3C7 !important; }
		.bgcaltype14 { background-color: #FFFFB3 !important; }
		.bgcaltype15 { background-color: #BEBADA !important; }
		.bgcaltype16 { background-color: #FB8072 !important; }
		.bgcaltype17 { background-color: #80B1D3 !important; }
		.bgcaltype18 { background-color: #FDB462 !important; }
		.bgcaltype19 { background-color: #B3DE69 !important; }
		.bgcaltype20 { background-color: #FCCDE5 !important; }
		.bgcaltype21 { background-color: #D9D9D9 !important; }
		.bgcaltype22 { background-color: #BC80BD !important; }
		.bgcaltype23 { background-color: #CCEBC5 !important; }
		.bgcaltype24 { background-color: #FFED6F !important; }
	</style>
	<script type="text/javascript">
	$(function() {
		$.datepicker.setDefaults($.extend({showMonthAfterYear: false}, $.datepicker.regional[<xsl:call-template name="i18n.jquery" />]));
		<xsl:for-each select="//calendar">
		$("#<xsl:value-of select="@name" />").datepicker({
			inline: true,
			defaultDate: new Date ( "<xsl:value-of select="@month"/> 1 <xsl:value-of select="@year"/>" ),
			onSelect: function (event, ui) { $('#<xsl:value-of select="@name" />_dialog').dialog('open'); }, 
			beforeShowDay: function (date) { return colorDayCalendar(date, calDays_<xsl:value-of select="@name" />); }
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
	
	function colorDayCalendar(date, calDays)
	{
		return [true, calDays['day_' + date.getDate()]];
	}
	</script>
</xsl:if>

<!-- Include specific JavaScript for FAQ element -->
<xsl:if test="//faq">
	<script type="text/javascript">
		$(function(){
			$(".faqaccordion").accordion({ header: "h5" });
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
	<script language="javascript" src="common/3rdparty/jquery/plugin/jquery.contextmenu.r2.js" />
	<script language='JavaScript' src='common/js/editlist.js?20100502'></script>
	<script language='JavaScript'><xsl:attribute name="src">common/js/editlist.<xsl:value-of select = "$lang" />.js</xsl:attribute></script>
</xsl:if>


<!-- Include specific JavaScript for DualList element -->
<xsl:if test="//duallist">
  <script language="JavaScript" src="common/duallist/script.js"></script>
</xsl:if>


<!-- Include TinyMCE visual editor if have some Textarea using it -->
<xsl:if test="//memo[@visualedit='true']">
	<script type="text/javascript" src="common/3rdparty/tiny_mce/jquery.tinymce.js"></script>
	<script type="text/javascript" src="common/3rdparty/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="common/3rdparty/tiny_mce/tiny_mce_xmlnuke.js"></script>
	<script type="text/javascript">
	<xsl:for-each select="//memo[@visualedit='true']">
		initTinyMCE("<xsl:value-of select="@name"/>", true, "<xsl:value-of select="@visualeditbasehref"/>");
	</xsl:for-each>
	</script>
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
	<style>
	.ui-autocomplete {
		max-height: 200px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
	}
	/* IE 6 doesn't support max-height
	 * we use height instead, but this forces the menu to always be this tall
	 */
	* html .ui-autocomplete {
		height: 200px;
	}
	</style>
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
	<script src="common/3rdparty/jquery/plugin/jquery.flash.js" language="javascript"></script>
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
	<script language="javascript" src="common/3rdparty/jquery/plugin/jquery.maskedinput-1.3.min.js"></script>
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

<!-- Include specific JavaScript for pretty Photo -->
<xsl:if test="//mediagallery">
	<script type="text/javascript" src="common/3rdparty/jquery/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="common/3rdparty/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="common/3rdparty/jquery/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
	<style>
		.clearfix {
			display: block;
		}
		.clearfix li {
			display: block;
			float: left;
			margin: 0;
			padding: 0;
		}
		.clearfix:after {
			clear: both;
			content: ".";
			display: block;
			height: 0;
			visibility: hidden;
		}
	</style>
	
	<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
		$(".mediagallery a[rel^='mgalxml']").fancybox();
	});	

	<xsl:for-each select="//mediagallery[@api='true' and @name]">
	function open_<xsl:value-of select="@name" />()
	{
		/*
		images = [<xsl:for-each select="mediaitem">'<xsl:value-of select="@src" />'<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>];
		titles = [<xsl:for-each select="mediaitem">'<xsl:value-of select="@title" />'<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>];
		descriptions = [<xsl:for-each select="mediaitem">'<xsl:value-of select="@caption" />'<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>];
		$.prettyPhoto.open(images,titles,descriptions);
		*/
	}
	</xsl:for-each>
	</script>
</xsl:if>


<!-- Include specific JavaScript for ProgressBar -->
<xsl:if test="//progressbar">
	<script type="text/javascript">
	$(function() {
		<xsl:for-each select="//progressbar">
		$("#<xsl:value-of select="@name" />").progressbar({ value: <xsl:value-of select="@value" /> });
		</xsl:for-each>
	});

	function getProgressValue(name) { return $( "#" + name ).progressbar( "option", "value" ); }
	function setProgressValue(name, value) { $( "#" + name ).progressbar( "option", "value", value ); }
	function addProgressValue(name, value) { $( "#" + name ).progressbar( "option", "value", getProgressValue(name, value) ); }
	</script>
</xsl:if>
