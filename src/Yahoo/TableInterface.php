<?php declare(strict_types=1);	
namespace Yahoo;

interface TableInterface  {

   public function row_count() : int;
   public function column_count() : int;
   public function getRowData($row_index) : \SplFixedArray;
}
