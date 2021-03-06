<?php declare(strict_types=1);

require_once("loader/SplClassLoader.php");

function boot_strap()
{
 date_default_timezone_set("America/Chicago"); // Workaround for PHP5 

 $spl_loader = new SplClassLoader('Yahoo', 'src');

 $spl_loader->setFileExtension('.php');
 $spl_loader->register();
}

function displayException(\Exception $e)
{
  $msg = "\nError occurred processing page. Exception information below:\n";
          
  $msg .= $e->getMessage() . "\n\n";
  echo $msg;
  echo $e->getTraceAsString() . "\n\n";
}


/*
 * Input: $argc, $argv, reference to $error_msg string to return
 * Returns: boolean: true if input good, false otherwise.
 */
function validate_user_input(int $arg_number, array $params)
{
   $error_msg = '';

   if ( isset($arg_number) && $arg_number != 3 ) {
      
     $error_msg = "Two input paramters are required.";
     return $error_msg;
   }
  
   // validate the date
   $mm_dd_yy_regex = "@^(\d{1,2})/(\d{1,2})/(\d{4})$@";
      
   $matches = array();
      
   $count = preg_match($mm_dd_yy_regex, $params[1], $matches);
          
   if ($count === FALSE || $count != 1) {
          
       $error_msg =  "The date " . $params[1] . " is not in a valid format.\n" ;
       return $error_msg;
   }
   // Convert strings to ints.        
   $bRc = checkdate (intval($matches[1]), intval($matches[2]), intval($matches[3]));
      
   if ($bRc === FALSE) {
          
       $error_msg = $params[1] . " is not a valid date\n";
       return $error_msg;
   }
      
   // validate that second parameter is between 1 and 40 
   if ( (preg_match("/^[0-9][0-9]?$/", $params[2]) == 0) || ( ((int) $params[2]) > 40) ) {
        
        $error_msg = $params[2] . " is not a number between 0 and 40\n";
        return $error_msg;
    } 
    
    return true;
}
/*
 * Input: $argv[1] == date in DD/MM/YYYY format 
 */ 
function  build_date_period(\DateTime $start_date, int $number_of_days) : \DatePeriod
{    
  // Determine the end date
  $end_date = clone($start_date); // \DateTime::createFromFormat('m/d/Y', $startDate);  
  
  $end_date->add(new \DateInterval("P" . ($number_of_days + 1 ) ."D")); 
  
  $one_day_interval = new \DateInterval('P1D');

  $date_period = new \DatePeriod($start_date, $one_day_interval, $end_date);
 
  return $date_period;
}

function build_output_fname(\DateTime $start_date, int $days)
{
  return $start_date->format('jmY') . "-plus-" . $days . ".csv";
}
/*
 * Return bool
 */ 
function validate_url_existence(string $url) : bool 
{
  
   $file_headers = @get_headers($url);
   
   if ($file_headers === FALSE) {
       
       return false;
   }
   
   $response_code = substr($file_headers[0], 9, 3);

   $bool = ( ( (int) $response_code )  >= 400) ? false : true;
  
   return $bool;
}
