<?php
use Yahoo\CSVWriter,
    Yahoo\CSVYahooFormatter,
    Yahoo\YahooTable, 
    Yahoo\CustomStockFilterIterator,
    Yahoo\Registry;
  
require_once("utility.php");

  boot_strap();

  if ($argc == 2) {

    $argv[2] = 0; 
    $argc = 3;
  }

  $error_msg = '';

  if (validate_user_input($argc, $argv, $error_msg) == false) {

       echo $error_msg . "\n";
       echo Registry::registry('help'); 
       return;
  }

  $start_date = \DateTime::createFromFormat('m/d/Y', $argv[1]); 

  $date_period = build_date_period($start_date, (int) $argv[2]);

  /*
   * CSVYahooFormatter determines the format of the output, the rows of the CSV file.
   */  
  $file_name = $start_date->format('jmY') . "-plus-" . $argv[2] . ".csv";
    
  $csv_writer = new CSVWriter($file_name, new CSVYahooFormatter()); // BUG: date needs to vary.

  // Start main loop
  foreach ($date_period as $date_time) {
      
      $url = make_url($date_time); // Build yyyymmdd.html name

      $friendly_date = $date_time->format("m-d-Y"); // User-friendly date format
      
      if (!validate_url_existence($url)) {
          
           echo 'Skipping data for date ' . $friendly_date . " because webpage $url does not exist...\n";               
           continue;    
      }
      
      try {

	  $start_column = (int) Registry::registry('start-column');     

	  $end_column = (int) Registry::registry('end-column');    // End column is one past the last column retrieved. 

	  $table = new YahooTable($url, Registry::registry('xpath-query'), $start_column, $end_column);

	  $total_rows = $table->rowCount(); // first row is 0, last is $total_rows - 1
	     
	  // We skip the first two rows, the table description and column headers, and the last row which has no financial data
	  $start_row = 2;

	  $row_count = $total_rows - $start_row - 1;

	  $limitIter = new \LimitIterator($table->getIterator(), 2, $row_count); 

	  /*
	   * The filter iterator should include all the filters of the original code:
	   *   1. no column may be blank
	   *   2. only US Stocks are selected
	   *
	   * Alternately, a custom callback filter iterator could be used like so: 
	   * $callbackFilterIter = new \CallbackFilterIterator($rowExtractorIter, 'isUSStock_callback');

	   */   
	  $filterIter = new CustomStockFilterIterator($limitIter);
     
          foreach($filterIter as $key => $stock) {

               $csv_writer->writeLine($stock, $date_time); 
	  }

	  echo "Date $friendly_date processed\n";

  
      } catch(\Exception $e) {
          
          echo $e->getMessage() . "\n\n";
	  echo $e->getTraceAsString() . "\n\n";
          return;
      }
  }

  $line_count = $csv_writer->getLineCount();
  
  echo  $csv_writer->getFileName() . " has been created. It contains $line_count US stocks entries.\n";
    
  return;
