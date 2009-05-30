<%@ import Namespace="System.Web.Security" %>
<%@ Page Language="c#" %>
<HTML>
	<HEAD>
		<script runat="server">

    void updateInfo(string usernamevalid, string roles)
    {
        // Create the authentication ticket and store the roles in the
        // custom UserData property of the authentication ticket
        FormsAuthenticationTicket authTicket = new
        FormsAuthenticationTicket(
                        1,                          // version
                        usernamevalid,              // user name
                        DateTime.Now,               // creation
                        DateTime.Now.AddMinutes(20),// Expiration
                        false,                      // Persistent
                        roles );                    // User data
        // Encrypt the ticket.
        string encryptedTicket = FormsAuthentication.Encrypt(authTicket);
        // Create a cookie and add the encrypted ticket to the
        // cookie as data.
        HttpCookie authCookie =
                new HttpCookie(FormsAuthentication.FormsCookieName,
                                encryptedTicket);

        // Add the cookie to the outgoing cookies collection.
        Response.Cookies.Add(authCookie);
        // Redirect the user to the originally requested page
        string Url = FormsAuthentication.GetRedirectUrl(usernamevalid, false);
        /*
        if (Url.IndexOf("?")>=0)
        {
			Url += "&";
        }
        else
        {
			Url += "?";
        }
        Url += "site=" + siteList.SelectedItem.Text;
        */
        Response.Redirect( Url );
    }
    
    void Page_Load(object sender, EventArgs e) {
        if (!Page.IsPostBack)
        {
			com.xmlnuke.engine.Context context = new com.xmlnuke.engine.Context(Context);
		    if (context.IsAuthenticated())
	        {
				Response.Redirect("default.aspx");
			}
        }
        optionsRow.Visible = (username.Text != "");
        passwordRow.Visible = optionsRow.Visible;
        dontHaveSite.Visible = false;
        ErrorMessage.Text =  "";
    }
    
    void btnSubmit_Click(object sender, EventArgs e) {
        com.xmlnuke.engine.Context context = new com.xmlnuke.engine.Context(Context);
        com.xmlnuke.admin.UsersAnyDataSet users = new com.xmlnuke.admin.UsersAnyDataSet( context );
    
        if ((username.Text != "") && (password.Text == ""))
        {
            ErrorMessage.Text = "Type your password";

			string[] sites = users.returnUserProperty( username.Text, com.xmlnuke.admin.UserProperty.Site );
            siteList.Visible = (sites != null);
			dontHaveSite.Visible = !siteList.Visible;

			if (siteList.Visible)
			{			    
				siteList.DataSource = sites;
				siteList.DataBind();
            }
        }
        else
        {
			if ((username.Text != "") && (password.Text != ""))
			{
				com.xmlnuke.anydataset.SingleRow user = users.validateUserName(username.Text, password.Text);
				if (user == null)
				{
					ErrorMessage.Text = "Password Invalid!";
				}
				else
				{
					updateInfo(username.Text, "user");
				}				
			}
		}
    }

		</script>
		<link href="admin.css" type="text/css" rel="stylesheet">
	</HEAD>
	<body>
		<h1>XMLNUKE.com Admin Secure Area
		</h1>
		<asp:Label id="ErrorMessage" runat="server" font-bold="True"></asp:Label>
		<p>
		</p>
		<form runat="server">
			<table>
				<tbody>
					<tr>
						<th colspan="2">
							Type your credentials</th>
					</tr>
					<tr>
						<td>
							UserName:
						</td>
						<td>
							&nbsp;<asp:TextBox id="username" runat="server"></asp:TextBox>
						</td>
					</tr>
					<tr id="optionsRow" runat="server">
						<td>
							Site:
						</td>
						<td>
							&nbsp;<asp:DropDownList id="siteList" runat="server"></asp:DropDownList><asp:Label id="dontHaveSite" runat="server" Visible="False">User doesn't admin any site</asp:Label>
						</td>
					</tr>
					<tr id="passwordRow" runat="server">
						<td>
							Password:
						</td>
						<td>
							&nbsp;<asp:TextBox id="password" runat="server" TextMode="Password"></asp:TextBox>
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;</td>
						<td>
							&nbsp;<asp:Button id="btnSubmit" onclick="btnSubmit_Click" runat="server" Text="Authenticate"></asp:Button>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</body>
</HTML>
