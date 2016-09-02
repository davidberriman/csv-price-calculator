<?php
date_default_timezone_set('Europe/London');	
require_once("model/priceCalculator.php");
require_once("model/priceCalculatorTests.php");
require_once("model/HTMLTableCreator.php");

$priceCalculatorTests = new PriceCalculatorTests();
if(!$priceCalculatorTests->runTests()){
	$html =  "<div class='errorBox'>".$priceCalculatorTests->error."</div>";
	return;
}

// make a log object
$HTMLTableCreator = new HTMLTableCreator();
		
// create a log file
if(!$html = $HTMLTableCreator->createTable($priceCalculatorTests->getResultsForHTMLTable())){
	$html =  "<div class='errorBox'>".$HTMLTableCreator->error."</div>";
	return;
}

// used for the demonstration on how to use the class methods 
$code = "TAP135";
?>