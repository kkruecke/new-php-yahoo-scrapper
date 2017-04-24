<?php declare(strict_types=1);

use Yahoo\{CSVWriter, CSVEarningsFormatter, EarningsTable, CustomStockFilterIterator, Configuration};

require_once("utility.php");

function displayException(\Exception $e)
{
  $msg = "\nError occurred processing page. Exception information below:\n";
          
  $msg .= $e->getMessage() . "\n\n";
  echo $msg;
  echo $e->getTraceAsString() . "\n\n";
}

  boot_strap();

  if ($argc == 2) {

    $argv[2] = "0"; // $argv values are strings 
    $argc = 3;
  }

  $error_msg = '';


  if (validate_user_input($argc, $argv, $error_msg) == false) {

       echo $error_msg . "\n";
       echo Configuration::config('help') . "\n"; 
       return;
  }

  $start_date = \DateTime::createFromFormat('m/d/Y', $argv[1]); 

  $date_period = build_date_period($start_date, intval($argv[2])); 

  /*
   * CSVEarningsFormatter determines the format of the output, the rows of the CSV file.
   */  
  $output_file_name = $start_date->format('jmY') . "-plus-" . intval($argv[2]) . ".csv";
  
  // loop over each day in the DatePeriod.
  foreach ($date_period as $date_time) {
      
      if (EarningsTable::page_exists($date_time) == false) {
          
           echo "Page $url does not exist, therefore no .csv file for $friendly_date can be created.\n";               
           continue;    
      }
      
      try {

          $output_ordering =  Configuration::config('output-ordering');

          $table = new EarningsTable($date_time, Configuration::config('column-names'), $output_ordering); 

          $abbrev_2_indecies = $table->getInputOrdering(); 

          //--echo "Dumping Abbreviation mapping to row indecies:\n";
          //--var_dump($abbrev_2_indecies);
          echo "\n";
        
          $csv_writer = new CSVWriter($output_file_name, new CSVEarningsFormatter($abbrev_2_indecies, $output_ordering)); 
          
          // CustomStockFilterIterator needs the index (in the row array returned from the table iterator) that contains the stock symbol.
	  $filterIter = new CustomStockFilterIterator($table->getIterator(), $abbrev_2_indecies['sym']); 
     
          foreach($filterIter as $key => $stock_row) {

               $csv_writer->writeLine($stock_row, $date_time); 
	  }
          
	  echo $date_time->format("m-d-Y") . " processed\n";

  
      } catch(\Exception $e) {
         
          displayException($e); 
          return 0;
      }
  }

  $line_count = $csv_writer->getLineCount();
  
  echo  $csv_writer->getFileName() . " has been created. It contains $line_count US stocks entries.\n";
    
  return;

