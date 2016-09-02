<?php
date_default_timezone_set('Europe/London');	
require_once("model/priceCalculator.php");
require_once("model/HTMLTableCreator.php");

$priceCalculator = new PriceCalculator();

if(!$priceCalculator->calculateFile("Products_CSV_file.csv"))
{	
	$html =  "<div class='errorBox'>".$importer->error."</div>";
	return;
}
// make a log object
$HTMLTableCreator = new HTMLTableCreator();
		
// create a log file
if(!$html = $HTMLTableCreator->createTable($priceCalculator->getResultsForHTMLTable())){
	$html =  "<div class='errorBox'>".$HTMLTableCreator->error."</div>";
	return;
}

// used for the demonstration on how to use the class methods 
$code = "TAP135";
?>