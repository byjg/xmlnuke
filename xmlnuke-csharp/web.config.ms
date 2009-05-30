<?xml version="1.0" encoding="utf-8" ?>
<configuration>

  <appSettings>
	<!-- Path where Snippets and sites are located (Don't add Slashs on the end)
			This Path can be VIRTUAL ou ABSOLUTE (e.g. C:\XMLNuke\Sites). 
			Set the parameter USEABSOLUTEPATHSROOTDIR to determine the behavior. 
	-->
	<add key="xmlnuke.ROOTDIR" value="~/sites" />

	<!-- If False set the ROOTDIR as VIRTUAL directory, and the system will be use the
			Server.MapPath statement. If True, the system will be the value typed here without
			any changes.
	-->
	<add key="xmlnuke.USEABSOLUTEPATHSROOTDIR" value="false" />

	<!-- The Virtual directory where the custom modules are located. 
			Note: xmlnuke.asp *must* be in this directory and the virtual directory XMLNukeEngine 
			and XMLNukeAdmin and modules *must* be below this directory.
	-->
	<add key="xmlnuke.URLMODULE" value="~" />

	<!-- This parameter set how the system will be replace the argument engine:xmlnuke and define the
			URL to call the xmlnuke engine.
	-->
	<add key="xmlnuke.URLXMLNUKEENGINE" value="~/xmlnuke.aspx" />

	<!-- Default site name	-->
	<add key="xmlnuke.DEFAULTSITE" value="default" />

	<!-- Default XSL style	-->
	<add key="xmlnuke.DEFAULTPAGE" value="page" />

	<!-- Enable/Disable the cache system on XMLNuke. Avoid set this option to false.
			This option is useful when you cannot change the write permissions, but the system will be slower.
	-->
	<add key="xmlnuke.ALWAYSUSECACHE" value="true" />

	<!-- Show or dont complete error messages. Set to false this option in production sites. -->
	<add key="xmlnuke.SHOWCOMPLETEERRORMESSAGES" value="true" />

	<!-- Languages XMLNuke Expected find -->
	<add key="xmlnuke.LANGUAGESAVAILABLE" value="en-us=English|pt-br=Portugues" />
	
	<!-- SmtpServer -->
	<add key="xmlnuke.SMTPSERVER" value="10.71.198.10" />
	
  </appSettings>

  <location path="sites">
	<system.web>
	   <authorization>
	      <deny users="*" />
	   </authorization>
	</system.web>
  </location>


  <!--
   <startup>
        <requiredRuntime imageVersion="v1.0.3705" version="v1.0.3705" />
    </startup>

   <runtime>
        <assemblyBinding xmlns="urn:schemas-microsoft-com:asm.v1">
            <dependentAssembly>
                <assemblyIdentity name="System.Xml" publicKeyToken="b77a5c561934e089" culture="neutral" />
                <bindingRedirect oldVersion="1.0.5000.0" newVersion="1.0.3300.0" />
            </dependentAssembly>
            <dependentAssembly>
                <assemblyIdentity name="System" publicKeyToken="b77a5c561934e089" culture="neutral" />
                <bindingRedirect oldVersion="1.0.5000.0" newVersion="1.0.3300.0" />
            </dependentAssembly>
        </assemblyBinding>
  </runtime>
  -->


  <system.web>

    <compilation defaultLanguage="c#" debug="false" />

    <!--  CUSTOM ERROR MESSAGES
          Set customErrors mode="On" or "RemoteOnly" to enable custom error messages, "Off" to disable. 
          Add <error> tags for each of the errors you want to handle.

          "On" Always display custom (friendly) messages.
          "Off" Always display detailed ASP.NET error information.
          "RemoteOnly" Display custom (friendly) messages only to users not running 
           on the local Web server. This setting is recommended for security purposes, so 
           that you do not display application detail information to remote clients.
    -->
    <customErrors mode="On" defaultRedirect="error.aspx" /> 

    <!--  AUTHENTICATION 
          This section sets the authentication policies of the application. Possible modes are "Windows", 
          "Forms", "Passport" and "None"

          "None" No authentication is performed. 
          "Windows" IIS performs authentication (Basic, Digest, or Integrated Windows) according to 
           its settings for the application. Anonymous access must be disabled in IIS. 
          "Forms" You provide a custom form (Web page) for users to enter their credentials, and then 
           you authenticate them in your application. A user credential token is stored in a cookie.
          "Passport" Authentication is performed via a centralized authentication service provided
           by Microsoft that offers a single logon and core profile services for member sites.
    -->
    <authentication mode="Windows" /> 

    <!--  AUTHORIZATION 
          This section sets the authorization policies of the application. You can allow or deny access
          to application resources by user or role. Wildcards: "*" mean everyone, "?" means anonymous 
          (unauthenticated) users.
    -->

    <authorization>
        <allow users="*" /> <!-- Allow all users -->
            <!--  <allow     users="[comma separated list of users]"
                             roles="[comma separated list of roles]"/>
                  <deny      users="[comma separated list of users]"
                             roles="[comma separated list of roles]"/>
            -->
    </authorization>

    <!--  APPLICATION-LEVEL TRACE LOGGING
          Application-level tracing enables trace log output for every page within an application. 
          Set trace enabled="true" to enable application trace logging.  If pageOutput="true", the
          trace information will be displayed at the bottom of each page.  Otherwise, you can view the 
          application trace log by browsing the "trace.axd" page from your web application
          root. 
    -->
    <trace
        enabled="false"
        requestLimit="10"
        pageOutput="false"
        traceMode="SortByTime"
		localOnly="true"
    />

    <!--  SESSION STATE SETTINGS
          By default ASP.NET uses cookies to identify which requests belong to a particular session. 
          If cookies are not available, a session can be tracked by adding a session identifier to the URL. 
          To disable cookies, set sessionState cookieless="true".
    -->
    <sessionState 
            mode="InProc"
            stateConnectionString="tcpip=127.0.0.1:42424"
            sqlConnectionString="data source=127.0.0.1;Trusted_Connection=yes"
            cookieless="false" 
            timeout="20" 
    />

    <!--  GLOBALIZATION
          This section sets the globalization settings of the application. 
    -->
    <globalization 
            requestEncoding="utf-8" 
            responseEncoding="utf-8" 
   />
   
    <httpHandlers>
        <add verb="*" 
             path="*.xmlnuke"
             type="com.xmlnuke.Handler.XmlNukeHandler, XmlNukeHandler"/>
   </httpHandlers>

 </system.web>

</configuration>
