<?php declare(strict_types=1);	
namespace Yahoo;

class Configuration {

   private static $config_;

   public function __construct()
   {
      self::init();
   }

   private static function init()
   {
     if (isset(self::$config_)) return;	  

     libxml_use_internal_errors();

     $xml = simplexml_load_file("config.xml"); 

     $names = array();
     $abbrevs = array();

     foreach($xml->columns->column as $column) {

        $names[] = (string) $column;

        $abbrevs[(string) $column{'abbrev'}] = (integer) $column{'output'};
     }

     self::$config_['help'] = (string) $xml->help;
     self::$config_['url'] = (string) $xml->url;
     self::$config_['column-names'] = $names;
     self::$config_['column-info'] = $abbrevs;
   }

   public static function setConfig(string $key, array $value) 
   {
       self::$config_[$key] = $value;
   }
   
   public static function getConfig()
   {
       self::init();
       return self::$config_;
   }
   
   public static function config(string $index) // returns either string or array
   {
      self::init();
      return self::$config_[$index];
   }
};
