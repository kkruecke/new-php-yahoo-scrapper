<?php declare(strict_types=1);	
namespace Yahoo;

class YahooEarningsTable implements \IteratorAggregate {

   private   $dom;	
   private   $xpath;	
   private   $trDOMNodeList;
   private   $tbl_begin_column;
   private   $tbl_end_column;

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

  /*
   * Preconditions: 
   * url exists
   * xpath is accurate
   * start and end column are within range of columns that exist
   */ 
 
  public function __construct(\DateTime $date_time, /*string $url,*/ string $xpath_table_query, int $tbl_begin_column, int $tbl_end_column)        
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
   
    $this->tbl_begin_column = $tbl_begin_column;	  
    $this->tbl_end_column = $tbl_end_column;;	  
     
    // a new dom object
    $this->dom = new \DOMDocument();
    
    // load the html into the object
    $this->dom->strictErrorChecking = false; // default is true.
    
    // discard redundant white space
    $this->dom->preserveWhiteSpace = false;

    @$this->dom->loadHTML($page);  // Turn off error reporting
       
    $this->xpath = new \DOMXPath($this->dom);
    
    // From returned \DOMNodeList we get the first (and only node), the table.
    $xpathNodeList = $this->xpath->query($xpath_table_query);
        
    if ($xpathNodeList->length != 1) { 
       
        throw new \Exception("XPath Query\n $xpath_table_query\nof page: $url\n   \nFailed!\n Check if page format has changed. It appears to have more than one table. Cannot proceed.\n");
    } 
    
    // Get DOMNode representing the table. 
    $tableNodeElement = $xpathNodeList->item(0);
    
    /* 
     * We need to as the $tableNodeElement->length to get the number of rows. We will subtract the first two rows --
     * the "Earnings Announcement ..." and the columns headers -- and we ignore the last row.
     * Path queries for the first two rows:
     *
     * 1.  /html/body/table[3]/tr/td[1]/table[1]/tr[1] is "Earnings Announcements for Wednesday, May 15"
     * 2.  /html/body/table[3]/tr/td[1]/table[1]/tr[2] is column headers
     */
    
    if (!$tableNodeElement->hasChildNodes()) {
       
       throw new \Exception("This is no table element at \n $xpath_table_query\n. Page format has evidently changed. Cannot proceed.\n");
    } 

    // DOMNodelist for rows of the table
    $this->trDOMNodeList = $tableNodeElement->childNodes;
  }

  static private function make_url(\DateTime $date_time) : string
  {
    return Registry::registry('url-path') . '?day=' . $date_time->format('Y-m-d');
  }

 /*
  * Return external iterator, passing the range of columns requested.
  */ 
  public function getIterator() : \Yahoo\YahooTableIterator
  {
     return new YahooTableIterator($this, $this->tbl_begin_column, $this->tbl_end_column);
  }

  public function rowCount() : int
  {
     return $this->trDOMNodeList->length;
  } 

  public function columnCount($rowid) : int
  {
     return $this->getTdNodelist($rowid)->length;
  }

  /*
   * Returns trimmed cell text
   */  
  public function getCellText(int $rowid, int $cellid) :  string
  {
      if ($rowid >= 0 && $rowid < $this->rowCount() && $cellid >= 0 && $cellid < $this->columnCount($rowid)) { 	  

        $tdNodelist = $this->getTdNodelist($rowid);
          
        $td = $tdNodelist->item($cellid);  

	$nodeValue = trim($td->nodeValue);

	return $nodeValue;

      } else {

          $row_count = $this->rowCount();

          $column_count = $this->columnCount($rowid);

	  //throw new \RangeException("Either row id of $rowid or cellid of $cellid is out of range. Row count is $row_count. Column count is $column_count\n");
	  echo "Either row id of $rowid or cellid of $cellid is out of range. Row count is $row_count. Column count is $column_count\n";
	  return "meaningless";
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
