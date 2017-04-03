<?php declare(strict_types=1);
namespace Yahoo;

class YahooEarningsTableIterator implements  \SeekableIterator {

  protected   $html_table;  // YahooTable
  protected   $current_row; // int
  private     $end;         // int

  /*
   * Parameters: range of columns to return from each row.
   */
  public function __construct(YahooEarningsTable $htmltable) //, int $tbl_begin_column, int $tbl_end_column)
  {
     $this->html_table = $htmltable;
     
     $this->current_row = 0; 
               
     //$this->row_data = new \SplFixedArray($this->html_table->row_count());

     $this->end = $this->html_table->row_count(); 
  }

  /*
   * Iterator methods
   */  
  // returns void
  public function rewind() 
  {
     $this->current_row = 0;
  }
  
  // returns bool
  public function valid() : bool
  {
     return $this->current_row != $this->end;
  }

  // returns \SplFixedArray
  public function current() : \SplFixedArray  
  {
    return  $this->getRowData($this->current_row);	  
  }
  
  // returns int
  public function key() : int
  {
     return $this->current_row;
  }

  // returns void
  public function next() 
  {
     ++$this->current_row;
  }

  // returns void
  public function seek($pos) 
  {
	if ($pos < 0) {

             $this->current_row = 0;

	} else if ($pos < $this->end) {

	     $this->current_row = $pos;

	} else {

	     $this->current_row = $this->end % $pos;
	}

	return;
  }
  /*
   * returns SplFixedArray of cell text for $rowid
   */ 
  protected function getRowData($rowid) : \SplFixedArray
  {
     $column_count = $this->html_table->column_count();

     $row_data = new \SplFixedArray($column_count); 

     for($cellid = 0; $cellid < $column_count; $cellid++) {

        $row_data[$cellid] = $this->html_table->getCellText($rowid, $cellid);
     }	     
      
     // Change html entities back into ASCII (or Unicode) characters.  
     for($i = 0; $i < $this->html_table->column_count(); ++$i) {
         
         $row_data[$i] = html_entity_decode($row_data[$i]);
     }
     
     return $row_data;
  }
} 
