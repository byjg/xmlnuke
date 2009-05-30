<?php
class SampleChart
{
	protected $_context;
	
	public function __construct($context)
	{
		$this->_context = $context;
	}
	
	public function getChartObject()
	{	
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
	    return $ochart;
	}
}
?>