
<!-- This script and many more are available free online at -->

<!-- The JavaScript Source!! http://javascript.internet.com -->

<!-- Original:  Fred P -->

<!-- Dodification date:  11/23/2005 02:15 am -->

<!-- Begin

// Compare two options within a list by VALUES

function compareOptionValues(a, b)
{
	// Radix 10: for numeric values
	// Radix 36: for alphanumeric values
	var sA = parseInt( a.value, 36 );
	var sB = parseInt( b.value, 36 );
	return sA - sB;
}

// Compare two options within a list by TEXT

function compareOptionText(a, b)
{
	// Radix 10: for numeric values
	// Radix 36: for alphanumeric values
	var sA = parseInt( a.text, 36 );
	var sB = parseInt( b.text, 36 );
	return sA - sB;
}



// Dual list move function

function moveDualList( srcList, destList, moveAll )
{
	srcList = document.getElementById(srcList);
	destList = document.getElementById(destList);
	// Do nothing if nothing is selected

	if (  ( srcList.selectedIndex == -1 ) && ( moveAll == false )   )
	{
		return;
	}
	newDestList = new Array( destList.options.length );
	var len = 0;
	for( len = 0; len < destList.options.length; len++ )
	{
		if ( destList.options[ len ] != null )
		{
			newDestList[ len ] = new Option( destList.options[ len ].text, destList.options[ len ].value, destList.options[ len ].defaultSelected, destList.options[ len ].selected );
		}
	}
	for( var i = 0; i < srcList.options.length; i++ )
	{
		if ( srcList.options[i] != null && ( srcList.options[i].selected == true || moveAll ) )
		{
			// Statements to perform if option is selected
			// Incorporate into new list
			newDestList[ len ] = new Option( srcList.options[i].text, srcList.options[i].value, srcList.options[i].defaultSelected, srcList.options[i].selected );
			len++;
		}
	}
	// Sort out the new destination list
	newDestList.sort( compareOptionValues );   // BY VALUES
	//newDestList.sort( compareOptionText );   // BY TEXT
	// Populate the destination with the items from the new array
	for ( var j = 0; j < newDestList.length; j++ )
	{
		if ( newDestList[ j ] != null )
		{
			destList.options[ j ] = newDestList[ j ];
		}
	}
	// Erase source list selected elements
	for( var i = srcList.options.length - 1; i >= 0; i-- )
	{
		if ( srcList.options[i] != null && ( srcList.options[i].selected == true || moveAll ) )
		{
			// Erase Source
			//srcList.options[i].value = "";
			//srcList.options[i].text  = "";
			srcList.options[i]       = null;
		}
	}
} // End of moveDualList()

//  End -->

/**
* Build form input hidden with selected Right List itens
*
* @param HTMLElement form
* @param string rightListName
* @param string dualListName
* @return void
*/
function buildDualListField(form, rightListName, dualListName)
{
	var dualListFieldValue = "";
	var rightList = document.getElementById(rightListName);
	rightList.multiple = true;
	for (i=0; i<rightList.options.length; i++) {
		rightList.options[i].selected = true;
		dualListFieldValue += "," + rightList.options[i].value;
	}
	dualListFieldValue = dualListFieldValue.substr(1);
	var dualListField = document.createElement("input");
	dualListField.setAttribute("type", "hidden");
	dualListField.setAttribute("value", dualListFieldValue);
	dualListField.setAttribute("name", dualListName);
	rightList.appendChild(dualListField);
}
