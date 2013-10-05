<%@ Page Language="c#" %>
<%@ Import Namespace="com.xmlnuke.processor" %>
<%@ Import Namespace="com.xmlnuke.util" %>
<%@ Import Namespace="com.xmlnuke.classes" %>
<script runat="server">

    void Page_Load(object sender, System.EventArgs e)
    {
	com.xmlnuke.engine.Context context = com.xmlnuke.engine.Context.getInstance();
	com.xmlnuke.engine.XmlnukeEngine engine = new com.xmlnuke.engine.XmlnukeEngine(context);

		context.Reset = true;
		context.NoCache = true;

		if (context.Module == "")
		{
			Response.Write(com.xmlnuke.engine.XmlnukeEngine.ProcessModule(context, engine, "com.xmlnuke.admin.ControlPanel"));
		}
		else
		{
			Response.Write(com.xmlnuke.engine.XmlnukeEngine.ProcessModule(context, engine));
		}
    }

</script>
