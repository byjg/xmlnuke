<%@ Page Language="C#" %>
<%@ Import Namespace="System" %>
<%@ Import Namespace="System.Drawing" %>
<%@ Import Namespace="System.Drawing.Text" %>
<%@ Import Namespace="System.Drawing.Imaging" %>

<%@ Import Namespace="com.xmlnuke.engine" %>
<%@ Import Namespace="com.xmlnuke.thirdparty" %>

<script language="C#" runat="server">

void Page_Load(Object sender, EventArgs e)
{
	com.xmlnuke.engine.Context context = com.xmlnuke.engine.Context.getInstance();

	int c = 5;
	try
	{
		c = Convert.ToInt32(context.ContextValue("c"));
	}
	catch (Exception ex)
	{
		c = 6;
	}
	bool cq = context.ContextValue("cq") == "1";

	Response.Clear();
	Response.ContentType = "image/jpeg";
	ADSSAntiBot captcha = new ADSSAntiBot(context, c, cq);
	captcha.Result.Save(Response.OutputStream, ImageFormat.Jpeg);
}

</script>

<form runat="server">
</form>

