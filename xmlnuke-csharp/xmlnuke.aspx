<%@ Page Language="c#" ValidateRequest="false" %>
<script runat="server">

	void Page_Load(object sender, System.EventArgs e)
	{
		com.xmlnuke.engine.Context context = new com.xmlnuke.engine.Context(Context);
		string selectNodes = context.ContextValue("xpath");
        com.xmlnuke.engine.OutputResult output = com.xmlnuke.engine.OutputResult.XHtml;
        
        if (context.ContextValue("rawxml")!="")
        {
	        string filename = (context.ContextValue("module") != "" ? context.ContextValue("module") : context.Xml);
            filename = filename.Replace(".", "_") + ".xsl";
            
	        output = com.xmlnuke.engine.OutputResult.Xml;
            Context.Response.ContentType = "text/xml";
            Context.Response.AppendHeader("Content-Disposition", "inline; filename=\"" + filename + "\";");
        }
        else if (context.ContextValue("rawjson")!="")
        {
	        string filename = (context.ContextValue("module") != "" ? context.ContextValue("module") : context.Xml);
            filename = filename.Replace(".", "_") + ".json";
            
	        output = com.xmlnuke.engine.OutputResult.Json;
            Context.Response.ContentType = "application/json";
            Context.Response.AppendHeader("Content-Disposition", "inline; filename=\"" + filename + "\";");
        }
		else
		{
			Context.Response.ContentType = context.getSuggestedContentType();
		}
		Context.Response.ContentEncoding = System.Text.Encoding.UTF8;
		try
		{
			com.xmlnuke.engine.XmlNukeEngine engine = new com.xmlnuke.engine.XmlNukeEngine(context, output, selectNodes);
			if (context.ContextValue("remote") != "")
			{
				Response.Write(engine.TransformDocumentRemote(context.ContextValue("remote")));
			}
			else if (context.ContextValue("module") == "")
			{
				Response.Write(engine.TransformDocument());
			}
			else
			{
				Response.Write(com.xmlnuke.engine.XmlNukeEngine.ProcessModule(context, engine));
			}
		}
		finally
		{
			context.persistXMLDataBaseInMemory();
		}

		//Response.Write("<p>&nbsp;</p><div align='right'><font face='verdana' size='1'><b>");
		//Response.Write(context.XmlNukeVersion + "<br>Platform: " + System.Environment.OSVersion.Platform.ToString());
		//Response.Write("</b></font></div>");
	}

</script>
