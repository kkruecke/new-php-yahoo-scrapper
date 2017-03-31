<?php declare(strict_types=1);
namespace Yahoo;

class YahooTableIterator implements  \SeekableIterator {

  protected   $html_table;  // YahooTable
  protected   $current_row; // int
  private     $end;         // int
  private     $tbl_begin_column;// int
  private     $tbl_end_column;  // int

  /*
   * Parameters: range of columns to return from each row.
   */
  public function __construct(YahooEarningsTable $htmltable, int $tbl_begin_column, int $tbl_end_column)
  {
     $this->html_table = $htmltable;
     $this->tbl_begin_column = $tbl_begin_column; 
     $this->tbl_end_column = $tbl_end_column;

     $this->current_row = 0; 

     $this->end = 0;    // This was required to make HHVM happy.
     $size = $this->tbl_end_column - $this->tbl_begin_column;
     
     $this->row_data = new \SplFixedArray($size); 

     $this->end = $this->html_table->rowCount(); 
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
     $row_data = new \SplFixedArray($this->tbl_end_column - $this->tbl_begin_column);

     for($cellid = $this->tbl_begin_column; $cellid < $this->tbl_end_column; $cellid++) {

        $row_data[$cellid] = $this->html_table->getCellText($rowid, $cellid);
     }	     
      
     // Change html entities back into ASCII (or Unicode) characters.  
     for($i = 0; $i < $this->tbl_end_column; ++$i) {
         
         $row_data[$i] = html_entity_decode($row_data[$i]);
     }
     
     return $row_data;
  }
} 
