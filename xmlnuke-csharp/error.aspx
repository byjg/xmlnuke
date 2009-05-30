<%@ Page Language="c#" %>
<script runat="server">

    void Page_Load(Object sender, EventArgs e) {
       bool completeErrMsg = false;

       if (!IsPostBack)
       {
            Object ex = HttpContext.Current.Session["ErrorObj"];
            if (ex == null)
            {
                TypeErrorMsg.Text = "Ooopss";
                MessageTxt.Text = "Ooopss";
                StackTraceTxt.Text = "Ooppss";
            }
            else
            {
                if (((System.Exception)ex).InnerException != null)
                {
                    ex = ((System.Exception)ex).InnerException;
                }
                TypeErrorMsg.Text = ex.GetType().Namespace + "." + ex.GetType().Name;
                MessageTxt.Text = ((System.Exception)ex).Message;
                StackTraceTxt.Text = ((System.Exception)ex).StackTrace;
                ExplanationTxt.Text = "";
                if (ex.GetType() == typeof(System.IO.DirectoryNotFoundException))
                {
                    ExplanationTxt.Text = "The most probably reason for this error occurs is: <ul><li>The site you provided in your request doesnt exists, or</li><li>Your Web.Config file have a invalid configuration</li></ul>";
                }
                if (ex.GetType() == typeof(System.Xml.XmlException))
                {
                    ExplanationTxt.Text = "This errors occurs when your document (XML or XSL) is invalid or when cache have size equal to zero.";
                }
                if (ex.GetType() == typeof(System.IO.FileNotFoundException))
                {
                    ExplanationTxt.Text = "The requested XML or XSL file was not found. Most probably XSL is not found.";
                }
                Explanation.Visible = (ExplanationTxt.Text != "");
                ExplanationTxt.Visible = (ExplanationTxt.Text != "");
            }
        }

       try
       {
	   com.xmlnuke.engine.Context context = new com.xmlnuke.engine.Context(Context);
	   completeErrMsg = context.showCompleteErrorMessage();
       }
       catch
       {
           completeErrMsg = false;
       }

       Message.Visible = completeErrMsg;
       MessageTxt.Visible = completeErrMsg;
       ShowStack.Visible = completeErrMsg;

    }
    
    void ShowStack_Click(Object sender, EventArgs e) {
        ShowStack.Visible = false;
        StackTrace.Visible = true;
        StackTraceTxt.Visible = true;
    }

</script>
<html>
<head>
  <title>Xmlnuke Error</title>
</head>
<body>
    <form runat="server">
        <p>
            <asp:Label id="Title" runat="server" Font-Names="Verdana" Font-Size="Large" Font-Bold="True">XMLNuke Error Message</asp:Label>
        </p>
        <p>
            <asp:Label id="TypeError" runat="server" Font-Names="Verdana" Font-Size="11pt" Font-Bold="True">Type Exception</asp:Label>
            <br />
            <asp:Label id="TypeErrorMsg" runat="server" Font-Names="Verdana" Font-Size="11pt"></asp:Label>
        </p>
        <p>
            <asp:Label id="Message" runat="server" Font-Names="Verdana" Font-Size="11pt" Font-Bold="True">Original Message</asp:Label>
            <br />
            <asp:Label id="MessageTxt" runat="server" Font-Names="Verdana" Font-Size="11pt"></asp:Label>
        </p>
        <p>
            <asp:Label id="Explanation" runat="server" Font-Names="Verdana" Font-Size="11pt" Font-Bold="True" Visible="False">Explanation</asp:Label>
            <br />
            <asp:Label id="ExplanationTxt" runat="server" Font-Names="Verdana" Font-Size="11pt" Visible="False">ExplanationTxt</asp:Label>
        </p>
        <p>
            <asp:LinkButton id="ShowStack" onclick="ShowStack_Click" runat="server" Font-Names="Verdana" Font-Size="11pt" Font-Bold="True" Visible="True">Show Error Details</asp:LinkButton>
            <asp:Label id="StackTrace" runat="server" Font-Names="Verdana" Font-Size="11pt" Font-Bold="True" Visible="False">Stack Trace</asp:Label>
            <br />
            <asp:Label id="StackTraceTxt" runat="server" Font-Names="Verdana" Font-Size="11pt" Visible="False"></asp:Label>
        </p>
    </form>
</body>
</html>
