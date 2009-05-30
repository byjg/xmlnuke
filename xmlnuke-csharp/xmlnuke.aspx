<%@ Page Language="c#" ValidateRequest="false" %>
<script runat="server">

	void Page_Load(object sender, System.EventArgs e)
	{
		com.xmlnuke.engine.Context context = new com.xmlnuke.engine.Context(Context);
		bool applyXslTemplate = (context.ContextValue("rawxml") == "");
		string selectNodes = context.ContextValue("xpath");
		if (!applyXslTemplate)
		{
			Context.Response.ContentType = "text/xml";
		}
		else
		{
			Context.Response.ContentType = context.getSuggestedContentType();
		}
		Context.Response.ContentEncoding = System.Text.Encoding.UTF8;
		try
		{
			com.xmlnuke.engine.XmlNukeEngine engine = new com.xmlnuke.engine.XmlNukeEngine(context, applyXslTemplate, selectNodes);
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
