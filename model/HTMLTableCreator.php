<?php
require_once('autoLoad.php');
# ========================================================================#
#
#  Author:    David Berriman
#  Version:	  1.0
#  Date:      14/06/2016
#  Purpose:   Create a log file with the outcome of the CSV import process
#  Plblic functions: 
#  			  logResults : Create a log file with the outcome of the CSV import process
#
#  Usage Example:
#                     require_once("fileImporterLog.php");
#                     $logFile = new FileImporterLog();
#                     $logFile->logResults($file);
#
# ========================================================================#

class HTMLTableCreator
{
	
	// ------------------------------------------------------
	// Class variables
	// ------------------------------------------------------	
	public  $error;
	private $tableHeadings = array();

    // -------------------------------------------------------------------
    // Main function called to create the log output
    // -------------------------------------------------------------------
    public function createTable($data)
    {		
	
        // check we have some data to work with
        if(!isset($data))
        {    
            $this->error = "ERROR - Please provide some data to be logged";
            return false;
        }

        // check data is in expected format
        if(!is_array($data))
        {    
            $this->error = "ERROR - the data supplied was not in the expected format. Expected array but received: ". gettype($data);
            return false;
        }
		
		date_default_timezone_set('Europe/London');
		
		// get the headings for the table which will also
		// be used for the column count
		$this->getTableHeadings($data);

        // create the output
        if(!$html = $this->createOutput($data))
        {
        	$this->error = "ERROR - could not generate report.";
			return false;
        }	
		return $html;
    }



    // -------------------------------------------------------------------
    // Run through the data and get all of the table headings
    // -------------------------------------------------------------------
	private function getTableHeadings($data){
	 		
		 // loop through each line (Y axis) of the CSV file
         foreach ($data as $line)
         {
			 // loop through each line (x axis) and add column
			 // headings to the class tableHeadings property
			 foreach ($line as $key => $value){
				 if(!in_array($key, $this->tableHeadings)){
				 	array_push($this->tableHeadings, $key);
				 }	
			 }
		 }
	 }


    // -------------------------------------------------------------------
    // function to drive the creation of the html output
    // -------------------------------------------------------------------
    private function createOutput($data)
    {
        $output = "";
		$output .= "<div class=\"container\">";
						
		$output .= $this->returnTableHTML($data);

		$output .= "</div>";
        return $output;
    }
	

	// -------------------------------------------------------------------
    // Return main table HTML
    // -------------------------------------------------------------------
    private function returnTableHTML($data)
    {
        $output = "";
        $output .= '<table>';
        $output .= '<thead>';
        $output .= '<tr>';
		
		// loop through list of headings
        foreach ($this->tableHeadings as $heading){
			$output .= '<th>'.$heading.'</th>';
		}

        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody> ';
		$output .= $this->returnRows($data);
        $output .= '</tbody>';
        $output .= '</table>';
        return $output;
    }


    // -------------------------------------------------------------------
    // Return table rows for report
    // -------------------------------------------------------------------
    private function returnRows($data)
    {
        $output = "";
				
		// create a Sanitize object to 'clean' anything potentially harmful in the file
		$clean = new Sanitize(); 
		
		// loop through 'outer' array which is each line in the CSV
        foreach ($data as &$value)
        {
            $output .= "<tr>";
			
			// loop through each item (td cell) for each line
			foreach ($this->tableHeadings as &$id)
        	{
				// add a class to colour the output cell red/green 
				// if this is the output item
				if(isset($value[$id]) && $value[$id] != "")
				{
					if($id == "outcome") // add class to outcome to color cell green / red
					{
						$output .= "<td class=\"".$value[$id]."\">".$value[$id]."</td>";
					}else
					{
						$output .= "<td>".$clean->clean($value[$id])."</td>";
					}
					
				}else
				{
					$output .= "<td></td>";
				}
			}
            $output .= "</tr>";
        }
		return $output;
    }


}
?>