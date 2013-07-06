Const Title = "Rename config Files"

MsgBox "RENAME-CONFIG-FILES" + vbCrLf + "March-2009" + vbCrLf + "by Joao Gilberto Magalhaes" + vbCrLf + vbCrLf + "This batch will rename all config files to their proper names.", 0, Title 

Dim Result
Result = ""

RenameInsideDir(".")

MsgBox "Log Report: " + vbCrLf + vbCrLf + Result + vbCrLf + vbCrLf + "Done.", 0, Title

Function RenameInsideDir(Path)

	Set FSO = CreateObject("Scripting.FileSystemObject")
	Set objFolder = FSO.GetFolder(Path)
	For Each Files In objFolder.Files
		If InStr(1,Files.Name,".dist") > 1 Then
			FileBaseName = FSO.GetBaseName(Files.Name)
			NewName = FSO.GetBaseName(Files.Name)
			'WScript.Echo "NewName is ",NewName
			OldName = Files.Name
			On Error Resume Next
			Files.Name = NewName
			If Err.Number = 0 Then
				Result = Result + "Renamed from " + OldName + " to " + NewName + vbCrLf
			Else
				Result = Result + "File " + OldName + " already exists " + vbCrLf
			End if
			On Error Goto 0
		End If
	Next

	For Each Dir In objFolder.SubFolders
		If Instr(1, Dir, ".svn") < 1 Then
			RenameInsideDir(Dir)
		End If
	Next
End Function
