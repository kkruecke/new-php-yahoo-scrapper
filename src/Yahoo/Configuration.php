<?php declare(strict_types=1);	
namespace Yahoo;

/* 
 TODO: Make this an ArrayObject--or whatever it is called?
 would that mean making the whole thing an associative array, like:
 $obj['help'];
 $obj['url'];
 $obj['columns']

class Configuration {
   
   private $url;
   private $help;
   private $columns = array();

   public function __construct($ini_fname)
   {
      libxml_use_internal_errors();

      $xml = simplexml_load_file($ini_fname); 

      $this->columns = array();

      foreach($xml->columns->column as $column) {

         $inner = array();

         $inner[ (string) $column{'abbrev'}] = (integer) $column{'output'};

         $this->columns[ (string) $column] = $inner;
      }

   }
  
};
