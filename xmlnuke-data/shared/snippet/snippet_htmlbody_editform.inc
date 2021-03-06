<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!--- EDITFORM -->

<xsl:template match="editform">
  <xsl:if test="@jsvalidate">
	<script language='JavaScript'>
	function <xsl:value-of select="@name"/>_checksubmit(){
		errorslist = document.getElementById("<xsl:value-of select="@name"/>_errorslist");
		errorslist.style.display = "none";
		SEPARADORDECIMAL = '<xsl:choose><xsl:when test="@decimalseparator"><xsl:value-of select="@decimalseparator"/></xsl:when><xsl:otherwise>.</xsl:otherwise></xsl:choose>';
		FORMATODATA = <xsl:choose><xsl:when test="@dateformat"><xsl:value-of select="@dateformat"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>;
		objForm = document.<xsl:value-of select="@name"/>;
		msgerror = "";
		<xsl:for-each select="textbox | password | select | inputgroup/textbox | inputgroup/password | datetimebox ">
		msgerror = msgerror + fn_checkthis('<xsl:choose><xsl:when test="@description"><xsl:value-of select="@description"/></xsl:when><xsl:otherwise><xsl:value-of select="@caption"/></xsl:otherwise></xsl:choose>',<xsl:choose><xsl:when test="@type"><xsl:value-of select="@type"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>,<xsl:choose><xsl:when test="@required"><xsl:value-of select="@required"/></xsl:when><xsl:otherwise>false</xsl:otherwise></xsl:choose>,objForm.elements['<xsl:value-of select="@name"/>'],'<xsl:value-of select="@minvalue"/>','<xsl:value-of select="@maxvalue"/>')
		<xsl:if test="@customjs">
		msgerror = msgerror + <xsl:value-of select="@customjs"/>(objForm, objForm.elements['<xsl:value-of select="@name"/>'])
		</xsl:if>
		</xsl:for-each>

		if (msgerror.length)
		{
			errorslist.innerHTML = fn_mountmessage(msgerror);
			errorslist.style.display = "block";
			return false;
		}
		else
		{
			fn_disableButton();
			return true;
		}

	}
	</script>
  </xsl:if>
  <form method="post" id="editform">
	<xsl:if test="@action">
		<xsl:attribute name="action"><xsl:value-of select="@action"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="@name">
		<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
		<xsl:attribute name="class"><xsl:value-of select="@name"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="@ENCTYPE">
		<xsl:attribute name="ENCTYPE"><xsl:value-of select="@ENCTYPE"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="@target">
		<xsl:attribute name="target"><xsl:value-of select="@target"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="@customsubmit">
		<xsl:attribute name="onsubmit">return <xsl:value-of select="@customsubmit"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="not(@customsubmit)">
		<xsl:attribute name="onsubmit">return <xsl:value-of select="@name"/>_checksubmit()</xsl:attribute>
	</xsl:if>
	<xsl:if test="@autocomplete">
		<xsl:attribute name="autocomplete"><xsl:value-of select="@autocomplete"/></xsl:attribute>
	</xsl:if>

	<input type="hidden" name="__postback" value="true" />

	<fieldset>
		<legend><xsl:value-of select="@title"/></legend>

		<div class="errorslist">
			<xsl:if test="@name">
				<xsl:attribute name="id"><xsl:value-of select="@name"/>_errorslist</xsl:attribute>
			</xsl:if>
			<!--
			<p><em>Error List</em></p>
			<ul><li>aaa</li><li>aaa</li><li>aaa</li></ul>
			-->
		</div>
		<!--<xsl:apply-templates select="hidden|caption|label|textbox|password|memo|checkbox|radiobox|select|file|buttons" />-->
		<dl>
		<xsl:apply-templates />
		</dl>
		<div class="clearer"></div>
       </fieldset>

  </form>
  <xsl:for-each select="textbox[@autosuggesturl]">
	<script type="text/javascript">
	$(function() {
		var cache_<xsl:value-of select="@name" /> = {}, lastXhr_<xsl:value-of select="@name" />;
		
		$("#<xsl:value-of select="@name" />").autocomplete({
			minLength: 2,
			delay: 600,
			source: function( request, response ) {
				var term = request.term;
				if (term in cache_<xsl:value-of select="@name" />) {
					response( cache_<xsl:value-of select="@name" />[term] );
					return
				}
				
				lastXhr_<xsl:value-of select="@name" /> = $.getJSON( "<xsl:value-of select="@autosuggesturl" /><xsl:value-of select="@autosuggestparamreq" />=" + request.term, request, function( data, status, xhr ) {
					var resultData = [].concat( data.<xsl:value-of select="@autosuggest_array" /> );
					var processedData = [];

					for (var key in resultData)
					{
						if (resultData[key]==undefined)
							continue;
						
						processedData = processedData.concat(
							[{
								"id": resultData[key]["<xsl:value-of select="@autosuggest_objid" />"],
								"value": resultData[key]["<xsl:value-of select="@autosuggest_objvalue" />"],
								"info": resultData[key]["<xsl:value-of select="@autosuggest_objinfo" />"]
							}]
						);
					}

					cache_<xsl:value-of select="@name" />[ term ] = processedData;

					if ( xhr === lastXhr_<xsl:value-of select="@name" /> ) {
						response( processedData );
					}
				});
			}<xsl:if test="@autosuggestcallback">, 
			select: function (event, ui) {
				<xsl:value-of select="@autosuggestcallback" />;
				return false;
			}
			</xsl:if>
		})
		.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + item.value.replace($("#<xsl:value-of select="@name" />").val(), "<b>" + $("#<xsl:value-of select="@name" />").val() + "</b>") + "</a>" )
				.appendTo( ul );
		};
	});
	fn_addEvent("<xsl:value-of select="@name" />", "focus", function () { this.select(); });
	</script>
  </xsl:for-each>

  <xsl:if test="textbox[@mask]|inputgroup/textbox[@mask]">
    <script type="text/javascript">
	  $(function() {
	  <xsl:for-each select="textbox[@mask]|inputgroup/textbox[@mask]">
		$("#<xsl:value-of select="@name" />").mask("<xsl:value-of select="@mask" />", {placeholder:" "});
	  </xsl:for-each>
	  });
	</script>
  </xsl:if>


</xsl:template>


<xsl:template name="generic.label">
	<xsl:param name="caption"/>
	<xsl:param name="name"/>
	<xsl:param name="required"/>

	<label>
		<xsl:if test="$name and $name!=''"><xsl:attribute name="for"><xsl:value-of select="$name"/></xsl:attribute></xsl:if>
		<xsl:value-of select="$caption"/><xsl:if test="$required='true'">*</xsl:if>
	</label>
</xsl:template>

<xsl:template name="generic.input">
	<xsl:param name="name"/>
	<xsl:param name="type"/>
	<xsl:param name="value"/>
	<xsl:param name="size"/>
	<xsl:param name="maxlength"/>
	<xsl:param name="selected"/>
	<xsl:param name="autocomplete"/>

	<xsl:if test="$type='label'">
		<span id="form_label">
			<xsl:apply-templates/>
			<xsl:value-of select="@value"/><xsl:if test="@value=''">&#160;</xsl:if>
		</span>
	</xsl:if>
	<xsl:if test="$type='select'">
                <select>
			<xsl:if test="$size!=''"><xsl:attribute name="size"><xsl:value-of select="@size"/></xsl:attribute></xsl:if>
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute><xsl:apply-templates select="option" />
		</select>
	</xsl:if>
	<xsl:if test="$type!='label' and $type!='select'">
	<input>
		<xsl:attribute name="type">
			<xsl:if test="$type!='radiobox' and $type!='textbox' "><xsl:value-of select="$type"/></xsl:if>
			<xsl:if test="$type='textbox'">text</xsl:if>
			<xsl:if test="$type='radiobox'">radio</xsl:if>
		</xsl:attribute>
		<xsl:if test="$selected='yes'"><xsl:attribute name="checked"/></xsl:if>
		<xsl:attribute name="name"><xsl:value-of select="$name"/></xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="$name"/></xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="$value"/></xsl:attribute>

		<xsl:if test="$size and $size!=''"><xsl:attribute name="size"><xsl:value-of select="$size"/></xsl:attribute></xsl:if>
		<xsl:if test="$maxlength and $maxlength!=''"><xsl:attribute name="maxlength"><xsl:value-of select="$maxlength"/></xsl:attribute></xsl:if>
		<xsl:if test="$autocomplete and $autocomplete!=''"><xsl:attribute name="autocomplete"><xsl:value-of select="$autocomplete"/></xsl:attribute></xsl:if>
	</input>
	</xsl:if>
</xsl:template>


<xsl:template match="hidden"><input type="hidden"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute></input></xsl:template>

<xsl:template match="caption">
	<dt class="caption" >
		<b><xsl:value-of select="text()"/></b>
	</dt>
</xsl:template>

<!-- Generic Templates -->
<xsl:template match="inputgroup|checkbox|radiobox|textbox|password|file|label|select">

	<!-- PUT CANHIDE OBJECT -->
	<xsl:if test="name()='inputgroup' and @name and @canhide='true'">
		<dt class="group" >
			<a><xsl:attribute name="href">javascript:void showHideGeneric('<xsl:value-of select="@name"/>', 'auto')</xsl:attribute>
			<img border="0" >
				<xsl:attribute name="src">common/imgs/<xsl:if test="@visible='false'">faqopen.gif</xsl:if><xsl:if test="not(@visible='false')">faqclose.gif</xsl:if></xsl:attribute>
				<xsl:attribute name="id">I_<xsl:value-of select="@name"/></xsl:attribute></img>
			</a>
			<xsl:value-of select="@caption" />
		</dt>
	</xsl:if>

	<!-- INPUTGROUP and BREAKLINE -->
	<xsl:if test="name()='inputgroup' and @breakline='true'">
		<xsl:apply-templates />
	</xsl:if>

	<!-- INPUT OBJECTS -->
	<xsl:if test="name()!='inputgroup'">
			<!-- textbox|password|file|label -->
			<dt>
				<xsl:if test="name()='checkbox' or name()='radiobox'">
					&#160;
				</xsl:if>
				
				<xsl:if test="name()!='checkbox' and name()!='radiobox' and name()!='inputgroup'">
					<xsl:if test="local-name(parent::node())='inputgroup' and parent::node()/@name"><xsl:attribute name="id"><xsl:value-of select="parent::node()/@name" /></xsl:attribute><xsl:if test="parent::node()/@visible"><xsl:attribute name="style">display:none</xsl:attribute></xsl:if></xsl:if>
					<xsl:call-template name="generic.label">
						<xsl:with-param name="caption"><xsl:value-of select="@caption"/></xsl:with-param>
						<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
						<xsl:with-param name="required"><xsl:value-of select="@required"/></xsl:with-param>
					</xsl:call-template>
				</xsl:if>
			</dt>
			<dd>
				
				<xsl:if test="local-name(parent::node())='inputgroup' and parent::node()/@name"><xsl:attribute name="id"><xsl:value-of select="parent::node()/@name" /></xsl:attribute><xsl:if test="parent::node()/@visible"><xsl:attribute name="style">display:none</xsl:attribute></xsl:if></xsl:if>
				<xsl:call-template name="generic.input">
					<xsl:with-param name="type"><xsl:value-of select="name()"/></xsl:with-param>
					<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
					<xsl:with-param name="value"><xsl:value-of select="@value"/></xsl:with-param>
					<xsl:with-param name="size"><xsl:value-of select="@size"/></xsl:with-param>
					<xsl:with-param name="maxlength"><xsl:value-of select="@maxlength"/></xsl:with-param>
					<xsl:with-param name="selected"><xsl:value-of select="@selected"/></xsl:with-param>
					<xsl:with-param name="autocomplete"><xsl:value-of select="@autocomplete"/></xsl:with-param>
				</xsl:call-template>
				
				<xsl:if test="name()='checkbox' or name()='radiobox'">
					<xsl:call-template name="generic.label">
						<xsl:with-param name="caption"><xsl:value-of select="@caption"/></xsl:with-param>
						<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
						<xsl:with-param name="required"><xsl:value-of select="@required"/></xsl:with-param>
					</xsl:call-template>
				</xsl:if>
			</dd>
		</xsl:if>

	<!-- Dont breakline!! -->
	<xsl:if test="name()='inputgroup' and (@breakline!='true' or not(@breakline))">
		<dt>
			<xsl:if test="@name"><xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute></xsl:if>
			<xsl:call-template name="generic.label">
				<xsl:with-param name="caption"><xsl:value-of select="child::*[1]/@caption"/></xsl:with-param>
				<xsl:with-param name="name"><xsl:value-of select="child::*[1]/@name"/></xsl:with-param>
				<xsl:with-param name="required"><xsl:value-of select="child::*[1]/@required"/></xsl:with-param>
			</xsl:call-template>
		</dt>
		<dd>
			<xsl:if test="@name"><xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute></xsl:if>
			<xsl:for-each select="textbox|password|checkbox|radiobox|file|label|select">
				<xsl:if test="position()!=1">
					<xsl:call-template name="generic.label">
						<xsl:with-param name="caption"><xsl:value-of select="@caption"/></xsl:with-param>
						<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
						<xsl:with-param name="required"><xsl:value-of select="@required"/></xsl:with-param>
					</xsl:call-template>
				</xsl:if>
				<xsl:call-template name="generic.input">
					<xsl:with-param name="type"><xsl:value-of select="name()"/></xsl:with-param>
					<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
					<xsl:with-param name="value"><xsl:value-of select="@value"/></xsl:with-param>
					<xsl:with-param name="size"><xsl:value-of select="@size"/></xsl:with-param>
					<xsl:with-param name="maxlength"><xsl:value-of select="@maxlength"/></xsl:with-param>
					<xsl:with-param name="selected"><xsl:value-of select="@selected"/></xsl:with-param>
					<xsl:with-param name="autocomplete"><xsl:value-of select="@autocomplete"/></xsl:with-param>
				</xsl:call-template>
				<xsl:if test="position()!=last()"> &#160; </xsl:if>
			</xsl:for-each>
			<xsl:for-each select="hidden">
				<input type="hidden"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute></input>
			</xsl:for-each>
		</dd>
	</xsl:if>
</xsl:template>


<!-- Specific Templates -->
<xsl:template match="memo">
	<dt>
		<xsl:call-template name="generic.label">
			<xsl:with-param name="caption"><xsl:value-of select="@caption"/></xsl:with-param>
			<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
			<xsl:with-param name="required"><xsl:value-of select="@required"/></xsl:with-param>
		</xsl:call-template>
	</dt>
	<dd>
		<textarea><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="cols"><xsl:value-of select="@cols"/></xsl:attribute><xsl:attribute name="rows"><xsl:value-of select="@rows"/></xsl:attribute><xsl:attribute name="wrap"><xsl:value-of select="@wrap"/></xsl:attribute><xsl:if test="@maxlength"><xsl:attribute name="onKeyDown">return fn_countChars(this, <xsl:value-of select="@maxlength"/>, event, 'lbl_<xsl:value-of select="@name"/>');</xsl:attribute></xsl:if><xsl:value-of select="text()"/></textarea>
		<xsl:if test="@maxlength">
			<br/>
			<script>document.write(MSG_MEMO_CHARLEFT1)</script>
			<span><xsl:attribute name="id">lbl_<xsl:value-of select="@name" /></xsl:attribute><xsl:value-of select="@maxlength"/></span>
			<script>document.write(MSG_MEMO_CHARLEFT2)</script>
		</xsl:if>
	</dd>
</xsl:template>

<xsl:template match="option">
	<option><xsl:if test="@selected='yes'"><xsl:attribute name="selected"/></xsl:if><xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute><xsl:apply-templates /></option>
</xsl:template>

<xsl:template match="imagevalidate">
	<dt>
		<xsl:call-template name="generic.label">
			<xsl:with-param name="caption"><xsl:value-of select="@caption"/></xsl:with-param>
			<xsl:with-param name="name"><xsl:value-of select="@name"/></xsl:with-param>
			<xsl:with-param name="required"><xsl:value-of select="@required"/></xsl:with-param>
		</xsl:call-template>
	</dt>
	<dd>
		<xsl:variable name="ampchar"><xsl:if test="$engine='PHP'">&amp;amp;</xsl:if><xsl:if test="not($engine='PHP')">&amp;</xsl:if></xsl:variable>
		<xsl:variable name="imgsrc"><xsl:value-of select="$urlbase" />imagevalidate.[param:ENGINEEXTENSION]?c=<xsl:value-of select="@chars" /><xsl:value-of select="$ampchar" />cq=<xsl:value-of select="@challengequestion" /><xsl:value-of select="$ampchar" /><xsl:value-of select="$ampchar" />lang=<xsl:value-of select="$lang" /><xsl:value-of select="$ampchar" /></xsl:variable>
		<img border="0" align="top" src="{$imgsrc}" onclick="this.src = '{$imgsrc}' + (new Date().getTime());" />
		<br/>
		<input type="text" name="imagevalidate" value="" size="8"/>
	</dd>
</xsl:template>

<xsl:template match="buttons">
	<dt id="buttons" >&#160;</dt>
	<dd>
		<xsl:apply-templates select="submit|button|reset"/>
	</dd>
</xsl:template>

<xsl:template match="submit"><input class="submit" type="submit"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@caption"/></xsl:attribute></input> </xsl:template>

<xsl:template match="reset"><input class="submit" type="reset"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@caption"/></xsl:attribute></input> </xsl:template>

<xsl:template match="button"><input class="submit" type="button"><xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@caption"/></xsl:attribute><xsl:attribute name="onclick"><xsl:value-of select="@onclick"/></xsl:attribute></input> </xsl:template>

<xsl:template match="clickevent"><input type="hidden" name="__clickevent"><xsl:attribute name="value"><xsl:value-of select="text()"/></xsl:attribute></input></xsl:template>


<xsl:template match="ajaxcallback">
	<div>
		<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
		<xsl:if test="@style"><xsl:attribute name="style"><xsl:value-of select="@style" /></xsl:attribute></xsl:if>
		<xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if>
	</div>
</xsl:template>


<xsl:template match="datetimebox">
	<dt><xsl:value-of select="@caption" /></dt>
	<dd>
		<input type="text" name="{@name}" id="{@name}" value="{@date}" maxlength="10" size="10"></input>
		<xsl:if test="@showhour">
			&#160; &#160; 
			<select name="{@name}_hour" id="{@name}_hour">
				<xsl:call-template name="datetimebox.loop">
					<xsl:with-param name="i"><xsl:value-of select="0"/></xsl:with-param>
					<xsl:with-param name="count"><xsl:value-of select="23"/></xsl:with-param>
					<xsl:with-param name="current"><xsl:value-of select="@hour"/></xsl:with-param>
				</xsl:call-template>
			</select>:
			<select name="{@name}_minute" id="{@name}_minute">
				<xsl:call-template name="datetimebox.loop">
					<xsl:with-param name="i"><xsl:value-of select="0"/></xsl:with-param>
					<xsl:with-param name="count"><xsl:value-of select="59"/></xsl:with-param>
					<xsl:with-param name="current"><xsl:value-of select="@minute"/></xsl:with-param>
				</xsl:call-template>
			</select>
		</xsl:if>
	</dd>
</xsl:template>


<xsl:template name="datetimebox.loop">
<xsl:param name="i"/>
<xsl:param name="count"/>
<xsl:param name="current"/>

	<xsl:if test="$i &lt;= $count">
		<option><xsl:if test="$i = $current"><xsl:attribute name="selected">true</xsl:attribute></xsl:if><xsl:value-of select="$i" /></option>
	</xsl:if>
	<xsl:if test="$i &lt;= $count">
		<xsl:call-template name="editlist.loop">
			<xsl:with-param name="i">
			<!-- Increment index-->
			<xsl:value-of select="$i + 1"/>
			</xsl:with-param>
			<xsl:with-param name="count">
			<xsl:value-of select="$count"/>
			</xsl:with-param>
			<xsl:with-param name="current">
			<xsl:value-of select="$current"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:if>
</xsl:template>
