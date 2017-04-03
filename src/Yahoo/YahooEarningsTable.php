<?php declare(strict_types=1);	
namespace Yahoo;

class YahooEarningsTable implements \IteratorAggregate, Table {

   private   $dom;	
   private   $trDOMNodeList;
   private   $row_count;
   private   $column_count;
   
  /*
   * Preconditions: 
   * url exists
   * xpath is accurate
   * start and end column are within range of columns that exist
   */ 
 
  public function __construct(\DateTime $date_time)
  {
   /*
    * The column of the table that the external iterator should return
    */ 	  
    $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n" .  "Cookie: foo=bar\r\n")
                 );

    $context = stream_context_create($opts);

    $friendly_date = $date_time->format("m-d-Y"); 

    $url = self::make_url($date_time);  

    $page = $this->get_html_file($url); //++
       
    $this->loadHTML($page);

    $this->trDOMNodeList = $this->get_DOMNodeList(Registry::registry('tbody-xpath-query'));

    $thDOMNodeList = $this->get_DOMNodeList(Registry::registry('thead-xpath-query'));

    $this->column_count = $thDOMNodeList->length; // <--- TODO: This is wrong
  }

  private function loadHTML(string $page) : bool
  {
      $this->dom = new \DOMDocument();
      
      // load the html into the object
      $this->dom->strictErrorChecking = false; // default is true.
      
      // discard redundant white space
      $this->dom->preserveWhiteSpace = false;
  
      @$boolRc = $this->dom->loadHTML($page);  // Turn off error reporting
       return $boolRc;
  } 

  private function get_DOMNodeList(string $xpath_query) : \DOMNodeList
  {
      $xpath = new \DOMXPath($this->dom);
   
      $xpathNodeList = $xpath->query($xpath_query);
          
      if ($xpathNodeList->length != 1) { 
         
          throw new \Exception("XPath Query\n $xpath_query\nof page: $url\n   \nFailed!\n Check if page format has changed. It appears to have more than one table. Cannot proceed.\n");
      } 
      
      // Get DOMNode representing the table. 
      $tableNodeElement = $xpathNodeList->item(0);
      
      if (!$tableNodeElement->hasChildNodes()) {
         
         throw new \Exception("This is no table element at \n $xpath_table_query\n. Page format has evidently changed. Cannot proceed.\n");
      } 
  
      // DOMNodelist for rows of the table
      return $tableNodeElement->childNodes;
   }

  static public function page_exists(\DateTime $date_time) : bool
  {
    return self::url_exists( self::make_url($date_time) );
  }

  static private function url_exists(string $url) : bool
  {
    $file_headers = @get_headers($url);

    if(strpos($file_headers[0], '404 Not Found') !== false) {

        return false;

    } else {

      return true;
    }
  } 

  static private function make_url(\DateTime $date_time) : string
  {
    return Registry::registry('url-path') . '?day=' . $date_time->format('Y-m-d');
  }

  private function get_html_file(string $url) : string
  {
      for ($i = 0; $i < 2; ++$i) {
          
         $page = @file_get_contents($url, false, $context);
                 
         if ($page !== false) {
             
            break;
         }   
         
         echo "Attempt to download data for $friendly_date on webpage $url failed. Retrying.\n";
      }
      
      if ($i == 2) {
          
         throw new \Exception("Could not download page $url after two attempts\n");
      }
      return $page;
  } 
    
   /*
  * Return external iterator, passing the range of columns requested.
  */ 
  public function getIterator() : \Yahoo\YahooEarningsTableIterator
  {
     return new YahooEarningsTableIterator($this);
  }

  public function row_count() : int
  {
     return $this->trDOMNodeList->length;
  } 

  public function column_count() : int
  {
     return $this->column_count;
  }

  /*
   * Returns trimmed cell text
   */  
  public function getCellText(int $rowid, int $cellid) :  string
  {
      if ($rowid >= 0 && $rowid < $this->row_count() && $cellid >= 0 && $cellid < $this->column_count()) { 	  

        $tdNodelist = $this->getTdNodelist($rowid);
          
        $td = $tdNodelist->item($cellid);  

	$nodeValue = trim($td->nodeValue);

	return $nodeValue;

      } else {

          $row_count = $this->row_count();

          $column_count = $this->column_count();

	  //throw new \RangeException("Either row id of $rowid or cellid of $cellid is out of range. Row count is $row_count. Column count is $column_count\n");
	  throw \RangeException("Either row id of $rowid or cellid of $cellid is out of range. Row count is $row_count. Column count is $column_count\n");
      }
  }
  // get td node list for row 
  protected function getTdNodelist($row_id) : \DOMNodeList
  {
     // get DOMNode for row number $row_id
     $rowNode =  $this->trDOMNodeList->item($row_id);

     // get DOMNodeList of <td></td> elemnts in the row     
     return $rowNode->getElementsByTagName('td');
  }
 
} // end class
