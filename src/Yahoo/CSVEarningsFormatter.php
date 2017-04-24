<?php declare(strict_types=1);
namespace Yahoo;

/*
 * Used be CSVWriter to return customize output
 * Should this be an Abstract class or an Interface since we don't need an implementation.
 * Interface seems the best choice.
 */ 
class CSVEarningsFormatter implements CSVFormatter {

   private $output_ordering;
   private $input_ordering;

   public function __construct(array $input_ordering, array $output_ordering)
   {
       $this->output_ordering = $output_ordering;
       $this->input_ordering = $input_ordering;
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
    
     /*
     print_r($row);
     print_r($this->output_ordering);
     die("ending"); 
      */
     $size = count($this->output_ordering) + 2;  
     $output = new \SplFixedArray($size);
     
     // Reorder input into appropriate output positions in $output
     foreach($this->output_ordering as $abbrev => $output_index) {
             
             $output[ $output_index ] = $this->modify_input($row[ $this->input_ordering[$abbrev] ], $abbrev);
     }
     
     $output[2] = $date->format('j-M'); // current DD/MM -- day and month.

     $output[$size - 1] = "Add"; // Last column "Add"

     $csv_str = '';
     // implode the fixed array
     $i = 0;
     while($i < $size) {
         
         $csv_str .= $output[$i++];
         if ($i == $size) break;
         $csv_str .= ',';    
     }
     
     return $csv_str;
   }

   private function modify_input($input, $abbrev)
   {
      switch ($abbrev) {

          case 'co':
               return str_replace(',', "", $input);  
               break;
      
          case 'eps':
              return $this->format_eps($input);  // EPS Estimate 
              break; 

          case 'time': 
             return $this->convert_call_time($input); // "Earnings Call Time"
             break;

          default:
              return $input;
              break;
      }
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
