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
            output = com.xmlnuke.engine.OutputResult.XHtml;
            string contentType;
            if (detectMobile())
		    {
    			// WML
			    //$contentType = "text/vnd.wap.wml";
			    //$context->setXsl("wml");

			    // XHTML + MP
			    contentType = context.getBestSupportedMimeType(new string[] {"application/vnd.wap.xhtml+xml", "application/xhtml+xml", "text/html"});
			    context.Xsl = "mobile";
		    }
		    else
		    {
    			contentType = context.getSuggestedContentType();
		    }            
			Context.Response.ContentType = contentType;
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

    /// <summary>
    /// Detecting a mobile browser in ASP.NET
    /// By Vincent Van Zyl | 24 Mar 2009 
    /// Adapted for XMLNuke 
    /// by João Gilberto Magalhães
    /// </summary>
    /// <see cref="http://www.codeproject.com/KB/aspnet/mobiledetect.aspx"/>
    /// <returns></returns>
	protected bool detectMobile()
	{
        //GETS THE CURRENT USER CONTEXT
        HttpContext context = HttpContext.Current;

        //FIRST TRY BUILT IN ASP.NT CHECK
        if (context.Request.Browser.IsMobileDevice)
        {
            return true;
        }
        //THEN TRY CHECKING FOR THE HTTP_X_WAP_PROFILE HEADER
        if (context.Request.ServerVariables["HTTP_X_WAP_PROFILE"] != null)
        {
            return true;
        }
        //THEN TRY CHECKING THAT HTTP_ACCEPT EXISTS AND CONTAINS WAP
        if (context.Request.ServerVariables["HTTP_ACCEPT"] != null &&
            context.Request.ServerVariables["HTTP_ACCEPT"].ToLower().Contains("wap"))
        {
            return true;
        }
        //AND FINALLY CHECK THE HTTP_USER_AGENT 
        //HEADER VARIABLE FOR ANY ONE OF THE FOLLOWING
        if (context.Request.ServerVariables["HTTP_USER_AGENT"] != null)
        {
            //Create a list of all mobile types
            string[] mobiles =
                new string[]
                {
                    "midp", "j2me", "avant", "docomo", "novarra", "palmos", "palmsource", "240x320", "opwv", "chtml",
                    "pda", "windows ce", "mmp/", "blackberry", "mib/", "symbian", "wireless", "nokia", "hand", "mobi",
                    "phone", "cdm", "up.b", "audio", "SIE-", "SEC-", "samsung", "HTC", "mot-", "mitsu", "sagem", "sony"
                    , "alcatel", "lg", "eric", "vx", "NEC", "philips", "mmm", "xx", "panasonic", "sharp", "wap", "sch",
                    "rover", "pocket", "benq", "java", /*"pt", */"pg", "vox", "amoi", "bird", "compal", "kg", "voda",
                    "sany", "kdd", "dbt", "sendo", "sgh", "gradi", "jb", "dddi", "moto", "iphone"
                };

            //Loop through each item in the list created above 
            //and check if the header contains that text
            foreach (string s in mobiles)
            {
                if (context.Request.ServerVariables["HTTP_USER_AGENT"].ToLower().Contains(s.ToLower()))
                {
                    return true;
                }
            }
        }

        return false;
    }
    
</script>
