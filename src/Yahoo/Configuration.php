<?php declare(strict_types=1);	
namespace Yahoo;

class Configuration {

   private $config = array();

   public function __construct($ini_fname)
   {
      libxml_use_internal_errors();

      $xml = simplexml_load_file($ini_fname); 

      $columns = array();

      foreach($xml->columns->column as $column) {

         $inner = array();

         $inner[ (string) $column{'abbrev'}] = (integer) $column{'output'};

         $columns[ (string) $column] = $inner;
      }

      $this->config['columns'] = $columns;   
      $this->config['help'] = (string) $xml->help;
      $this->config['url'] = (string) $xml->url;
   }

   public function setInputColumn(string $name) 
   {
       // Alter $this->columns[$name], adding input column somehow. 
   }
  
};
