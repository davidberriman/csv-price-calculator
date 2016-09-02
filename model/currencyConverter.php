<?php
require_once('autoLoad.php');
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
#                     $currencyConverter = new CurrencyConverter();
#                     $conversion = $currencyConverter->convertPriceToCurrency($price, $currency);
#                     
#                     if(isSet($currencyConverter->error)){
#                     	this->error = $currencyConverter->error;
#                     	return false;
#                     }
#                     
#                     return $conversion;
# ========================================================================#

class CurrencyConverter 
{

	public $error;
	
	private $currencyRates = array();


	// -------------------------------------------------------------------
	// Convert price to currency provided
	// -------------------------------------------------------------------
	public function convertPriceToCurrency($price, $currency){
		
		if(!isSet($price)){
			$this->error = "ERROR - please provide a price to be converted";
			return false;
		}
		
		if(!isSet($currency)){
			$this->error = "ERROR - please provide a currency code for the conversion";
			return false;
		}
		
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
	// convert the amount to the desired currency. The array is based on
	// euros so we need to convert the GBP to EUR first then multiply
	// that by the $price and output the value
	// -------------------------------------------------------------------
	private function convertToCurrencyCode($price, $currency){
					
		// exchange rates are supplied in EUR so first convert it to GBP
		$GBPrate = $this->currencyRates['GBP'];
		
		$GBPrate = (float)$GBPrate * 100;
		
		if(!is_numeric($GBPrate)){
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
	// Get the currency exchange rates in XML format and convert to array
	// -------------------------------------------------------------------
	private function getCurrencyExchangeRates(){
		
		//function needs ini to allow_url_fopen
		if(!ini_get('allow_url_fopen')){
			$this->error = "ERROR - PHP.ini needs allow_url_fopen set to on";
			return false;
		}
		
		if(!$XML = $this->returnCurrencyXML()){
			return false;
		}
		
		if(!$this->createCurrencyArray($XML)){
			return false;
		}
		
		if(!isSet($this->currencyRates) || empty($this->currencyRates)){
			$this->error = "ERROR - there was an error converting the XML data";
			return false;
		}
		
		return true;
		
	} 
	
	
	
	// -------------------------------------------------------------------
	// ****************** POSSIBLE SECURITY RISK!!!! *********************
	// code found on http://www.ecb.europa.eu/stats/exchange/eurofxref/html/index.en.html
	// -------------------------------------------------------------------
	private function returnCurrencyXML(){
		
		// ****************** POSSIBLE SECURITY RISK!!!! *********************
	    // Get xml of currency exchange rates
	    $XML=simplexml_load_file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
	    //the file is updated daily between 2.15 p.m. and 3.00 p.m. CET
		
		// check it is XML
		if($XML === false){
			$this->error = "ERROR - there was an error parsing the XML from the provider";
			return false;
		}
		
		return $XML;
	}
	
	
	
	// -------------------------------------------------------------------
	// Convert XML to array of currencies
	// -------------------------------------------------------------------
	private function createCurrencyArray($XML){
		
		if(!isSet($XML)){
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
		
		return true;
	}
	
}
?>