<?php
use Yahoo\CSVWriter;
use Yahoo\TableRowExtractorIterator;

include "utility.php";
include "loader/SplClassLoader.php";

$spl_loader = new SplClassLoader('Yahoo', 'src');

$spl_loader->register();

define('YAHOO_BIZ_URL', "http://biz.yahoo.com/research/earncal/");

define('HELP', "How to use: Enter a date in mm/dd/YYYYY format follow by number between 0 and 40.\n");

  $error_msg = "";
  
  if ($argc == 2) {
    $argv[2] = 0; 
    $argc = 3;
  }

  if (validate_user_input($argc, $argv, $error_msg) == false) {

       echo $error_msg;
       echo HELP . "\n"; 
       return;
  }

  $date_period = build_date_period($argv[1], (int) $argv[2]);

  $start_date = \DateTime::createFromFormat('m/d/Y', $argv[1]); 

  $csv_writer = new CSVWriter($start_date, $argv[2]);

  // Start main loop
  foreach ($date_period as $date) {
      
      // Build yyyymmdd.html name
      $url = YAHOO_BIZ_URL . $date->format('Ymd') . ".html";
      
      if (url_exists($url) === false) {
          
           echo 'The url for ' . $date->format("m-d-Y") . ", $url , " . " does not exist...skipping\n";               
           continue;    
      }
      
      try {
     
         $extractor = new TableRowExtractorIterator($url, $date, '/html/body/table[3]/tr/td[1]/table[1]');
  
          foreach($extractor as $stock_data) {
         
               $csv_writer->writeLine($stock_data); 
          }
  
      } catch(Exception $e) {
          
          echo $e->getMessage() . "\n";
          return;
      }
  }

  $us_stock_count = $csv_writer->getLineCount();
  
  echo "A total of " . $us_stock_count . " US stocks were extracted.\n";
  echo  $csv_writer->getFileName() . " has been created. It contains $us_stock_count US stocks entries.\n";
    
  return;
