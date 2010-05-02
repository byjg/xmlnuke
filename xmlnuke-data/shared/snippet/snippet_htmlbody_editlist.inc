<!-- USED ONLY WITH SNIPPET-HTMLBODY-BASIC -->
<!-- ATENTION: YOU NEED PUT IN YOUR HEADER SNIPPET_HTMLHEADER.INC -->
<!-- EDITLIST -->

<xsl:template match="editlist" >

	<xsl:if test="not(@readonly='true')">

	<!-- Submit Form -->
	<form id="form_{@name}" method="post"><xsl:if test="@module"><xsl:attribute name="action"><xsl:value-of select="@module"/></xsl:attribute></xsl:if><xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute>
	<input type="hidden" name="acao" value="" />
	<input type="hidden" name="valueid" value="" />
	<input type="hidden" name="curpage"><xsl:attribute name="value"><xsl:value-of select="@curpage" /></xsl:attribute></input>
	<input type="hidden" name="offset"><xsl:attribute name="value"><xsl:value-of select="@offset" /></xsl:attribute></input>
	<xsl:for-each  select="param">
	<input type="hidden"><xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute><xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute></input>
	</xsl:for-each>
	</form>

	<!-- Context Menu -->
	<div class="contextMenu" id="{@name}Menu" style="display: none">
      <ul>
		<!-- <xsl:if test="@new"><li require="0" action="new" id="{@name}_new"><img src="common/editlist/ic_novo.gif" border="0" /><span id="lbl_{@name}_new"></span></li></xsl:if> -->
		<xsl:if test="@view"><li require="1" action="view" id="{@name}_view"><img src="common/editlist/ic_detalhes.gif" border="0" /><span id="lbl_{@name}_view"></span></li></xsl:if>
		<xsl:if test="@edit"><li require="1" action="edit" id="{@name}_edit"><img src="common/editlist/ic_editar.gif" border="0" /><span id="lbl_{@name}_edit"></span></li></xsl:if>
		<xsl:if test="@delete"><li require="1" action="delete" id="{@name}_delete"><img src="common/editlist/ic_excluir.gif" border="0" /><span id="lbl_{@name}_delete"></span></li></xsl:if>
		<xsl:for-each select="button">
			<li>
				<xsl:attribute name="require"><xsl:value-of select="@multiple"/></xsl:attribute>
				<xsl:attribute name="id"><xsl:value-of select="parent::*/@name"/>_C<xsl:value-of select="@custom" /></xsl:attribute>
				<xsl:attribute name="action"><xsl:value-of select="@acao" /></xsl:attribute>
				<xsl:if test="@url!=''">
					<xsl:attribute name="url"><xsl:value-of select="@url"/></xsl:attribute>
				</xsl:if>
				<img src="{@img}" border="0" alt="{@alt}" title="{@alt}" />
				<span><xsl:value-of select="@alt" /></span>
			</li>
		</xsl:for-each>
      </ul>
    </div>
	</xsl:if>

	<!-- EDITLIST-->
	<div id="editlist" class="{@name}">
		<table id="edit_table">

			<!-- Table Caption -->
			<caption id="edit_caption"><xsl:value-of select="@title"/></caption>
		
			<!-- Table header -->
			<thead>
				<tr>
					<th class="edit_nav" colspan="999">

						<xsl:if test="not(@readonly='true')">

						<!-- Header Buttons -->
						<xsl:if test="@new"><input type="button" require="0" action="new" id="valbtn_{@name}_new" /></xsl:if>
						<xsl:if test="@view"><input type="button" require="1" action="view" id="valbtn_{@name}_view" /></xsl:if>
						<xsl:if test="@edit"><input type="button" require="1" action="edit" id="valbtn_{@name}_edit" /></xsl:if>
						<xsl:if test="@delete"><input type="button" require="1" action="delete" id="valbtn_{@name}_delete" /></xsl:if>
						<xsl:for-each select="button">
							<input type="button" value="{@alt}">
								<xsl:attribute name="require"><xsl:value-of select="@multiple"/></xsl:attribute>
								<xsl:attribute name="id"><xsl:value-of select="parent::*/@name"/>_C<xsl:value-of select="@custom" /></xsl:attribute>
								<xsl:attribute name="action"><xsl:value-of select="@acao" /></xsl:attribute>
								<xsl:if test="@url!=''">
									<xsl:attribute name="url"><xsl:value-of select="@url"/></xsl:attribute>
								</xsl:if>
							</input>
						</xsl:for-each>

						</xsl:if>

						<!-- navigation buttons -->
						<div class="edit_nav_pages">
							<span>#</span>
							<xsl:if test="@offset">
								<select onChange="navigate('{@name}', this.selectedIndex + 1)">
									<xsl:call-template name="editlist.loop">
									<xsl:with-param name="i">1</xsl:with-param>
									<xsl:with-param name="current"><xsl:value-of select="@curpage" /></xsl:with-param>
									<xsl:with-param name="count"><xsl:value-of select="@pages" /></xsl:with-param>
									</xsl:call-template>
								</select>
							</xsl:if>
				
							<a id="navbtn_{@name}_first" style="background-image: url(common/editlist/ic_primeiro.gif);"><xsl:if test="@pageback"><xsl:attribute name="href">javascript:navigate('<xsl:value-of select="@name" />', 1);</xsl:attribute></xsl:if><xsl:if test="not(@pageback)"><xsl:attribute name="active">false</xsl:attribute></xsl:if></a>
							<a id="navbtn_{@name}_previous" style="background-image: url(common/editlist/ic_voltar.gif);"><xsl:if test="@pageback"><xsl:attribute name="href">javascript:navigate('<xsl:value-of select="@name" />', '<xsl:value-of select="@pageback" />');</xsl:attribute></xsl:if><xsl:if test="not(@pageback)"><xsl:attribute name="active">false</xsl:attribute></xsl:if></a>
							<a id="navbtn_{@name}_next" style="background-image: url(common/editlist/ic_avancar.gif);"><xsl:if test="@pagefwd"><xsl:attribute name="href">javascript:navigate('<xsl:value-of select="@name" />', '<xsl:value-of select="@pagefwd" />');</xsl:attribute></xsl:if><xsl:if test="not(@pagefwd)"><xsl:attribute name="active">false</xsl:attribute></xsl:if></a>
							<a id="navbtn_{@name}_last" style="background-image: url(common/editlist/ic_ultimo.gif);"><xsl:if test="@pagefwd"><xsl:attribute name="href">javascript:navigate('<xsl:value-of select="@name" />', '<xsl:value-of select="@pages" />');</xsl:attribute></xsl:if><xsl:if test="not(@pagefwd)"><xsl:attribute name="active">false</xsl:attribute></xsl:if></a>
						</div>
					</th>
				</tr>
				<tr>
					<xsl:for-each select="row[1]/field">
						<xsl:if test="position() != 1">
							<th><xsl:value-of select="@name" /></th>
						</xsl:if>
					</xsl:for-each>
				</tr>
			</thead>

			<!-- Table footer -->
			<tfoot>
				<xsl:for-each select="row[@total='true']">
				<tr>
					<xsl:if test="position() mod 2 = 1"><xsl:attribute name="class">odd</xsl:attribute></xsl:if>
					<xsl:if test="position() mod 2 = 0"><xsl:attribute name="class">even</xsl:attribute></xsl:if>
					<xsl:for-each select="field">
						<xsl:if test="position() != 1">
							<td><xsl:apply-templates /></td>
						</xsl:if>
					</xsl:for-each>
				</tr>
				</xsl:for-each>
			</tfoot>

			<!-- Table body -->
			<tbody>
				<xsl:for-each select="row[not(@total='true')]">
				<tr id="{parent::*/@name}_{position()}" value="{field[1]/text()}">
					<xsl:if test="position() mod 2 = 1"><xsl:attribute name="class">odd</xsl:attribute></xsl:if>
					<xsl:if test="position() mod 2 = 0"><xsl:attribute name="class">even</xsl:attribute></xsl:if>
					<xsl:for-each select="field">
						<xsl:if test="position() != 1">
							<td><xsl:apply-templates /></td>
						</xsl:if>
					</xsl:for-each>
				</tr>
				</xsl:for-each>
			</tbody>
		</table>

		<div class="edit_footer">

			<!-- Buttons -->

			<!-- Navigation -->
			
			<xsl:for-each select="xmlheader">
				&#160;
				<xsl:apply-templates />
			</xsl:for-each>

		</div>

	</div>
	
	<script language="javascript">
	$(document).ready(function() {
		$('#editlist.<xsl:value-of select="@name"/>').find('tr.odd, tr.even').contextMenu('<xsl:value-of select="@name" />Menu', {
			defaultAction: function(source, row)
			{
				submitEditList("<xsl:value-of select="@name" />", source);
			},
		    onShowMenu: function(e, menu)
			{
				if ($(e.target).attr('id') == 'edit_caption') {
					menu.find("li[require!='0']").remove();
				}
				return menu;
			}
		});
	} );

	var editlistName = '<xsl:value-of select="@name"/>';
	defineCaption(editlistName + "_new", BTN_NEW);
	defineCaption(editlistName + "_view", BTN_VIEW);
	defineCaption(editlistName + "_edit", BTN_EDIT);
	defineCaption(editlistName + "_delete", BTN_DELETE);
	defineImageCaption(editlistName + "_first", BTN_FIRST);
	defineImageCaption(editlistName + "_previous", BTN_PREVIOUS);
	defineImageCaption(editlistName + "_next", BTN_NEXT);
	defineImageCaption(editlistName + "_last", BTN_LAST);

	$('#editlist.<xsl:value-of select="@name"/>').find('th').find('input').click(function() {
		submitEditList('<xsl:value-of select="@name"/>', this);
	});

	$('#editlist.<xsl:value-of select="@name"/>').find('tr.even, tr.odd').click(function() {
		attrClass = ($(this).attr("class"));
		$(this).parent().find("tr").removeClass("selected");
		if (!attrClass.match("selected"))
		{
			$(this).addClass("selected");
		}
	});
	</script>

	
</xsl:template>


<xsl:template name="editlist.loop">
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

