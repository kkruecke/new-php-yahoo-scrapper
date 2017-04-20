<?php declare(strict_types=1);	
namespace Yahoo;

interface YahooTableInterface  {

   public function row_count() : int;
   public function column_count() : int;
   public function getRowData(int $row_index) : \SplFixedArray;
}
