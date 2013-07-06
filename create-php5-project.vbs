Const Title = "Create PHP5 Project"

MsgBox "CREATE PHP5 PROJECT" + vbCrLf + "@ 2012" + vbCrLf + "by Joao Gilberto Magalhaes" + vbCrLf + vbCrLf + "Use this batch to create a XMLNuke PHP5 project ready to use on PDT Eclipse or another editor.", 0, Title 

Dim xmlnukeDir, phpDir, dataDir
xmlnukeDir = ""
phpDir = ""
dataDir = ""

Set filesys = CreateObject("Scripting.FileSystemObject")
xmlnukeDir = filesys.GetParentFolderName(Wscript.ScriptFullName)

if filesys.FileExists(xmlnukeDir & "\xmlnuke-php5\xmlnuke.php") then
	phpDir = xmlnukeDir & "\xmlnuke-php5"
        dataDir = xmlnukeDir & "\xmlnuke-data"
	if not filesys.FileExists(dataDir & "\anydataset.dtd") then
		dataDir = phpDir & "\data"
		if not filesys.FileExists(dataDir & "\anydataset.dtd") then
			MsgBox "Xmlnuke release not found!!! Cannot continue."
			phpDir = ""
		end if
	end if
else
	MsgBox "XMLNuke release not found!!! Cannot continue."	
	phpDir = ""
end if


if phpDir <> "" then

	Dim home
	home = InputBox ( "Enter the path for the new XMLNuke PHP5 Project", Title, "C:\Projects\XMLNuke")
	Dim site
	site = InputBox ( "Enter the site name", Title, "mysite")
	Dim project
	project = InputBox ( "Enter the project name", Title, "MyProject")
	Dim langStr
	langStr = InputBox ( "Enter the available languages", Title, "en-us pt-br")

	if (home <> "" and site <> "" and langStr<>"") then

		MsgBox "The process may take several minutes. Press OK and wait until it is done.", 0, Title

		Set wshShell = WScript.CreateObject ("WSCript.shell")

		'
		' COPYING FILES
		' 
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\imagevalidate.php""  """ & home & """", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\xmlnukeadmin.php""  """ & home & """", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\xmlnuke.inc.php""  """ & home & """", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\check_install.php.dist""  """ & home & "\check_install.php""", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\index.php.dist""  """ & home & "\index.php""", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\xmlnuke.php""  """ & home & """", 6, True

		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\writepage.inc.php.dist""  """ & home & "\writepage.inc.php""", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\unittest.php""  """ & home & """", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\webservice.php""  """ & home & """", 6, True
		wshshell.run "%COMSPEC% /C copy """ & phpDir & "\chart.php""  """ & home & """", 6, True


		'
		' Creating Directory Structure
		'
		Set filetxt = filesys.CreateTextFile(home & "\config.inc.php", True)
		filetxt.WriteLine ""
		filetxt.Close
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/static""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/anydataset""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/cache""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/lang""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/offline""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/xml""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/xsl""", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & home & "/data/snippet""", 6, True

		langs = Split(langStr, " ")
		for i=0 to Ubound(langs)

			wshshell.run "%COMSPEC% /C mkdir """ & home & "\data\xml\" & langs(i) & """", 6, True

			wshshell.run "%COMSPEC% /C copy """ & dataDir & "\sites\index.xsl.template""  """ & home & "\data\xsl\index." & langs(i) & ".xsl""", 6, True
			wshshell.run "%COMSPEC% /C copy """ & dataDir & "\sites\page.xsl.template""  """ & home & "\data\xsl\page." & langs(i) & ".xsl""", 6, True
			wshshell.run "%COMSPEC% /C copy """ & dataDir & "\sites\index.xml.template""  """ & home & "\data\xml\" & langs(i) & "\index." & langs(i) & ".xml""", 6, True
			wshshell.run "%COMSPEC% /C copy """ & dataDir & "\sites\home.xml.template""  """ & home & "\data\xml\" & langs(i) & "\home." & langs(i) & ".xml""", 6, True
			wshshell.run "%COMSPEC% /C copy """ & dataDir & "\sites\notfound.xml.template""  """ & home & "\data\xml\" & langs(i) & "\notfound." & langs(i) & ".xml""", 6, True
			Set filetxt = filesys.CreateTextFile(home & "\data\xml\" & langs(i) & "\index.php.btree", True)
			filetxt.WriteLine "xmlnuke"
			filetxt.WriteLine "+home." + langs(i) + ".xml"
			filetxt.Close
		next

		wshshell.run "%COMSPEC% /C mkdir """ & home & "\lib""", 6, True
		GrepSed dataDir & "\sites\_includelist.php.template", home & "\lib\_includelist.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)

		wshshell.run "%COMSPEC% /C mkdir """ & home & "\lib\modules""", 6, True
		GrepSed dataDir & "\sites\module.php.template", home & "\lib\modules\home.class.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)

		wshshell.run "%COMSPEC% /C mkdir """ & home & "\lib\base""", 6, True
		GrepSed dataDir & "\sites\adminbasemodule.php.template", home & "\lib\base\" & LCase(project) & "adminbasemodule.class.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)
		GrepSed dataDir & "\sites\basedbaccess.php.template", home & "\lib\base\" & LCase(project) & "basedbaccess.class.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)
		GrepSed dataDir & "\sites\basemodel.php.template", home & "\lib\base\" & LCase(project) & "basemodel.class.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)
		GrepSed dataDir & "\sites\basemodule.php.template", home & "\lib\base\" & LCase(project) & "basemodule.class.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)
		GrepSed dataDir & "\sites\baseuiedit.php.template", home & "\lib\base\" & LCase(project) & "baseuiedit.class.php", "__PROJECT__", project, "__PROJECT_FILE__", LCase(project)
			

		' Creating Anydataset 
		Set filetxt = filesys.CreateTextFile(home & "\data\anydataset\_db.anydata.xml", True)
		filetxt.WriteLine "<?xml version=""1.0"" encoding=""utf-8""?>"
		filetxt.WriteLine "<anydataset>"
		filetxt.WriteLine "	<row>"
		filetxt.WriteLine "		<field name=""dbname"">" & LCase(project) & "</field>"
		filetxt.WriteLine "		<field name=""dbtype"">dsn</field>"
		filetxt.WriteLine "		<field name=""dbconnectionstring"">mysql://root@localhost/" & LCase(project) & "</field>"
		filetxt.WriteLine "	</row>"
		filetxt.WriteLine "</anydataset>"
		filetxt.Close

		' Creating ConfigEmail 
		Set filetxt = filesys.CreateTextFile(home & "\data\anydataset\_configemail.anydata.xml", True)
		filetxt.WriteLine "<?xml version=""1.0"" encoding=""utf-8""?>"
		filetxt.WriteLine "<anydataset>"
		filetxt.WriteLine "	<row>"
		filetxt.WriteLine "		<field name=""destination_id"">DEFAULT</field>"
		filetxt.WriteLine "		<field name=""email"">youremail@provider.com</field>"
		filetxt.WriteLine "		<field name=""name"">Your Name</field>"
		filetxt.WriteLine "	</row>"
		filetxt.WriteLine "</anydataset>"
		filetxt.Close

		'
		' Creating Config file
		'
		Set filetxt = filesys.CreateTextFile(home & "\config.default.php", True)
		filetxt.WriteLine "<?php"
		filetxt.WriteLine "# This file was generated by create-php5-project.sh. "
		filetxt.WriteLine "# You can safely remove this file after you XMLNuke installation is running."
		filetxt.WriteLine "$configValues[""xmlnuke.ROOTDIR""]='" & dataDir & "'; "
		filetxt.WriteLine "$configValues[""xmlnuke.USEABSOLUTEPATHSROOTDIR""] = true; "
		filetxt.WriteLine "$configValues[""xmlnuke.DEFAULTSITE""]='" & site & "'; "
		filetxt.WriteLine "$configValues[""xmlnuke.EXTERNALSITEDIR""] = '" & site & "=" & home & "\data'; "
		filetxt.WriteLine "$configValues[""xmlnuke.PHPLIBDIR""] = '" & LCase(project) & "=" & home & "\lib'; "
		filetxt.WriteLine "$configValues[""xmlnuke.PHPXMLNUKEDIR""] = '" & phpDir & "'; "
		filetxt.WriteLine "?>"
		filetxt.Close

		'
		' Creating Post Install Notes
		'
		Set filetxt = filesys.CreateTextFile(home & "\post_install_notes.txt", True)
		filetxt.WriteLine "You must do some configurations manualy:"
		filetxt.WriteLine "  - Point the document root on your Web Server to """ & home & """ "
		filetxt.WriteLine "  - Create virtual directory called ""common"" pointing to """ & xmlnukeDir & "\xmlnuke-common"" "
		filetxt.WriteLine "  - Grant WRITE permissions to folder """ & home & "\data"" "
		filetxt.WriteLine "  - Grant WRITE permissions to file """ & home & "\config.inc.php"" "
		filetxt.WriteLine ""
		filetxt.WriteLine "After this you can play with these URLs:"
		filetxt.WriteLine "http://localhost/xmlnuke.php?xml=home"
		filetxt.WriteLine "http://localhost/xmlnuke.php?module=" & LCase(project) & ".home"
		filetxt.Close
		
		MsgBox "Done." & vbCrLf & vbCrLf & "Press OK to read the post install notes.", 0, Title

		wshshell.run "%windir%\notepad """ & home & "\post_install_notes.txt""", 1, true
		set wshshell = nothing


	else

		MsgBox "Nothing to do.", 0, Title

	end if

end if



Sub GrepSed(source, target, search1, replace1, search2, replace2)

	Set objFSO = CreateObject("Scripting.FileSystemObject")

	Set objSource = objFSO.OpenTextFile(source)
	strContents = objSource.ReadAll
	objSource.Close

	strContents = Replace(Replace(strContents, search1, replace1), search2, replace2)

	Set objTarget = objFSO.CreateTextFile(target, True)
	objTarget.Write(strContents)
	objTarget.Close

End Sub
