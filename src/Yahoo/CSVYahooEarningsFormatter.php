<?php declare(strict_types=1);
namespace Yahoo;

/*
 * Used be CSVWriter to return customize output
 * Should this be an Abstract class or an Interface since we don't need an implementation.
 * Interface seems the best choice.
 */ 
class CSVYahooEarningsFormatter implements CSVFormatter {

   public function format(\SplFixedArray $row, \DateTime $date) : string    
   {
     /* 
      * Column Header - Column No:
      * ==================
      * Stock - 0
      * Name  - 1
      * EPS Estimate - 2
      * Reported EPS - 3
      * Surpise (%) - 4
      * Earnings Call Time - 5
      */    

     $company_name = $row[1];
     
     $array[0] = str_replace(',', "", $company_name);  // company name
      
     $array[1] = $row[0]; // stock symbol

     $array[2] = $date->format('j-M');

     // Alter "Earnings Call Time" per specification.txt     
     $array[3] = $this->convert_call_time($row[5]); // "call time"/time    

     $array[4] = $row[2];  // <-- TODO: This seems missing.
     
     $array[] = "Add"; // Last column "Add"

     $csv_str = implode(",", $array);

     return $csv_str;
   }

   private function convert_call_time(string $call_time) : string
   {
      if (is_numeric($call_time[0])) { // a time was specified
    
            $call_time =  'D';
    
       } else if (FALSE !== strpos($call_time, "After")) { // "After market close"
    
             $call_time =  'A';
    
       } else if (FALSE !== strpos($call_time, "Before")) { // "Before market close"
    
            $call_time =  'B';
    
       } else if (FALSE !== strpos($call_time, "Time")) { // "Time not supplied"
    
          $call_time =  'U';
    
       } else { // none of above cases
    
            $call_time =  'U';
       }  
       return $call_time;
   } 
}
