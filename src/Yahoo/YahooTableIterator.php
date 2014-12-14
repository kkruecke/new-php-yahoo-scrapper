<?php
namespace Yahoo;
/*
 *
Take from the Hack  ArrayIterator.hhi 

class ArrayIterator<T> implements KeyedIterator<int, T>,
                                  KeyedTraversable<int, T>,
                                  ArrayAccess<int, T>,
                                  SeekableIterator,
                                  Countable,
                                  Serializable {
  public function __construct (mixed $array);
  public function append(mixed $value): void;
  public function asort(): void;
  public function count(): int;
  public function current(): mixed;
  public function getArrayCopy(): array;
  public function getFlags(): void;
  public function key(): mixed;
  public function ksort(): void;
  public function natcasesort(): void;
  public function natsort(): void;
  public function next(): void;
  public function offsetExists(string $index): void;
  public function offsetGet(string $index): mixed;
  public function offsetUnset(string $index): void;
  public function rewind(): void;
  public function seek(int $position): void;
  public function serialize(): string;
  public function setFlags(string $flags): void;
  public function uasort(string $cmp_function): void;
  public function uksort(string $cmp_function): void;
  public function unserialize(string $serialized): string;
  public function valid(): bool;
} 
 */ 
//class YahooTableIterator implements \KeyedIterator<int, \Vector<string> >, SeekableIterator {
class YahooTableIterator implements  \SeekableIterator {

  //--protected   YahooTable $html_table;
  protected   $html_table;  // YahooTable
  protected   $current_row; // int
  private     $end;         // int
  private     $start_column;// int
  private     $end_column;  // int

  /*
   * Parameters: range of columns to return from each row.
   */
  public function __construct(YahooTable $htmltable, int $start_column, int $end_column)
  {
     $this->html_table = $htmltable;
     $this->start_column = $start_column; 
     $this->end_column = $end_column;

     $this->current_row = 0; 

     $this->end = 0;    // This is required to make HHVM happy.
     $size = $this->end_column - $this->start_column;
     
     $this->row_data = new \SplFixedArray($size); 

     $this->end = $this->html_table->rowCount(); 
  }

  /*
   * Iterator methods
   */  
  // returns void
  public function rewind() // void
  {
     $this->current_row = 0;
  }
  
  // returns bool
  public function valid() 
  {
     return $this->current_row != $this->end;
  }

  // returns \SplFixedArray
  public function current() 
  {
    return  $this->getRowData($this->current_row);	  
  }
  
  // returns int
  public function key()
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
   * returns splFixedArray of cell text for $rowid
   */ 
  protected function getRowData(int $rowid)
  {
     $row_data = new \SplFixedArray($this->end_column - $this->start_column);

     for($cellid = $this->start_column; $cellid < $this->end_column; $cellid++) {

        $row_data[] = $this->html_table->getCellText($rowid, $cellid);
     }	     
      
     // Change html entities back into ASCII (or Unicode) characters.  
     for($i = 0; $i < $this->end_column; ++$i) {
         
         $row_data[$i] = html_entity_decode($value);
     }
     
     return $row_data;
  }
 
} 
