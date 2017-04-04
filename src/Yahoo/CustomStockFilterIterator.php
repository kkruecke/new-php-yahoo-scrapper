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
        $symbol = $row[$this->symbol_column];
        
        // Added back on 03/02/2017
        $stock_length = strlen($symbol);

        return (($stock_length > 1 && $stock_length =< 5) && ( strpos($symbol, '.') === FALSE)) ? true : false;
    }
}
