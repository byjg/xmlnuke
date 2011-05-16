<%@ Page Language="C#" Debug="True" ValidateRequest="false" %>
<%@ import Namespace="System.IO" %>
<%@ import Namespace="System.Xml" %>
<%@ import Namespace="com.xmlnuke.admin" %>
<%@ import Namespace="com.xmlnuke.engine" %>
<%@ import Namespace="com.xmlnuke.anydataset" %>
<%@ import Namespace="com.xmlnuke.processor" %>
<%@ import Namespace="com.xmlnuke.util" %>
<script runat="server">

    // <config>
    // FileFusian.NET - http://www.efusian.co.uk/v2/index.php?page=filefusian
    //
    //  Uma boa ideia é criar um AnyDataSet para arquivos permitidos para edicao, upload e arquivos que devem ser escondidos...
    //
    //

         const char SLASH = '/';
         com.xmlnuke.engine.Context context ;

         string filedir;  // Upload Directory

         long maxfilesize;  // Maximum Upload File Size In KB

         string[] uploadtypes; // Allowed File Types
         string[] edittypes; // Allowed File Types

    // </config>

    public void Page_Load(Object Sender, EventArgs e) {

        context = com.xmlnuke.engine.Context.getInstance();
        UsersAnyDataSet users = new UsersAnyDataSet(context);
        SingleRow currentUser = users.getUserName(context.authenticatedUser());
        if ((currentUser == null) || (currentUser.getField("admin") != "yes"))
        {
            Response.Redirect(context.bindModuleUrl("Login", "page") + "&ReturnUrl=~%2ffilemanagement.aspx");
        }

           AnydatasetFilenameProcessor procFileManagement = new AnydatasetFilenameProcessor("filemanagement", context);
           procFileManagement.FilenameLocation = ForceFilenameLocation.PathFromRoot;
           AnyDataSet anyFileManagement = new AnyDataSet(procFileManagement);

           IteratorFilter filter = new IteratorFilter();
           filter.addRelation("type", Relation.Equal , "INITIAL_DIR" );
           Iterator it = anyFileManagement.getIterator(filter);
           SingleRow sr;
           if (it.hasNext())
           {
               sr = it.moveNext();
               filedir = sr.getField("value");
           }

           filter = new IteratorFilter();
           filter.addRelation("type", Relation.Equal , "MAX_UPLOAD" );
           it = anyFileManagement.getIterator(filter);
           if (it.hasNext())
           {
               sr = it.moveNext();
               maxfilesize = Convert.ToInt32(sr.getField("value"));
           }


           filter = new IteratorFilter();
           filter.addRelation("type", Relation.Equal , "VALID_EDIT" );
           it = anyFileManagement.getIterator(filter);
           if (it.hasNext())
           {
               sr = it.moveNext();
               edittypes = sr.getFieldArray("value");
           }

           filter = new IteratorFilter();
           filter.addRelation("type", Relation.Equal , "VALID_UPLOAD" );
           it = anyFileManagement.getIterator(filter);
           if (it.hasNext())
           {
               sr = it.moveNext();
               string[] tmp = sr.getFieldArray("value");
               uploadtypes = new string[tmp.Length + edittypes.Length];
               for(int i = 0; i<edittypes.Length; i++)
               {
                   uploadtypes[i] = edittypes[i];
               }
               for(int i = 0; i<tmp.Length; i++)
               {
                   uploadtypes[edittypes.Length + i] = tmp[i];
               }
           }



      lblRemoteAddress.Text = Request.ServerVariables["REMOTE_ADDR"];
      lblMaxFileSize.Text = maxfilesize + " KB";

      lblValidExtensions.Text = null;
      foreach (string a in uploadtypes) {
        lblValidExtensions.Text = lblValidExtensions.Text + a + ", ";
      }
      int LastComma = lblValidExtensions.Text.LastIndexOf(",");
      lblValidExtensions.Text = lblValidExtensions.Text.Substring(0, LastComma);


      if (Request.QueryString["dir"] != null)
      {
            filedir += Request.QueryString["dir"] + SLASH;
      }


      if (!IsPostBack) {

        ViewState["CurrentDir"] = filedir;
        vewUpload.Visible = false;
        vewFileList.Visible = true;
        vewEditFile.Visible = false;
      }

      BindGrid();

    }

    private void BindGrid()
    {
      string curdir = (string)ViewState["CurrentDir"];

      CurrentDir.Text = "Current Directory: " + curdir;

      linkToParent.Visible = (curdir != filedir);

      curdir = Server.MapPath(curdir);


      DirectoryInfo dirinfo = new DirectoryInfo(curdir);

      dirlist.DataSource = dirinfo.GetDirectories();
      dirlist.DataBind();

      filelist.DataSource = dirinfo.GetFiles("*");
      filelist.DataBind();

    }

    private void DeleteFile (string strFileName) {

      if (strFileName.Trim().Length > 0) {

        FileInfo finfo = new FileInfo(strFileName);

        if (finfo.Exists) {

          finfo.Delete();

        }

      }

    }

    public void button1_Click(object Source, EventArgs e) {

      if ((file1.PostedFile != null) && (file1.PostedFile.ContentLength > 0)) {

        maxfilesize = maxfilesize * 1024;

        string filename = Path.GetFileName(file1.PostedFile.FileName);

        try {

          if (file1.PostedFile.ContentLength <= maxfilesize)  {

            bool fallowed = false;

            foreach (string a in uploadtypes) {

              if (Path.GetExtension(file1.PostedFile.FileName) == a) {

                fallowed = true;

              }

            }

            if (fallowed == true ) {

              file1.PostedFile.SaveAs(Server.MapPath(filedir + filename));
              lblMessages.Text = "File uploaded successfully!";

            }
            else {

              lblMessages.Text = "Upload failed! File type disallowed.";

            }

          }
          else {

            lblMessages.Text = "The file size is over the limit of " + lblMaxFileSize.Text + ".";

          }
        }
        catch(Exception ex) {

          lblMessages.Text = "An error occured during the file upload. Please try again!<br/>"+ex.Message;
          DeleteFile(filedir + filename);

        }

      }

    }


    void LinkButton_Command(object sender, CommandEventArgs e) {
        vewUpload.Visible = e.CommandName == "Upload";
        vewFileList.Visible = e.CommandName != "Upload"; //e.CommandName == "FileListing";
        vewEditFile.Visible = false;
        lblMessages.Text = "";
    }

    void dirlist_SelectedIndexChanged(object sender, EventArgs e) {
        string curdir = (string)ViewState["CurrentDir"];
        string newdir = ((System.Web.UI.WebControls.LinkButton)dirlist.SelectedItem.Cells[0].Controls[0]).Text;
        ViewState["CurrentDir"] = curdir + newdir + SLASH;

        BindGrid();
    }

    void linkToParent_Command(object sender, CommandEventArgs e) {
        string curdir = (string)ViewState["CurrentDir"];
        curdir = curdir.Substring(0, curdir.Length-1);
        curdir = curdir.Substring(0, curdir.LastIndexOf(SLASH) + 1);
        ViewState["CurrentDir"] = curdir;

        LinkButton_Command(sender, e);

        BindGrid();
    }

    void filelist_SelectedIndexChanged(object sender, EventArgs e) {
        string curdir = (string)ViewState["CurrentDir"];
        string file = filelist.SelectedItem.Cells[2].Text;

		bool fallowed = false;
        foreach (string a in edittypes) {

          if (Path.GetExtension(file) == a) {

               fallowed = true;

           }
        }

        if (!fallowed)
        {
 		    lblMessages.Text = "Cannot Editing \"" + file + "\"! File type disallowed.";
 		}
 		else
 		{
	        txtEdit.Text = FileUtil.QuickFileRead( Server.MapPath(curdir + file) );

		    lblEdit.Text = file;

			vewUpload.Visible = false;
			vewFileList.Visible = false;
			vewEditFile.Visible = true;
		}
    }

    void btnSave_Click(object sender, EventArgs e) {
        string curdir = (string)ViewState["CurrentDir"];
        string file = lblEdit.Text;

        FileUtil.QuickFileWrite( Server.MapPath(curdir + file), txtEdit.Text );

        lblMessages.Text = "Saved...";
    }

</script>
<html>
<head>
    <title>XMLNuke FileManagement (based on File-Fusian .NET v1.0.1)</title> <style>A {
	TEXT-DECORATION: none
}
</style>
</head>
<body>
    <form enctype="multipart/form-data" runat="server">
        Commands:
        <asp:LinkButton id="linkFileListing" runat="server" CommandName="FileListing" OnCommand="LinkButton_Command">File Listing</asp:LinkButton>
        |
        <asp:LinkButton id="linkUpload" runat="server" CommandName="Upload" OnCommand="LinkButton_Command">Upload</asp:LinkButton>
        <hr />
        <asp:Label id="CurrentDir" runat="server" font-name="Verdana" font-size="10px"></asp:Label>
        <br />
        <asp:LinkButton id="linkToParent" runat="server" OnCommand="linkToParent_Command" Font-Name="Verdana" Font-Size="10px">&lt;&lt; To Parent Directory</asp:LinkButton>
        <hr />
        <asp:Panel id="vewUpload" runat="server">
            <br />
            <input id="file1" type="file" runat="server" />
            <br />
            <br />
            <input id="button1" type="button" value="Upload File" runat="server" onserverclick="button1_Click" />
            <br />
            <br />
            <br />
            <br />
        </asp:Panel>
        <asp:Label id="lblMessages" runat="server"></asp:Label>
        <asp:Panel id="vewFileList" runat="server" Width="603px">
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td valign="top">
                            <asp:DataGrid id="dirlist" runat="server" Font-Name="Verdana" Font-Size="10px" Width="147px" EnableViewState="False" AutoGenerateColumns="False" BorderColor="#999999" Headerstyle-BackColor="#eeeeee" Headerstyle-Font-Size="11px" Headerstyle-Font-Style="bold" CellPadding="1" Font-Names="Verdana" GridLines="None" OnSelectedIndexChanged="dirlist_SelectedIndexChanged">
                                <HeaderStyle font-size="11px" backcolor="#EEEEEE"></HeaderStyle>
                                <Columns>
                                    <asp:ButtonColumn Text="Select" DataTextField="Name" HeaderText="Directory List" CommandName="Select"></asp:ButtonColumn>
                                </Columns>
                            </asp:DataGrid>
                        </td>
                        <td valign="top">
                            <asp:DataGrid id="filelist" runat="server" Font-Name="Verdana" Font-Size="10px" Width="432px" EnableViewState="False" AutoGenerateColumns="False" BorderColor="#999999" Headerstyle-BackColor="#eeeeee" Headerstyle-Font-Size="11px" Headerstyle-Font-Style="bold" CellPadding="2" Font-Names="Verdana" OnSelectedIndexChanged="filelist_SelectedIndexChanged">
                                <HeaderStyle font-size="11px" backcolor="#EEEEEE"></HeaderStyle>
                                <Columns>
                                    <asp:ButtonColumn Text="Edit" CommandName="Select"></asp:ButtonColumn>
                                    <asp:ButtonColumn Text="Delete" CommandName="Delete"></asp:ButtonColumn>
                                    <asp:BoundColumn DataField="Name" HeaderText="File Name"></asp:BoundColumn>
                                    <asp:BoundColumn DataField="LastWriteTime" HeaderText="Upload Date" DataFormatString="{0:d}"></asp:BoundColumn>
                                    <asp:BoundColumn DataField="Length" HeaderText="File Size" DataFormatString="{0:#,### B}">
                                        <ItemStyle horizontalalign="Right"></ItemStyle>
                                    </asp:BoundColumn>
                                </Columns>
                            </asp:DataGrid>
                        </td>
                    </tr>
                </tbody>
            </table>
        </asp:Panel>
        <br />
        <asp:Panel id="vewEditFile" runat="server" Width="603px" Height="160px">
            <asp:Label id="lblEdit" runat="server" font-name="Verdana" font-size="10px">Label</asp:Label>
            <br />
            <asp:TextBox id="txtEdit" runat="server" Width="585px" Rows="20" TextMode="MultiLine"></asp:TextBox>
            <br />
            <asp:Button id="btnSave" onclick="btnSave_Click" runat="server" Text="Save File"></asp:Button>
        </asp:Panel>
        <br />
        <div id="row_span4" style="WIDTH: 502px; HEIGHT: 63px">Your Current IP Address Is: <asp:Label id="lblRemoteAddress" runat="server"></asp:Label>
            <br />
            Maximum Allowed File Size: <asp:Label id="lblMaxFileSize" runat="server"></asp:Label>
            <br />
            Allowed File Types: <asp:Label id="lblValidExtensions" runat="server"></asp:Label>
            <br />
        </div>
    </form>
</body>
</html>
