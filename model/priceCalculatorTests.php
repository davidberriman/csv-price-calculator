<?php
class PriceCalculatorTests
{
	
	public  $numberProcessed;
	private $CSVHTMLArray = array();
	private $productCode;
	
	public  $error;
	
	
	function __construct(){
		$this->numberProcessed;
		$this->CSVHTMLArray[0] = "";
	}
	
	
	// -------------------------------------------------------------------
	// Run tests on file test.csv
	// -------------------------------------------------------------------
	public function runTests(){
		
		$this->productCode = "test1";
		
		// start at -1 so each of the functions can increment
		$this->numberProcessed = -1;
		
		$priceCalculator = new PriceCalculator();

		if(!$priceCalculator->calculateFile("tests.csv"))
		{	
			$this->error = "TESTS Failed to initiate";
			return false;
		}
		
		$this->test_getPriceNoTaxForProductCode($priceCalculator);
				
		$this->test_getPriceNoTaxForProductCodeInPence($priceCalculator);
				
		$this->test_getPriceWithTaxForProductCode($priceCalculator);
		
		$this->test_getPriceWithTaxForProductCodeInPence($priceCalculator);
		
		$this->test_getPriceWithAdditionForProductCode($priceCalculator);
		
		$this->test_getPriceWithAdditionForProductCodeInPence($priceCalculator);
		
		$this->test_getPriceWithAdditionAndTaxForProductCode($priceCalculator);
		
		$this->test_getPriceWithAdditionAndTaxForProductCodeInPence($priceCalculator);
		
		$this->test_getTaxRateForProductCode($priceCalculator);
		
		return true;
	}
	
	
	// -------------------------------------------------------------------
	// Return array with results from processing 
	// -------------------------------------------------------------------
	public function getResultsForHTMLTable(){
		return $this->CSVHTMLArray;	
	}
	
	
	// -------------------------------------------------------------------
	// Table headings & content for successes
	// -------------------------------------------------------------------
	private function createSuccessMessage($message){
		
		$this->createHTMLArray("MESSAGE", $message." passed unit test");
		$this->createHTMLArray("RESULT", "SUCCESS");
	}
	
	
	
	// -------------------------------------------------------------------
	// Table headings & content for failures
	// -------------------------------------------------------------------
	private function createFailureMessage($message){
		
		$this->createHTMLArray("MESSAGE", $message." failed unit test");
		$this->createHTMLArray("RESULT", "FAILED");
	}


	// -------------------------------------------------------------------
	// Make output array which whill be used for an HTML file with
	// processing information
	// -------------------------------------------------------------------
	private function createHTMLArray($id, $value){
		
		$this->CSVHTMLArray[$this->numberProcessed][$id] = $value;
	}
	
	
	
	// -------------------------------------------------------------------
	// Unit tests are below 
	// -------------------------------------------------------------------
	private function test_getPriceNoTaxForProductCode($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceNoTaxForProductCode($this->productCode) != 100){			
			$this->createFailureMessage("getPriceNoTaxForProductCode()");
			return false;
		}

		$this->createSuccessMessage("getPriceNoTaxForProductCode()");
		return true;
	}


	private function test_getPriceNoTaxForProductCodeInPence($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceNoTaxForProductCodeInPence($this->productCode) != 10000){
			$this->createFailureMessage("getPriceNoTaxForProductCodeInPence()");
			return false;
		}
				
		$this->createSuccessMessage("getPriceNoTaxForProductCodeInPence()");
		return true;
	}


	private function test_getPriceWithTaxForProductCode($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceWithTaxForProductCode($this->productCode) != 110){
			$this->createFailureMessage("getPriceWithTaxForProductCode()");
			return false;
		}
		
		$this->createSuccessMessage("getPriceWithTaxForProductCode()");
		return true;
	}

	
	private function test_getPriceWithTaxForProductCodeInPence($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceWithTaxForProductCodeInPence($this->productCode) != 11000){
			$this->createFailureMessage("getPriceWithTaxForProductCodeInPence()");
			return false;
		}
		
		$this->createSuccessMessage("getPriceWithTaxForProductCodeInPence()");
		return true;
	}
	
	
	private function test_getPriceWithAdditionForProductCode($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceWithAdditionForProductCode($this->productCode) != 150){
			$this->createFailureMessage("getPriceWithAdditionForProductCode()");
			return false;
		}
		
		$this->createSuccessMessage("getPriceWithAdditionForProductCode()");
		return true;
	}
	

	private function test_getPriceWithAdditionForProductCodeInPence($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceWithAdditionForProductCodeInPence($this->productCode) != 15000){
			$this->createFailureMessage("getPriceWithAdditionForProductCodeInPence()");
			return false;
		}
		
		$this->createSuccessMessage("getPriceWithAdditionForProductCodeInPence()");
		return true;
	}
	
	
	
	private function test_getPriceWithAdditionAndTaxForProductCode($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceWithAdditionAndTaxForProductCode($this->productCode) != 165){
			$this->createFailureMessage("getPriceWithAdditionAndTaxForProductCode()");
			return false;
		}
		
		$this->createSuccessMessage("getPriceWithAdditionAndTaxForProductCode()");
		return true;
	}

	private function test_getPriceWithAdditionAndTaxForProductCodeInPence($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getPriceWithAdditionAndTaxForProductCodeInPence($this->productCode) != 16500){
			$this->createFailureMessage("getPriceWithAdditionAndTaxForProductCodeInPence()");
			return false;
		}
		
		$this->createSuccessMessage("getPriceWithAdditionAndTaxForProductCodeInPence()");
		return true;
	}

	
	private function test_getTaxRateForProductCode($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->getTaxRateForProductCode($this->productCode) != 10){
			$this->createFailureMessage("getTaxRateForProductCode()");
			return false;
		}
		
		$this->createSuccessMessage("getTaxRateForProductCode()");
		return true;
	}
	
	
	
	private function test_priceInPence($priceCalculator){
		
		$this->numberProcessed++;
		
		if($priceCalculator->priceInPence(150.00) != 15000){
			$this->createFailureMessage("priceInPence()");
			return false;
		}
		
		$this->createSuccessMessage("priceInPence()");
		return true;
	}
	
}
?>