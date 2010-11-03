/**************************************************************
 * Init Script for XMLNuke objects
 * By JG 2008-04-23
 *******/
function initTinyMCE(varElements, editDoc, baseHref)
{
	buttonsAvailable = "bold,italic,underline,separator,bullist,numlist,link,unlink,image,separator,undo,redo,removeformat,cleanup,pastetext,pasteword,selectall,separator,code";

	if (editDoc)
	{
		buttonsAvailable = "xmlblkbtn," + buttonsAvailable;
	}
	
	tinyMCE.init({
		theme : "advanced",
		mode : "exact",
		elements : varElements,
		theme_advanced_buttons1 : buttonsAvailable, 
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		plugins : "paste",
		entity_encoding : "raw",
		document_base_url : baseHref,
		add_unload_trigger : false,
		remove_linebreaks : false,
		inline_styles : false,
		extended_valid_elements : "iframe[src|width|height|name|align]",
		convert_fonts_to_spans : false,
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		setup : function(ed) {
			// Change some XMLNuke elements into HTML editor
			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(/&nbsp;/gi," ");
				o.content = o.content.replace(/&quot;/gi,"\"");
				o.content = o.content.replace(/&lt;/gi,"<");
				o.content = o.content.replace(/&gt;/gi,">");
				o.content = o.content.replace(/&amp;/gi,"&");
				o.content = o.content.replace(/<blockcenter>/gi, "<div style=\"border: 1px solid silver; margin: 5px;\">");
				o.content = o.content.replace(/<\/blockcenter>/gi, "</div>");
				o.content = o.content.replace(/<\/?body>/gi, "");
				o.content = o.content.replace(/<title>(.*)<\/title>/gi, "<h1>$1</h1>");
			});

			// Gets executed after DOM to HTML string serialization
			ed.onPostProcess.add(function(ed, o) {
				// State get is set when contents is extracted from editor
				if (o.get) {
					//o.content = o.content.replace(/\r?\n/gi, "");
					o.content = o.content.replace(/<div style=\"border: 1px solid silver; margin: 5px;\"><h1>(.*?)<\/h1>(.*?)<\/div>/gi,"<blockcenter><title>$1</title><body>$2</body></blockcenter>");
					o.content = o.content.replace(/<\/(strong|b)>/gi,"</b>");
					o.content = o.content.replace(/<(strong|b)>/gi,"<b>");
					o.content = o.content.replace(/<\/(em|i)>/gi,"</i>");
					o.content = o.content.replace(/<(em|i)>/gi,"<i>");
					o.content = o.content.replace(/<\/u>/gi,"</u>");
					o.content = o.content.replace(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"<u>$1</u>");
					o.content = o.content.replace(/<u>/gi,"<u>");
					o.content = o.content.replace(/<br>/gi,"<br />");
					o.content = o.content.replace(/<span.*>(.*?)<\/span>/gi,"$1");
					o.content = o.content.replace(/<font.*>(.*?)<\/font>/gi,"$1");
				}
			});
			
			// Add a custom button
			ed.addButton('xmlblkbtn', {
			    title : 'Add Block',
			    image : 'common/3rdparty/tiny_mce/btn_block.gif',
			    onclick : function() {
				ed.selection.setContent('<div style="border: 1px solid silver; margin: 5px"><h1>Title</h1><p>Content</p></div><p></p>');
			    }
			});			
		}
	});
}
