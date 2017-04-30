<?php declare(strict_types=1);

use Yahoo\{CSVWriter, CSVEarningsFormatter, EarningsTable, CustomStockFilterIterator, EmptyFilter, Configuration};

require_once("utility.php");

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

  $output_file_name = build_output_fname($start_date, intval($argv[2]));

  $total = 0; 

  // loop over each day in the date period.
  foreach ($date_period as $date_time) {
      
      if (EarningsTable::page_exists($date_time) == false) {
          
           echo "Page $url does not exist, therefore no .csv file for $friendly_date can be created.\n";               
           continue;    
      }
      
      try {

          $table = new EarningsTable($date_time, Configuration::config('column-names'), Configuration::config('output-order')); 

          echo "Date: " . $date_time->format("m-d-Y") . "\n"; 

          $table->debug_show_table();
            
      } catch(\Exception $e) {
         
          displayException($e); 
          return 0;
      }
  }
   
  wrap_up($total, $csv_writer);

  return;

function display_progress(\DateTime $date_time, int $table_stock_cnt, int $lines_written, int $total)
{
  echo "\n" . $date_time->format("m-d-Y") . " earnings table contained $table_stock_cnt stocks. {$lines_written} stocks met the filter criteria. $total total records have been written.\n";
}

function wrap_up(int $total,  CSVWriter $csv_writer)
{
  if ($total != 0) {

      echo  $csv_writer->getFileName() . " has been created. It contains " . $total . " met filter entries.\n";

  } else {

     echo  "No output was created because $total stocks met filter criteria.\n";
     $csv_writer->unlink(); 
  }
}

function createFilterIterator(EarningsTable $table) : \FilterIterator
{
 return ($table->row_count() > 0) ? new CustomStockFilterIterator($table->getIterator(), $table->getRowDataIndex('sym')) : new EmptyFilter($table->getIterator()); 
}
