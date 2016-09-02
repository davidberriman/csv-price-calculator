<?php
require_once('csvFile.php');
require_once('sanitize.php');
# ========================================================================#
#
#  Author:    David Berriman
#  Version:	  1.0
#  Date:      01/09/2016
#  Purpose:   Import product data from CSV and calculate amounts
#  Plblic functions: 
#  			   importCSV : import the CSV file specified 
#
#  Usage Example:
#                     require_once("fileImporter.php");
#                     $importer = new FileImporter();
#                     $importer->calculateFile(filename.csv);  // check this equals true
#
# ========================================================================#

class PriceCalculator extends CSVFile
{
	// ------------------------------------------------------
	// Class variables
	// ------------------------------------------------------
	public  $numberProcessed = 0; // index used for arrays

	// column numbers in CSV file
	private  $columnProductCode; // should be 0
	private  $columnProductPriceWithTax; // should be 1
	private  $columnPriceAddition; // should be 2
	private  $taxAdjustment; // should be 3
	
	// define tax amount added to column 1 in CSV
	private  $taxAmount; 
	
	// Class arrays for CSV data
	private  $CSVHTMLArray = array();
	private  $CSVDataArray = array();
	private  $currencyRates = array();
	
	// the associated array indexes for the different calculations:
	static  $code = "code"; // product code
	static $price = "price"; // product code
	static $priceAddition = "priceAddition"; // price_addition_without_tax column
	static $priceWithAddition = "priceWithAddition"; // Add the value from the price_addition column to the price (with no tax)
	static $priceWithTax = "priceWithTax"; // Apply the tax rate to the price 
	static $priceWithAdditionAndTax = "priceWithAdditionAndTax"; // Add the value from the price_addition column to the price then add tax
	static $tax = "tax"; // tax rate (as defined in column 4)
	
	// column titles expected in the csv file
	private $expectedColumnHeadings = array(
		'product_sku', 
		'price_with_tax_rate_of_20_percent_applied', 
		'price_addition_without_tax', 
		'tax_rate_adjustment_in_percentage'
	);

	
	// -------------------------------------------------------------------
	// Init class variables
	// -------------------------------------------------------------------
	function __construct(){

		// define the amount of tax applied to the 
		// price_with_tax_rate_of_20_percent_applied column
		$this->taxAmount = 20; 
	}
	
	
	// -------------------------------------------------------------------
	// Import CSV file
	// -------------------------------------------------------------------
	public function calculateFile($file){
		
		// init parent class vars with the csv file
		parent::__construct($file);

		// call parent function to validate the CSV
		if(!$this->parseCSV())
		{
			return false;
		}
		
		// get the column numbers for the required items
		// this way changes in the CSV file structure
		// will not break this class
		$this->getColumnNumbers();
			
		// check column headings have been found in the CSV file
		if(!$this->checkColumnNumbers())
		{
			return false;
		}
				
		// remove the first line which is a row of headings
		unset($this->CSVLineArray[0]);
		
		// process array to import data
		if(!$this->processCSVLines())
		{
			return false;
		}
		
		return true;
	}
	
	
	
	// -------------------------------------------------------------------
	// Return array with results from processing with readable 
	// table headings
	// -------------------------------------------------------------------
	public function getResultsForHTMLTable(){
		return $this->CSVHTMLArray;	
	}
	
	
	// -------------------------------------------------------------------
	// Return array with results from processing with product code as
	// array index and the static variables as the array ids
	// -------------------------------------------------------------------
	public function getCSVData(){
		return $this->CSVDataArray;
	}
	
	
	// -------------------------------------------------------------------
	// Return price (no tax) for product code
	// -------------------------------------------------------------------
	public function getPriceNoTaxForProductCode($code){
		return $this->CSVDataArray[$code][self::$price];
	}
	
	
	// -------------------------------------------------------------------
	// Return price (no tax) for product code in pence
	// -------------------------------------------------------------------
	public function getPriceNoTaxForProductCodeInPence($code){
		return $this->priceInPence($this->CSVDataArray[$code][self::$price]);
	}
	
	
	// -------------------------------------------------------------------
	// Return price (with tax) for product code
	// -------------------------------------------------------------------
	public function getPriceWithTaxForProductCode($code){
		return $this->CSVDataArray[$code][self::$priceWithTax];
	}
	

	// -------------------------------------------------------------------
	// Return price (with tax) for product code in pence
	// -------------------------------------------------------------------
	public function getPriceWithTaxForProductCodeInPence($code){
		return $this->priceInPence($this->CSVDataArray[$code][self::$priceWithTax]);
	}
	
	
	// -------------------------------------------------------------------
	// Return price with addition (in column 3) for product code
	// -------------------------------------------------------------------
	public function getPriceWithAdditionForProductCode($code){
		return $this->CSVDataArray[$code][self::$priceWithAddition];
	}
	

	// -------------------------------------------------------------------
	// Return price with addition (in column 3) for product code in pence
	// -------------------------------------------------------------------
	public function getPriceWithAdditionForProductCodeInPence($code){
		return $this->priceInPence($this->CSVDataArray[$code][self::$priceWithAddition]);
	}
	
	
	
	// -------------------------------------------------------------------
	// Return price with addition (in column 3) and tax for product code
	// -------------------------------------------------------------------
	public function getPriceWithAdditionAndTaxForProductCode($code){
		return $this->CSVDataArray[$code][self::$priceWithAdditionAndTax];
	}
	
	
	// -------------------------------------------------------------------
	// Return price with addition (in column 3) and tax for product code in pence
	// -------------------------------------------------------------------
	public function getPriceWithAdditionAndTaxForProductCodeInPence($code){
		return $this->priceInPence($this->CSVDataArray[$code][self::$priceWithAdditionAndTax]);
	}
	
	
	// -------------------------------------------------------------------
	// Return tax rate for the product code
	// -------------------------------------------------------------------
	public function getTaxRateForProductCode($code){
		return $this->CSVDataArray[$code][self::$tax];
	}
	
	
	// -------------------------------------------------------------------
	// All values are in pounds so multiply by 100 to get pence value
	// -------------------------------------------------------------------
	public function priceInPence($price){
		return round(($price * 100), 2);
	}
	
	
	// -------------------------------------------------------------------
	// Convert price to currency provided
	// -------------------------------------------------------------------
	public function convertToCurrency($price, $currency){
		
		// ---------------------------------------------------------------------------------------------------
		// ****************** WARNING - POSSIBLE SECURITY RISK in method getCurrencyExchangeRates() *********************
		// code needs allow_url_fopen=On in php.ini and downloads xml from external source
		// to use the converter please comment out the following two lines 
		// ---------------------------------------------------------------------------------------------------
		$this->error = " To see the currency conversion please comment out this line and the return in convertToCurrency()";
		return;
	
		// -------------------------------------------------------------------
		
		if(!$this->getCurrencyExchangeRates()){
			$this->error = "ERROR - currency codes could not be retrieved";
			return false;
		}
				
		if((!isSet($this->currencyRates[$currency]) || $this->currencyRates[$currency] == "") && $currency != "EUR"){
			$this->error = "ERROR - {$currency} code could not be found";
			return false;
		}
		
		return $this->convertToCurrencyCode($price, $currency);
	}
	
	

	// -------------------------------------------------------------------
	// Set the class column heading numbers - this is incase they change
	// with future files eg. 'Product Code' will be column 0
	// -------------------------------------------------------------------
	private function getColumnNumbers(){
		
		// get the first item from CSVLineArray (which is the list of column titles)
		$ColumnTitlesArray = $this->CSVLineArray[0];
		
		// trim white space from begining and end of each item in array
		$ColumnTitlesArray = array_map('trim',$ColumnTitlesArray);
				
		// Search the $ColumnTitlesArray for each of the column headings eg 'product_sku'
		// and assign the class properties the array number of each heading - this means
		// future changes to the file format will still process the correct columns 
		$this->columnProductCode = array_search($this->expectedColumnHeadings[0], $ColumnTitlesArray); // expecte to be 0
		$this->columnProductPriceWithTax = array_search($this->expectedColumnHeadings[1], $ColumnTitlesArray); // expecte to be 1
		$this->columnPriceAddition = array_search($this->expectedColumnHeadings[2], $ColumnTitlesArray); // expecte to be 2
		$this->taxAdjustment = array_search($this->expectedColumnHeadings[3], $ColumnTitlesArray); // expecte to be 3
		
		return true;
				
	}
	
	
	
	// -------------------------------------------------------------------
	// Verify that the class column heading numbers have been set
	// -------------------------------------------------------------------
	private function checkColumnNumbers(){
		
		$columnCode = $this->columnProductCode;
		$columnProductPriceWithTax = $this->columnProductPriceWithTax;
		$columnPriceAddition = $this->columnPriceAddition;
		$taxAdjustment = $this->taxAdjustment;
		
		$checkColumnHeadings = array($columnCode, 
									$columnProductPriceWithTax, 
									$columnPriceAddition, 
									$taxAdjustment);
		
		$i = 0; // used to provide error message
		
		// loop through each item and check that the expected column heading was found
		foreach ($checkColumnHeadings as &$title){
			
			if($title === false){
				
				$this->error = "ERROR - could not find column heading: (".$this->expectedColumnHeadings[$i].") in the CSV file". PHP_EOL;
				return false;
			}
			
			$i++;
		}
		
		return true;
	}
	

	
	// -------------------------------------------------------------------
	// Main driving function that loops through the array 
	// and calls the processLine method for each line of the CSV file
	// -------------------------------------------------------------------
	private function processCSVLines()
	{
		foreach ($this->CSVLineArray as &$value){
			
			$this->processLine($value);
		}

		return true;
	}
	
	
	
	
	// -------------------------------------------------------------------
	// Process the values in each line of the CSV file 
	// -------------------------------------------------------------------
	private function processLine($array)
	{
		
		// incrememnt the numberProcessed variable
		$this->numberProcessed++;
		
		// check data was parsed correctly
		if(!is_array($array)){
			
			$this->makeOutputArray('Error', "Data could not be converted into an array");
			return false;
		}
		
		// check data is valid
		if(!$this->isValid($array)){
			
			return false;
		}
		
		// process data
		if(!$this->processColumns($array)){
			
			return false;
		}	
		
		return true;	
	}
	
	
	
	// -------------------------------------------------------------------
	// Create column data
	// -------------------------------------------------------------------
	private function processColumns($array){
		
		$productCode = $array[$this->columnProductCode];
		$priceWithTax = $array[$this->columnProductPriceWithTax];
		$priceNoTax = $this->getPriceWithoutTax($priceWithTax); // remove the 20% in colun 2
		$productNoTaxPence = $this->priceInPence($this->getPriceWithoutTax($priceWithTax));		
		$priceWithTax = $array[$this->columnProductPriceWithTax];
		$priceWithTaxPence = $this->priceInPence($priceWithTax);
		$priceWithAddition = $this->returnPriceWithAddition($priceNoTax, $array[$this->columnPriceAddition]);
		$priceWithTaxAdjustment = $this->returnPriceWithTaxRateAdjustment($priceNoTax, $array[$this->taxAdjustment]);
		$priceWithAdditionAndTax = $this->returnPriceWithTaxRateAdjustment($priceWithAddition, $array[$this->taxAdjustment]);
		
		// create the arrays for output
		$this->createOutputArrays($productCode, self::$code, $productCode, 'Product Code');
		$this->createOutputArrays($productCode, self::$price, $priceNoTax, 'Price (Without '.$this->taxAmount.'% tax)');
		$this->createOutputArrays($productCode, self::$priceAddition, $array[$this->columnPriceAddition], 'Price Addition');
		$this->createOutputArrays($productCode, self::$priceWithAddition, $priceWithAddition, 'Price With Price Addition');
		$this->createOutputArrays($productCode, self::$tax, str_replace("%", "",$array[$this->taxAdjustment]), 'Tax Rate (%)');
		$this->createOutputArrays($productCode, self::$priceWithTax, $priceWithTaxAdjustment, 'Price With Tax Adjustment');	
		$this->createOutputArrays($productCode, self::$priceWithAdditionAndTax, $priceWithAdditionAndTax, 'Price With Addition And Tax');	
		
		return true;
		
	}
	
	
	
	// -------------------------------------------------------------------
	// Create arrays for HTML output and the class array
	// -------------------------------------------------------------------
	private function createOutputArrays($code, $id, $value, $tableHeader){
		
		$this->createDataArray($code, $id, $value);
				
		$this->createHTMLArray($tableHeader, $value);
		
	}

		
		
	// -------------------------------------------------------------------
	// Return price without tax amount defined in $this->taxAmount
	// -------------------------------------------------------------------
	private function getPriceWithoutTax($price){
		
		return round(($price / (100 + $this->taxAmount)) * 100, 2);

	}


	// -------------------------------------------------------------------
	// Return price with addition amount from price_addition_without_tax column
	// -------------------------------------------------------------------
	private function returnPriceWithAddition($price, $priceAddition){
		
		return $price + $priceAddition;
	}
	
	
	
	// -------------------------------------------------------------------
	// Return price with tax added on
	// -------------------------------------------------------------------
	private function returnPriceWithTaxRateAdjustment($price, $taxRate){
		
		$pricePrcAmount = ($price / 100) * $taxRate;
				
		return round($price + $pricePrcAmount, 2);
	}
	

	
	// -------------------------------------------------------------------
	// Check each of the values in the array are the correct data type
	// -------------------------------------------------------------------
	private function isValid($array){
		
		// check item 3 is int 
		if(isset($array[$this->taxAdjustment])){
			
			$percentageValue = str_replace("%", "", $array[$this->taxAdjustment]);
			// item is really a string so ctype_digit will check that it is just numbers 
			// in that string eg. is_int
			if(!is_numeric($percentageValue))

			{
				$this->createHTMLArray('Error', "Data in column 4 was not an number");
				return false;
			}
		}
		
		// check item 4 is numeric
		if( isset($array[$this->columnProductPriceWithTax])){
			
			// check item 4 is float				
			if(!is_numeric($array[$this->columnProductPriceWithTax]) ){
				// could make this error message a less technical term if necessary
				$this->createHTMLArray('Error', "Data in column 2 was not a number");
				return false;
			}
		}
		
		// check item 4 is numeric
		if( isset($array[$this->columnPriceAddition])){
			
			// check item 4 is float				
			if(!is_numeric($array[$this->columnPriceAddition])){
				
				// could make this error message a less technical term if necessary
				$this->createHTMLArray('Error', "Data in column 3 was not a number");
				return false;
			}
		}
		
		return true;		
	}
		
	
	
	// -------------------------------------------------------------------
	// Make output array which whill be used for an HTML file with
	// processing information
	// -------------------------------------------------------------------
	private function createHTMLArray($id, $value){
		
		$this->CSVHTMLArray[$this->numberProcessed][$id] = $value;
	}
	

	// -------------------------------------------------------------------
	// Make output array which whill be used for an HTML file with
	// processing information
	// -------------------------------------------------------------------
	private function createDataArray($code, $id, $value){
		
		$this->CSVDataArray[$code][$id] = $value;
		
	}
	
	
	
	// -------------------------------------------------------------------
	// convert the amount to the desired currency. The array is based on
	// euros so we need to convert the GBP to EUR first then multiply
	// that by the $price and output the value
	// -------------------------------------------------------------------
	private function convertToCurrencyCode($price, $currency){
					
		// exchange rates are supplied in EUR so first convert it to GBP
		$GBPrate = $this->currencyRates['GBP'];
		
		$GBPrate = (float)$GBPrate * 100;
		
		if(!is_numeric($GBPrate) && $currency != "EUR"){
			$this->error = "ERROR - there was an error converting the data";
			return false;
		}
		
		// get the amount of euros for £1 sterling
		if($currency == "EUR"){
			return round(((1 / $GBPrate) * $price) * 100, 2); 
		}
		
		// convert the sterling to euro
		$Euros = 1 / $GBPrate;
		
		// multiply the $price var with the euro value
		$Euros = $Euros * $price;
		
		// find the exchange rate for the currency required
		$currencyRate = $this->currencyRates[$currency];
		
		// output the calculation 
		return round(($Euros * $currencyRate) * 100, 2); 
		
	}
	
	
	
	// -------------------------------------------------------------------
	// ****************** POSSIBLE SECURITY RISK!!!! *********************
	// code found on http://www.ecb.europa.eu/stats/exchange/eurofxref/html/index.en.html
	// -------------------------------------------------------------------
	private function getCurrencyExchangeRates(){
		
		//function needs ini to allow_url_fopen
		if(!ini_get('allow_url_fopen')){
			return false;
		}
		
		// ****************** POSSIBLE SECURITY RISK!!!! *********************
	    // Get xml of currency exchange rates
	    $XML=simplexml_load_file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
	    //the file is updated daily between 2.15 p.m. and 3.00 p.m. CET
		
		// check it is XML
		if($XML === false){
			return false;
		}
		
		// create Sanitize object to clean inputs
		$clean = new Sanitize(); 
		 
	    foreach($XML->Cube->Cube->Cube as $rate){
			// create array of currency code => conversion value
			// sanitize each value to reduce harmful threats
		   $currencyValue = $clean->clean($rate["currency"]);
		   $rateValue = $clean->clean($rate["rate"]);
		   $this->currencyRates["{$currencyValue}"] = $rateValue;
		   
	    }
		
		if(!isSet($this->currencyRates) || empty($this->currencyRates)){
			return false;
		}
		
		return true;
		
	} 
	
}
?>