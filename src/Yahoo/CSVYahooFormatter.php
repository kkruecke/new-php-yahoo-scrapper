<?hh
namespace Yahoo;

/*
 * Used be CSVWriter to return customize output
 * Should this be an Abstract class or an Interface since we don't need an implementation.
 * Interface seems the best choice.
 */ 
class CSVYahooFormatter implements CSVFormatter {

   private \DateTime $start_date;

   public function __construct(\DateTime $start_date) 
   {
	   $this->start_date = $start_date;
   }

   public function format(Vector<string> $row) : string
   {
      
     if ($row->count() < 4) {

	  throw new \RangeException("Size of Vector<string> is less than four\n");
     }	   

     $column4_text = $row[3];	   
   
     if (is_numeric($column4_text[0])) { // a time was specified
   
           $column4_text =  'D';
   
      } else if (FALSE !== strpos($column4_text, "After")) { // "After market close"
   
            $column4_text =  'A';
   
      } else if (FALSE !== strpos($column4_text, "Before")) { // "Before market close"
   
           $column4_text =  'B';
   
      } else if (FALSE !== strpos($column4_text, "Time")) { // "Time not supplied"
   
         $column4_text =  'U';
   
      } else { // none of above cases
   
           $column4_text =  'U';
      }  
      
     $row[3] = $column4_text; 
      /*
       * This is taken from the prior php code TableRowExtractorIterator::addDataSuffix() method, which was invoked after
       * TableRowExtractorIterator::getRowData()
       */
     $date = $this->start_date->format('j-M');
    
     $array = $row->toArray();

     array_splice($array, 2, 0, $date); // Insert date after first third columns.

     $array[] = "Add"; // Also taken from TableRowExtractorIterator::addDataSuffix() in prior PHP code.

     $csv_str = implode(",", $array);

     return $csv_str;
   }

}
