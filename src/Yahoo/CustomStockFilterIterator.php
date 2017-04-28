<?php declare(strict_types=1);
namespace Yahoo;
      
class CustomStockFilterIterator extends \FilterIterator {
    
   private $symbol_column;
   
   public function __construct(\Iterator $iter, int $symbol_column)
   {
	parent::__construct($iter);
        $this->symbol_column = $symbol_column;
   }	   

   public function accept() : bool
   {
        // Only accept strings with a length of 10 and greater
        $row = parent::current();

	/* 
	 * Filter criteria: Only accept rows with all non-empty columns
	 */  
	foreach($row as $columnn_num => $column_text) {
                  
		if (strlen($column_text) == 0) {
			return false;
		}
	}
        
        /*
	 * Further criteria: We only want US Stocks
	 */ 
        $symbol = $row[$this->symbol_column];
        
        // Added back on 03/02/2017
        $stock_length = strlen($symbol);

        /* 
          Must be:
          1. Between 1 and 5 characters in length.
          2. Must be only a-z and/or A-Z--no numbers, no dots.
         */ 
        $rc = preg_match("/^[a-zA-Z]{1,5}$/", $symbol) ? true : false;
        
        return $rc;
    }
}
