Const Title = "Mount Release"

MsgBox "MOUNT-RELASE" + vbCrLf + "November-01-2011" + vbCrLf + "by Joao Gilberto Magalhaes" + vbCrLf + vbCrLf + "This batch will mount a valid XMLNuke release for Windows NTFS systems using symbolic links.", 0, Title 

Dim resp
resp = InputBox ( "XMLNuke Engine (e.g. php5 or csharp)", Title, "")

if (resp <> "") then

	Set wshShell = WScript.CreateObject ("WSCript.shell")

	' Mount Release
	wshshell.run "%COMSPEC% /C utils\windows\linkd.exe xmlnuke-" & resp & "\data xmlnuke-data", 6, True
	wshshell.run "%COMSPEC% /C utils\windows\linkd.exe xmlnuke-" & resp & "\common xmlnuke-common", 6, True

	set wshshell = nothing

	MsgBox "Done.", 0, Title

else

	MsgBox "Nothing to do.", 0, Title

end if
