<?php declare(strict_types=1);
namespace Yahoo;

/*
 * Used be CSVWriter to return customize output
 * Should this be an Abstract class or an Interface since we don't need an implementation.
 * Interface seems the best choice.
 */ 
class CSVEarningsFormatter implements CSVFormatter {

   private $column_info;

   public function __construct(array $column_info)
   {
       $this->column_info = $column_info;
   }

   public function format(\SplFixedArray $row, \DateTime $date) : string    
   {
     /* 
      * Column order based on columns[] in yahoo.ini currently is:
      * ==================
      * Symbol - 0
      * Company  - 1
      * Earnings Call Time - 2
      * EPS Estimate - 3
      *     
      * Desired Output Order:     
      * =====================    
      *  Company column   
      *  Symbol column  
      *  Current Date: day and month   
      *  Time column translated as one-letter code
      *  EPS Estimate column
      *  Hardcoded value of "Add"
      */    
     print_r($row);
     print_r($this->column_info);
      
     foreach($this->column_info as $abbrev => $input_column) {

        $temp =  $row[$input_column];  

        // Bug?: we need to place them in $array[xxx], where xxxx is output="xxx"; for example,
        // <column abbrev="sym" output="1">Symbol</column>
        // Are we missing abbrev-to-input??? Rethink what is actually needed.
        switch ($abbrev) {

           case 'co':
                $array[0] = str_replace(',', "", $temp);  
                break;
      
           case 'sym':
               $array[1] = $temp;
               break;

           case 'eps':
               $array[4] = $this->format_eps($temp);  // EPS Estimate 
               break; 

           case 'time': 
              // Alter "Earnings Call Time" per specification.txt     
              $array[3] = $this->convert_call_time($temp); // "Earnings Call Time"
              break;
       }
     }
     
     $array[2] = $date->format('j-M'); // current DD/MM -- day and month.

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

   private function format_eps(string $eps) : string
   {
      return (is_numeric($eps) == FALSE) ? "N/A" : $eps;
   }
}
