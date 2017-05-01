<?php declare(strict_types=1);

use Yahoo\{CSVWriter, CSVEarningsFormatter, EarningsTable, CustomStockFilterIterator, EmptyFilter, Configuration, SplFileObjectExtended};

require_once("utility.php");

  boot_strap();

  if ($argc == 2) {

    $argv[2] = "0"; // Set default argv[2] if not entered ($argv values are strings). 
    $argc = 3;
  }

  $rc = validate_user_input($argc, $argv);

  if ($rc !== true) {

       echo $rc . "\n";
       echo Configuration::config('help') . "\n"; 
       return;
  }

  $start_date = \DateTime::createFromFormat('m/d/Y', $argv[1]); 

  $date_period = build_date_period($start_date, intval($argv[2])); 

  $output_file_name = build_output_fname($start_date, intval($argv[2])); 

  $file_object = new SplFileObjectExtended($output_file_name, "w+");

  $total = 0; 

  // loop over each day in the date period.
  foreach ($date_period as $date_time) {
      
      if (EarningsTable::page_exists($date_time) == false) {
          
           echo "Page $url does not exist, therefore no .csv file for $friendly_date can be created.\n";               
           continue;    
      }
      
      try {

          $table = new EarningsTable($date_time, Configuration::config('column-names'), Configuration::config('output-order')); 
                
          $csv_writer = new CSVWriter(new CSVEarningsFormatter($table->getInputOrder(), Configuration::config('output-order')), $file_object); 
          
          $filterIter = createFilterIterator($table);
    
          foreach($filterIter as $key => $stock_row) {

               $csv_writer->writeLine($stock_row, $date_time); 
	  }

          $total += $csv_writer->getLineCount();    

          display_progress($date_time, $table->row_count(), $csv_writer->getLineCount(), $total); 
  
      } catch(\Exception $e) {
         
          displayException($e); 
          return 0;
      }
  }
   
  wrap_up($total, $output_file_name);

  return;

function display_progress(\DateTime $date_time, int $table_stock_cnt, int $lines_written, int $total)
{
  echo "\n" .$date_time->format("m-d-Y") . " earnings table contained $table_stock_cnt stocks. {$lines_written} stocks met the filter criteria. $total total records have been written.\n";
}

function wrap_up(int $total,  string $fname)
{
  if ($total != 0) {

      echo  $fname . " has been created. It contains " . $total . " met filter entries.\n";

  } else {

     echo  "No output was created because $total stocks met filter criteria.\n";
  }
}

function createFilterIterator(EarningsTable $table) : \FilterIterator
{
 return ($table->row_count() > 0) ? new CustomStockFilterIterator($table->getIterator(), $table->getRowDataIndex('sym')) : new EmptyFilter($table->getIterator()); 
}
