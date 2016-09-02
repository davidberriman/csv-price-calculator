<?php
# ========================================================================#
#
#  Author:    David Berriman
#  Version:	  1.0
#  Date:      14/06/2016
#  Purpose:   Check CSV file is valid
#  Plblic functions: 
#  			   importCSV : import the CSV file specified 
#
#  Usage Example:
#                     require_once("csvFile.php");
#                     $importer = new CSVFile("fileToBeProcessed.csv");
#					  $importer->parseCSV();  // check this equals true
#
# ========================================================================#

class CSVFile
{

	protected $CSVLineArray = array();   // each line of the CSV file is an item in the array
	protected $importFile;

	public  $error; // feedback to users about errors

	
	// -------------------------------------------------------------------
	// copy file to class property
	// -------------------------------------------------------------------
	public function __construct($file)
	{
		$this->importFile = $file;
	}
	

	
	// -------------------------------------------------------------------
	// Convert the CSV file to an array
	// -------------------------------------------------------------------
	public function parseCSV()
	{
			
		// check we have a value to work with
		if(!isset($this->importFile) )
		{
			$this-> error = "ERROR - file data was not found". PHP_EOL;
			return false;	
		}
		
		$row = 1;
		
		// loop through the CSV file and create an array of arrays
		// the top level array is a list if CSV lines - each line is an array
		// the inner arrays are the individual lines
		if (($handle = fopen($this->importFile, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

				$num = count($data);
				$lineArray = array();
				
				// create an array for this line in the CSV file 
				// where each column is an array item
		        for ($c=0; $c < $num; $c++) {
					array_push($lineArray, $data[$c]);
		        }
				
				// push the line array into the array of lines
				array_push($this->CSVLineArray, $lineArray);
				
		    }
		    fclose($handle);
		}
		
		return true;
	}
	
		

}
?>