<?php declare(strict_types=1);
namespace Yahoo;

class Registry {

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

  public static function register(sting $property, string $value)  // ": void " is coming in php7.1
  {
	  self::init();
	  self::$registry->offsetSet($property, $value);
  }

  //--public static function registry($key) : ?string
  public static function registry(string $key) : string // ?string
  {
       self::init();

       if (self::$registry->offsetExists($key)) {

	  return self::$registry->offsetGet($key);

       } else {

 	  return null;	
       }
  }
}
