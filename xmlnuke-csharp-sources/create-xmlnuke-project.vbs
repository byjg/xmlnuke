Const Title = "Create Xmlnuke Project"

MsgBox "CREATE XMLNUKE PROJECT" + vbCrLf + "June-24-2007" + vbCrLf + "by Joao Gilberto Magalhaes" + vbCrLf + vbCrLf + "Use this batch to create a XMLNuke project in Visual Studio.", 0, Title 

Dim source
source = ""
Set filesys = CreateObject("Scripting.FileSystemObject")
if filesys.FileExists(".\xmlnuke-csharp\xmlnuke.aspx") then
	source = ".\xmlnuke-csharp"
elseif filesys.FileExists("..\xmlnuke-csharp\xmlnuke.aspx") then
	source = "..\xmlnuke-csharp"
else
	MsgBox "Xmlnuke release not found!!! Cannot continue."	
end if


if source <> "" then

	Dim resp
	resp = InputBox ( "Enter the path for the new XMLNuke project", Title, "C:\Projects\Xmlnuke")
	Dim project
	project = InputBox ( "Enter the project name", Title, "NewProject")

	if (resp <> "" and project <> "") then

		MsgBox "The process may take several minutes. Press OK and wait until it is done.", 0, Title

		Set wshShell = WScript.CreateObject ("WSCript.shell")

		basedir = resp & "\" & project & "\" 
		xmlnukedir = basedir & "xmlnuke"
		classdir = basedir & "Classes"

		wshshell.run "%COMSPEC% /C mkdir """ & xmlnukedir & """", 6, True
		wshshell.run "%COMSPEC% /C mkdir """ & classdir & """", 6, True
		wshshell.run "%COMSPEC% /C xcopy " & source & " /s/e/h """ & xmlnukedir & """", 6, True
	
		set wshshell = nothing

		'
		' Creating Solution
		'
		Set filetxt = filesys.CreateTextFile(basedir & project & ".sln", True)
		filetxt.WriteLine "Microsoft Visual Studio Solution File, Format Version 10.00"
		filetxt.WriteLine "# Visual Studio 2008"
		filetxt.WriteLine "Project(""{E24C65DC-7377-472B-9ABA-BC803B73C61A}"") = ""xmlnuke"", ""xmlnuke\"", ""{ABC73716-D1E9-4D3B-8B25-016FE8DE36D3}"""
		filetxt.WriteLine "	ProjectSection(WebsiteProperties) = preProject"
		filetxt.WriteLine "		Debug.AspNetCompiler.VirtualPath = ""/xmlnuke"""
		filetxt.WriteLine "		Debug.AspNetCompiler.PhysicalPath = ""xmlnuke\"""
		filetxt.WriteLine "		Debug.AspNetCompiler.TargetPath = ""PrecompiledWeb\xmlnuke\"""
		filetxt.WriteLine "		Debug.AspNetCompiler.Updateable = ""true"""
		filetxt.WriteLine "		Debug.AspNetCompiler.ForceOverwrite = ""true"""
		filetxt.WriteLine "		Debug.AspNetCompiler.FixedNames = ""false"""
		filetxt.WriteLine "		Debug.AspNetCompiler.Debug = ""True"""
		filetxt.WriteLine "		Release.AspNetCompiler.VirtualPath = ""/xmlnuke"""
		filetxt.WriteLine "		Release.AspNetCompiler.PhysicalPath = ""xmlnuke\"""
		filetxt.WriteLine "		Release.AspNetCompiler.TargetPath = ""PrecompiledWeb\xmlnuke\"""
		filetxt.WriteLine "		Release.AspNetCompiler.Updateable = ""true"""
		filetxt.WriteLine "		Release.AspNetCompiler.ForceOverwrite = ""true"""
		filetxt.WriteLine "		Release.AspNetCompiler.FixedNames = ""false"""
		filetxt.WriteLine "		Release.AspNetCompiler.Debug = ""False"""
		filetxt.WriteLine "		VWDPort = ""1479"""
		filetxt.WriteLine "	EndProjectSection"
		filetxt.WriteLine "EndProject"
		filetxt.WriteLine "Project(""{FAE04EC0-301F-11D3-BF4B-00C04F79EFBC}"") = """ & project & """, ""Classes\" & project & ".csproj"", ""{231753CD-44BC-4ABF-85DA-43D6AA0940DD}"""
		filetxt.WriteLine "EndProject"
		filetxt.WriteLine "Global"
		filetxt.WriteLine "	GlobalSection(SolutionConfigurationPlatforms) = preSolution"
		filetxt.WriteLine "		Debug|Any CPU = Debug|Any CPU"
		filetxt.WriteLine "		Release|Any CPU = Release|Any CPU"
		filetxt.WriteLine "	EndGlobalSection"
		filetxt.WriteLine "	GlobalSection(ProjectConfigurationPlatforms) = postSolution"
		filetxt.WriteLine "		{231753CD-44BC-4ABF-85DA-43D6AA0940DD}.Debug|Any CPU.ActiveCfg = Debug|Any CPU"
		filetxt.WriteLine "		{231753CD-44BC-4ABF-85DA-43D6AA0940DD}.Debug|Any CPU.Build.0 = Debug|Any CPU"
		filetxt.WriteLine "		{231753CD-44BC-4ABF-85DA-43D6AA0940DD}.Release|Any CPU.ActiveCfg = Release|Any CPU"
		filetxt.WriteLine "		{231753CD-44BC-4ABF-85DA-43D6AA0940DD}.Release|Any CPU.Build.0 = Release|Any CPU"
		filetxt.WriteLine "		{ABC73716-D1E9-4D3B-8B25-016FE8DE36D3}.Debug|Any CPU.ActiveCfg = Debug|Any CPU"
		filetxt.WriteLine "		{ABC73716-D1E9-4D3B-8B25-016FE8DE36D3}.Debug|Any CPU.Build.0 = Debug|Any CPU"
		filetxt.WriteLine "		{ABC73716-D1E9-4D3B-8B25-016FE8DE36D3}.Release|Any CPU.ActiveCfg = Release|Any CPU"
		filetxt.WriteLine "		{ABC73716-D1E9-4D3B-8B25-016FE8DE36D3}.Release|Any CPU.Build.0 = Release|Any CPU"
		filetxt.WriteLine "	EndGlobalSection"
		filetxt.WriteLine "	GlobalSection(SolutionProperties) = preSolution"
		filetxt.WriteLine "		HideSolutionNode = FALSE"
		filetxt.WriteLine "	EndGlobalSection"
		filetxt.WriteLine "EndGlobal"
		filetxt.Close

		'
		' Creating Project
		'
		Set filetxt = filesys.CreateTextFile(classdir & "\" & project & ".csproj", True)
		filetxt.WriteLine "<Project DefaultTargets=""Build"" xmlns=""http://schemas.microsoft.com/developer/msbuild/2003"" ToolsVersion=""3.5"">"
		filetxt.WriteLine "  <PropertyGroup>"
		filetxt.WriteLine "    <Configuration Condition="" '$(Configuration)' == '' "">Debug</Configuration>"
		filetxt.WriteLine "    <Platform Condition="" '$(Platform)' == '' "">AnyCPU</Platform>"
		filetxt.WriteLine "    <ProductVersion>8.0.50727</ProductVersion>"
		filetxt.WriteLine "    <SchemaVersion>2.0</SchemaVersion>"
		filetxt.WriteLine "    <ProjectGuid>{231753CD-44BC-4ABF-85DA-43D6AA0940DD}</ProjectGuid>"
		filetxt.WriteLine "    <OutputType>Library</OutputType>"
		filetxt.WriteLine "    <AppDesignerFolder>Properties</AppDesignerFolder>"
		filetxt.WriteLine "    <RootNamespace>" & project & "</RootNamespace>"
		filetxt.WriteLine "    <AssemblyName>" & project & "</AssemblyName>"
		filetxt.WriteLine "    <FileUpgradeFlags>"
		filetxt.WriteLine "    </FileUpgradeFlags>"
		filetxt.WriteLine "    <OldToolsVersion>2.0</OldToolsVersion>"
		filetxt.WriteLine "    <UpgradeBackupLocation>"
		filetxt.WriteLine "    </UpgradeBackupLocation>" 
		filetxt.WriteLine "  </PropertyGroup>"
		filetxt.WriteLine "  <PropertyGroup Condition="" '$(Configuration)|$(Platform)' == 'Debug|AnyCPU' "">"
		filetxt.WriteLine "    <DebugSymbols>true</DebugSymbols>"
		filetxt.WriteLine "    <DebugType>full</DebugType>"
		filetxt.WriteLine "    <Optimize>false</Optimize>"
		filetxt.WriteLine "    <OutputPath>..\xmlnuke\bin\</OutputPath>"
		filetxt.WriteLine "    <DefineConstants>DEBUG;TRACE</DefineConstants>"
		filetxt.WriteLine "    <ErrorReport>prompt</ErrorReport>"
		filetxt.WriteLine "    <WarningLevel>4</WarningLevel>"
		filetxt.WriteLine "  </PropertyGroup>"
		filetxt.WriteLine "  <PropertyGroup Condition="" '$(Configuration)|$(Platform)' == 'Release|AnyCPU' "">"
		filetxt.WriteLine "    <DebugType>pdbonly</DebugType>"
		filetxt.WriteLine "    <Optimize>true</Optimize>"
		filetxt.WriteLine "    <OutputPath>..\xmlnuke\bin\</OutputPath>"
		filetxt.WriteLine "    <DefineConstants>TRACE</DefineConstants>"
		filetxt.WriteLine "    <ErrorReport>prompt</ErrorReport>"
		filetxt.WriteLine "    <WarningLevel>4</WarningLevel>"
		filetxt.WriteLine "  </PropertyGroup>"
		filetxt.WriteLine "  <ItemGroup>"
		filetxt.WriteLine "    <Reference Include=""com.xmlnuke, Version=2.1.2729.34151, Culture=neutral, processorArchitecture=MSIL"">"
		filetxt.WriteLine "      <SpecificVersion>False</SpecificVersion>"
		filetxt.WriteLine "      <HintPath>..\xmlnuke\bin\com.xmlnuke.dll</HintPath>"
		filetxt.WriteLine "    </Reference>"
		filetxt.WriteLine "    <Reference Include=""com.xmlnuke.db, Version=1.0.2724.34984, Culture=neutral, processorArchitecture=MSIL"">"
		filetxt.WriteLine "      <SpecificVersion>False</SpecificVersion>"
		filetxt.WriteLine "      <HintPath>..\xmlnuke\bin\com.xmlnuke.db.dll</HintPath>"
		filetxt.WriteLine "    </Reference>"
		filetxt.WriteLine "    <Reference Include=""System"" />"
		filetxt.WriteLine "    <Reference Include=""System.Data"" />"
		filetxt.WriteLine "    <Reference Include=""System.Xml"" />"
		filetxt.WriteLine "  </ItemGroup>"
		filetxt.WriteLine "  <ItemGroup>"
		filetxt.WriteLine "    <Compile Include=""NewModule.cs"" />"
		filetxt.WriteLine "  </ItemGroup>"
		filetxt.WriteLine "  <Import Project=""$(MSBuildBinPath)\Microsoft.CSharp.targets"" />"
		filetxt.WriteLine "  <!-- To modify your build process, add your task inside one of the targets below and uncomment it. "
		filetxt.WriteLine "       Other similar extension points exist, see Microsoft.Common.targets."
		filetxt.WriteLine "  <Target Name=""BeforeBuild"">"
		filetxt.WriteLine "  </Target>"
		filetxt.WriteLine "  <Target Name=""AfterBuild"">"
		filetxt.WriteLine "  </Target>"
		filetxt.WriteLine "  -->"
		filetxt.WriteLine "</Project>"
		filetxt.Close

		'
		' Creating First Module
		'
		Set filetxt = filesys.CreateTextFile(classdir & "\NewModule.cs", True)
		filetxt.WriteLine "using System;"
		filetxt.WriteLine "using System.Collections.Generic;"
		filetxt.WriteLine "using System.Text;"
		filetxt.WriteLine ""
		filetxt.WriteLine "// Xmlnuke"
		filetxt.WriteLine "using com.xmlnuke.admin;"
		filetxt.WriteLine "using com.xmlnuke.anydataset;"
		filetxt.WriteLine "using com.xmlnuke.classes;"
		filetxt.WriteLine "using com.xmlnuke.database;"
		filetxt.WriteLine "using com.xmlnuke.db;"
		filetxt.WriteLine "using com.xmlnuke.engine;"
		filetxt.WriteLine "using com.xmlnuke.exceptions;"
		filetxt.WriteLine "using com.xmlnuke.international;"
		filetxt.WriteLine "using com.xmlnuke.module;"
		filetxt.WriteLine "using com.xmlnuke.processor;"
		filetxt.WriteLine "using com.xmlnuke.util;"
		filetxt.WriteLine ""
		filetxt.WriteLine "namespace " & project
		filetxt.WriteLine "{"
		filetxt.WriteLine "    public class NewModule : BaseModule"
		filetxt.WriteLine "    {"
		filetxt.WriteLine "        public override IXmlnukeDocument CreatePage()"
		filetxt.WriteLine "        {"
		filetxt.WriteLine "			// Load default Language Collection"
		filetxt.WriteLine "			this._myWords = this.WordCollection();"
		filetxt.WriteLine ""
		filetxt.WriteLine "			// Create a XMLNuke document"
		filetxt.WriteLine "                 this.defaultXmlnukeDocument = new XmlnukeDocument(this._myWords.Value(""TITLE""), this._myWords.Value(""ABSTRACT""));"
		filetxt.WriteLine ""
		filetxt.WriteLine "			// Create a Block, a Paragraph and a Text"
		filetxt.WriteLine "			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value(""TITLE""), BlockPosition.Center);"
		filetxt.WriteLine "			XmlParagraphCollection paragraph = new XmlParagraphCollection();"
		filetxt.WriteLine "			paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value(""FIRST_TEXT"")));"
		filetxt.WriteLine "			block.addXmlnukeObject(paragraph);"
		filetxt.WriteLine "			this.defaultXmlnukeDocument.addXmlnukeObject(block);"
		filetxt.WriteLine ""
		filetxt.WriteLine "            //"
		filetxt.WriteLine "            return this.defaultXmlnukeDocument;"
		filetxt.WriteLine "        }"
		filetxt.WriteLine "    }"
		filetxt.WriteLine "}"
		filetxt.Close

		Set filetxt = filesys.CreateTextFile(xmlnukedir & "\Default.aspx", True)
		filetxt.WriteLine "<%@ Page Language=""C#"" %>"
		filetxt.WriteLine "<script runat=""server"">"
		filetxt.WriteLine "	void Page_Load(Object sender, EventArgs e) "
		filetxt.WriteLine "	{"
		filetxt.WriteLine "		Response.Redirect(""xmlnuke.aspx?module=" & project & ".NewModule"");"
		filetxt.WriteLine "	}"
		filetxt.WriteLine "</script>"
		filetxt.Close
		
		MsgBox "Done.", 0, Title

	else

		MsgBox "Nothing to do.", 0, Title

	end if

end if
