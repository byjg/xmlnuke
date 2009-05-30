<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System" %>
<%@ page Language="C#" debug="true"  %>

<%
//with debug = true you can really see some extra information 
//about your programme.Try some error in your code and see the result your self ...
// http://www.csharphelp.com/archives2/archive351.html
%>

<html>
<head>
<title>FileUpload utility on web, By Prashant Brall</title>

<script language="C#" runat="server">
public void UploadFile(object sender , EventArgs E)
{
   if(myFile.PostedFile!=null)
      {
		string FileServerDir=@"C:\Temp\";
                //Iam saving to a network drive F: change it to your
                //File server.Remember your web server needs write
                //access to your File Server..(Network Authentication)
		string fname = myFile.PostedFile.FileName;
		fname=fname.Substring(fname.LastIndexOf("\\")) ;
		//above line helps you the just the file name from the
               // full path
	       myFile.PostedFile.SaveAs(FileServerDir+fname) ;

	       Message.InnerHtml  = "File Successfully saved";
	          }
		  }
</script>

</head>
<body>
<h3> File Upload Utiliy </h3>
<form id="uploderform" method="post" action="FileUpload.aspx" 
enctype="multipart/form-data"

runat="server" >

<table border="1" cellspacing="2" cellpadding="2" >
<tr><td><h5>Select the File to upload</h5></td></tr>
<tr><td><input type="file" id="myFile" runat="server" ></td></tr>
<tr><td><input type="button"  value="Upload" OnServerClick="UploadFile" 
runat="server" >
</td></tr>
<tr><td><asp:label id="FileSave" text=" " runat="server" /></td></tr>
</table>
</form>
  <span id="Message" runat=server/>
</body>
</html>
