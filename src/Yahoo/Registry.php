<?php
namespace Yahoo;

class Registry {

  //--static private \ArrayObject  $registry; 
  static private $registry; 

  public function __construct(array $ini_array)
  {
      self::init();
  }

  protected static function init()
  {	  
     if (!isset(self::$registry)) {	  
	  
        @$ini_map = parse_ini_file("yahoo.ini", true); 
        self::$registry = new \ArrayObject($ini_map); 
     }
  }

  //--public static function register(string $property, string $value) : void
  public static function register($property, $value)
  {
	  self::init();
	  self::$registry->offsetSet($property, $value);
  }

  //--public static function registry($key) : ?string
  public static function registry($key)
  {
       self::init();

       if (self::$registry->offsetExists($key)) {

	  return self::$registry->offsetGet($key);

       } else {

 	  return null;	
       }
  }
}
