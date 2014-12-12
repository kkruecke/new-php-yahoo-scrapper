<?hh
namespace Yahoo;

class Registry {

  static private \ArrayObject  $registry; 

  public function __construct(array $ini_array)
  {
      self::init();
  }

  protected static function init() : void
  {	  
     if (!isset(self::$registry)) {	  
	  
        @$ini_map = parse_ini_file("yahoo.ini", true); 
        self::$registry = new \ArrayObject($ini_map); 
     }
  }

  public static function register(string $property, string $value) : void
  {
	  self::init();
	  self::$registry->offsetSet($property, $value);
  }

  public static function registry($key) : ?string
  {
       self::init();

       if (self::$registry->offsetExists($key)) {

	  return self::$registry->offsetGet($key);

       } else {

 	  return null;	
       }
  }
}
