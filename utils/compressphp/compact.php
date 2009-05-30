<?php


/** Removes Comments, Tabs, Spaces and CRLFs
    Things not handeled:
    - mixed PHP and HTML code in one file
    - echo <<<EOT EOT; statements
    @Author: Hannes Dorn
    @Company: IBIT.at
    @Homepage: http://www.ibit.at
    @Email: hannes.dorn@ibit.at
    @Comment: If it was hard to write, it should be hard to {read|understand}
    @Use: Free (GPL) as long as you do not remove this header.
    @Param: $sText Buffer with PHP Code
    @Return: String
*/
function CompactPhp( $sText )
{
    // Search for PHP Block Begin
    $i = strpos( $sText, '<?' );
    if ( $i === false )
        die( "CompactPhp: Invalid Buffer, need <? to find start.\n" );
    $i = $i + 2;

    // Search for PHP Block End
    $iStop = strpos( $sText, '?>' );
    if ( $iStop === false )
        die( "CompactPhp: Invalid Buffer, need ?> to find end.\n" );

    // Start > End?
    if ( $i > $iStop )
        die( "CompactPhp: Invalid Buffer, start > end!\n" );

    // Copy Start
    $sBuffer = substr( $sText, 0, $i );

    // Compact and Copy PHP Source Code.
    $sChar = '';
    $sLast = '';
    $sWanted = '';
    $fEscape = false;
    for( $i = $i; $i < $iStop; $i++ )
    {
        $sLast = $sChar;
        $sChar = substr( $sText, $i, 1 );

        // \ in a string marks possible an escape sequence
        if ( $sChar == '\\' )
            // are we in a string?
            if ( $sWanted == '"' || $sWanted == "'" )
                // if we are not in an escape sequence, turn it on
                // if we are in an escape sequence, turn it off
                $fEscape = !$fEscape;

        // " marks start or end of a string
        if ( $sChar == '"' && !$fEscape )
            if ( $sWanted == '' )
                $sWanted = '"';
            else
                if ( $sWanted == '"' )
                    $sWanted = '';

        // ' marks start or end of a string
        if ( $sChar == "'" && !$fEscape )
            if ( $sWanted == '' )
                $sWanted = "'";
            else
                if ( $sWanted == "'" )
                    $sWanted = '';

        // // marks start of a comment
        if ( $sChar == '/' && $sWanted == '' )
            if ( substr( $sText, $i + 1, 1 ) == '/' )
            {
                $sWanted = "\n";
                $i++;
                continue;
            }

        // \n marks possible end of comment
        if ( $sChar == "\n" && $sWanted == "\n" )
        {
            $sWanted = '';
            continue;
        }

        // /* marks start of a comment
        if ( $sChar == '/' && $sWanted == '' )
            if ( substr( $sText, $i + 1, 1 ) == '*' )
            {
                $sWanted = "*/";
                $i++;
                continue;
            }

        // */ marks possible end of comment
        if ( $sChar == '*' && $sWanted == '*/' )
            if ( substr( $sText, $i + 1, 1 ) == '/' )
            {
                $sWanted = '';
                $i++;
                continue;
            }

        // if we have a tab or a crlf replace it with a blank and continue if we had one recently
        if ( ( $sChar == "\t" || $sChar == "\n" || $sChar == "\r" ) && $sWanted == '' )
        {
            $sChar = ' ';
            if ( $sLast == ' ' )
                continue;
        }

        // skip blanks only if previous char was a blank or nothing
        if ( $sChar == ' ' && ( $sLast == ' ' || $sLast == '' ) && $sWanted == '' )
            continue;

        // add char to buffer if we are not inside a comment
        if ( $sWanted == '' || $sWanted == '"' || $sWanted == "'" )
            $sBuffer .= $sChar;

        // if we had an escape sequence and the actual char isn't the escape char, cancel escape sequence...
        // since we are only interested in escape sequences of \' and \".
        if ( $fEscape && $sChar != '\\' )
            $fEscape = false;
    }

    // Copy Rest
    $sBuffer .= substr( $sText, $iStop );

    return( $sBuffer );
}

/*
if ($argc == 2)
{
	$texto = file_get_contents($argv[1]);
	$texto_compress = CompactPhp($texto);
	echo "";
	echo "$texto_compress";
}
else
{
	echo "\nWrong parameters count. You need pass the file you want to compress\n";
}
*/
?>
