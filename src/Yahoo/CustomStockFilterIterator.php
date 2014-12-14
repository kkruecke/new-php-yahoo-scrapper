<?php
namespace Yahoo;
      
class CustomStockFilterIterator extends \FilterIterator {

   public function __construct(\Iterator $iter)
   {
	parent::__construct($iter);
   }	   

   public function accept() 
   {
        // Only accept strings with a length of 10 and greater
        $row = parent::current();

	/* 
	 * Filter criteria: Only accept rows with all non-empty columns
	 */  
	foreach($row as $columnn_num => $column_text) {
                /*
		 * All column must be non-empty. Alternate regex:
                $rc = preg_match ('/^\s*$/', $cell_text); 
		 */  
		if (strlen($column_text) == 0) {
			return false;
		}
	}
        /*
	 * Further criteria: We only want US Stocks
	 */ 
        $stock_length = strlen($row[1]);

        return (($stock_length > 1 && $stock_length < 5) && ( strpos($row[1], '.') === FALSE)) ? true : false;

    }
}
