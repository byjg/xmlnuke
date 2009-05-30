<%@ Import NameSpace="System" %>
<%@ Import NameSpace="System.Collections" %>

<script language="c#" runat="server">

	void Application_Error(Object sender, EventArgs e)
	{
		// I don´t know why, byt when I change the language I got an error and the object session is NULL!!!
		if (HttpContext.Current.Session != null)
		{
			HttpContext.Current.Session["ErrorObj"] = Server.GetLastError();
		}
    }

	void Session_Start(Object sender, EventArgs e)
	{
		if (HttpContext.Current.Session != null)
		{
			HttpContext.Current.Session["ErrorObj"] = new Exception("Bad directory structure or web.config file");
		}
	}

</script>
