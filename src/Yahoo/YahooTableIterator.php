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
  protected   $html_table;
  protected   int $current_row;
  //--protected   Vector<string> $row_data;
  protected   $row_data; // SplFixedArray
  private     int $end;
  private     int $start_column;
  private     int $end_column;

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
     $this->row_data = Vector {};

     $this->end = $this->html_table->rowCount(); 
  }

  /*
   * Iterator methods
   */  
  public function rewind() : void
  {
     $this->current_row = 0;
  }

  public function valid() : bool
  {
     return $this->current_row != $this->end;
  }

  //--public function current() : Vector<string>
  public function current() 
  {
    return  $this->getRowData($this->current_row);	  
  }

  public function key()  : int
  {
     return $this->current_row;
  }

  public function next() : void
  {
     ++$this->current_row;
  }

  public function seek($pos) : void
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
   * returns Vector<string> of cell text for $rowid
   */ 
  protected function getRowData(int $rowid) : Vector<string>
  {
     $row_data = Vector{};     

     for($cellid = $this->start_column; $cellid < $this->end_column; $cellid++) {

        $row_data[] = $this->html_table->getCellText($rowid, $cellid);
     }	     
      
     // Change html entities back into ASCII (or Unicode) characters.             
     $row_data = $row_data->map( $x ==> { return html_entity_decode($x); } );

     return $row_data;
  }
 
} 
