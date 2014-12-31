<?php
use Yahoo\Registry;

require_once("loader/SplClassLoader.php");

function boot_strap()
{
 date_default_timezone_set("America/Chicago"); // Workaround for PHP5 

 $spl_loader = new SplClassLoader('Yahoo', 'src');

 $spl_loader->setFileExtension('.php');
 $spl_loader->register();
}

/*
 * Input: $argc, $argv, reference to $error_msg string to return
 * Returns: boolean: true if input good, false otherwise.
 */
function validate_user_input($arg_number, array $params, &$error_msg)
{

   if ( isset($arg_number) && $arg_number != 3 ) {
      
     $error_msg = "Two input paramters are required\n";
     return false;
   }
  
   // validate the date
   $mm_dd_yy_regex = "@^(\d{1,2})/(\d{1,2})/(\d{4})$@";
      
   $matches = array();
      
   $count = preg_match($mm_dd_yy_regex, $params[1], $matches);
          
   if ($count === FALSE || $count != 1) {
          
       $error_msg =  "The date " . $params[1] . " is not in a valid format.\n" ;
       return false;
   }
          
   $bRc = checkdate ($matches[1], $matches[2], $matches[3]);
      
   if ($bRc === FALSE) {
          
       $error_msg = $params[1] . " is not a valid date\n";
       return false;
   }
      
   // validate that second parameter is between 1 and 40 
   if ( (preg_match("/^[0-9][0-9]?$/", $params[2]) == 0) || ( ((int) $params[2]) > 40) ) {
        
        $error_msg = $params[2] . " is not a number between 0 and 40\n";
        return false;
    } 
    
    return true;
}
/*
 * Input: $argv[1] == date in DD/MM/YYYY format 
 */ 
function  build_date_period(\DateTime $start_date, $number_of_days)
{    
  // Determine the end date
  $end_date = clone($start_date); // \DateTime::createFromFormat('m/d/Y', $startDate);  
  
  $end_date->add(new \DateInterval("P" . ($number_of_days + 1 ) ."D")); 
  
  $one_day_interval = new \DateInterval('P1D');

  $date_period = new \DatePeriod($start_date, $one_day_interval, $end_date);
 
  return $date_period;
}

/*
 * returns bool
 */   
function url_exists($url)
{
    $file_headers = get_headers($url);
    return ($file_headers[0] == 'HTTP/1.1 404 Not Found') ? false : true;
}

// Prospective callback
function make_url(\DateTime $date_time)
{
    // Build yyyymmdd.html name
   return  Registry::registry('url-path')  . $date_time->format('Ymd') . ".html";
}

/*
 * Return bool
 */ 
function  validate_url_existence($url) 
{
   $file_headers = @get_headers($url);
   $response_code = substr($file_headers[0], 9, 3);

   $bool = true;
   
   if ( ( (int) $response_code)  >= 400) {
       
       $bool = false;
   }
   return $bool;
}
