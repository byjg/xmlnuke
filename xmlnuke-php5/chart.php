<?php
/*     
    PanaChart - PHP Chart Generator -  October 2003    
	
    Copyright (C) 2003 Eugen Fernea - eugenf@panacode.com
    Panacode Software - info@panacode.com
    http://www.panacode.com/
    
    Modified by Joao Gilberto Magalhaes to adapt into XMLNuke Project
    
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation;
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.    
*/  
		
	#############################################
	# To create a XMLNuke capable PHP5 page
	#
	require_once("xmlnuke.inc.php");
	#############################################

	// This class may called:
	//  chart.php?cn=class&param1=10&param2=20
	//   - or -
	//  chart.php?args=cn:class;param1:10;param2:20
	//
	//  The last form is necessary because in transformation the system didnt
	//  reconigze the second ampersand.
	$cnAux = split(";", $_REQUEST["args"]);
	foreach ($cnAux as $key=>$value)
	{
		$pair = split(":", $value);
		$_REQUEST[$pair[0]] = $pair[1];
	}
	
	$context = new Context();

	/*
	   You must have pass a parameter called CN.
	   Example: chart.php?cn=NAME
	   
	   XmlNuke will Try load the class called "NAME" and execute the Method:
	   getChartObject()
	*/
	try 
	{
		$cn = $context->ContextValue("cn");
		if ($cn!="")  
		{
			$ochart = ChartFactory::getChart($cn, $context);
			$ochart->plot("");
		}
		else 
		{
			// You need create a Class it have the method getChartObjet().
			// This method *must* return a PanaChart object.
			// The code like this:
	
			// Series
			$vSerie1 = array(10, 15, 20);
			$vSerie2 = array(8, 12, 8);
			$vLabels = array("A", "B", "C");
			
		    // AREA
		    $ochart = new chart(500,300,7, '#eeeeee');
		    $ochart->setTitle("You need pass: chart.php?cn=CHARTNAME","#000000",2);
		    $ochart->setPlotArea(SOLID,"#000000", '#ddddee');
		    $ochart->setLegend(SOLID, "#444444", "#ffffff", 1, '');
		    $ochart->addSeries($vSerie1,'bar','Serie 1', SOLID,'#000000', '#88ff88');
		    $ochart->addSeries($vSerie2,'line','Serie 2', LARGE_SOLID,'#ff8888', '#ff8888');
		    $ochart->setXAxis('#000000', SOLID, 1, "", '%s');
		    $ochart->setYAxis('#000000', SOLID, 1, "", '%d');
		    $ochart->setLabels($vLabels, '#000000', 1, VERTICAL);
		    $ochart->setGrid("#bbbbbb", DOTTED, "#bbbbbb", DOTTED);
		    $ochart->plot("");
		    
		    // Chart Types
		    // area, line, bar, impuls, spline, step, dot
		}
		
		Header("Content-Type: image/png");
	}
	catch (Exception $ex)
	{
		echo "Chart Error: " . $ex->getMessage();
	}

?>
