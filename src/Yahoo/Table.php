<?php declare(strict_types=1);	
namespace Yahoo;

interface Table  {

   public function row_count() : int;
   public function column_count() : int;
}
