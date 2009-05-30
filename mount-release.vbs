Const Title = "Mount Release"

MsgBox "MOUNT-RELASE" + vbCrLf + "January-10-2007" + vbCrLf + "by Joao Gilberto Magalhaes" + vbCrLf + vbCrLf + "This batch will mount a valid XMLNuke release.", 0, Title 

Dim resp
resp = InputBox ( "XMLNuke Engine (e.g. php5 or csharp)", Title, "")

if (resp <> "") then

	MsgBox "The process may take several minutes. Press OK and wait until it is done.", 0, Title

	Set wshShell = WScript.CreateObject ("WSCript.shell")

	' Mount Release
	wshshell.run "%COMSPEC% /C mkdir xmlnuke-" & resp & "\data", 6, True
	wshshell.run "%COMSPEC% /C xcopy xmlnuke-data /s/e/h xmlnuke-" & resp & "\data", 6, True
	wshshell.run "%COMSPEC% /C mkdir xmlnuke-" & resp & "\common", 6, True
	wshshell.run "%COMSPEC% /C xcopy xmlnuke-common /s/e/h xmlnuke-" & resp & "\common", 6, True

	set wshshell = nothing

	MsgBox "Done.", 0, Title

else

	MsgBox "Nothing to do.", 0, Title

end if