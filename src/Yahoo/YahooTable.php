<?php	
namespace Yahoo;

class YahooTable implements \IteratorAggregate {

   private   $dom;	
   private   $xpath;	
   private   $trNodesList;
   private   $start_column;
   private   $end_column;

  /*
   * Preconditions: 
   * url exists
   * xpath is accurate
   * start and end column are within range of columns that exist
   */ 
 
  public function __construct(string $friendly_date, string $url, string $xpath_table_query, int $start_column, int $end_column)        
  {
   /*
    * The column of the table that the external iterator should return
    */ 	  
     $this->start_column = $start_column;	  
     $this->end_column = $end_column;;	  
     
     /* Alternate code to use Guzzle
     $client = new \GuzzleHttp\Client();
     
     for($i = 0; $i <2; ++$i) {
         
       try {
                
        $response = $client->get($url);
        $body = $response->getBody();
     
        $page = $body->__toString();

        break;
        
       } catch (\Exception $e) {
             
          echo "Attempt to download page $url failed.  Retrying...\n";
          
          echo $e->getMessage() . "\n\n";
	  echo $e->getTraceAsString() . "\n\n";
          
         
          continue;
       }
     }
     */
        
    $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n" .  "Cookie: foo=bar\r\n")
                 );

    $context = stream_context_create($opts);
    
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
   
    
     // a new dom object
     $this->dom = new \DOMDocument();
     
     // load the html into the object
     $this->dom->strictErrorChecking = false; // default is true.

    @$this->dom->loadHTML($page);  // Turn off error reporting
     
     // discard redundant white space
     $this->dom->preserveWhiteSpace = false;
    
     $this->xpath = new \DOMXPath($this->dom);
    
     // returns \DOMNodeList. We must first get the first and only node, the table.
     $xpathNodeList = $this->xpath->query($xpath_table_query);
    
     if ($xpathNodeList->length != 1) { 
        
         throw new \Exception("XPath Query\n $xpath_table_query\nof page: $url\n   \nFailed!\n Check if page format has changed. Cannot proceed.\n");
     } 

     // DOMNode representing the table. 
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
      $this->trNodesList = $tableNodeElement->childNodes;
  }

 /*
  * Return external iterator, passing the range of columns requested.
  */ 
  public function getIterator() : \Yahoo\YahooTableIterator
  {
     return new YahooTableIterator($this, $this->start_column, $this->end_column);
  }

  //--public function rowCount() : int
  public function rowCount() : int
  {
     return $this->getRowsNodelist()->length;
  } 

  //--public function columnCount(int $rowid) : int
  public function columnCount($rowid) : int
  {
     return $this->getTdNodelist($rowid)->length;
  }

  /*
   * return cell text trimmed
   */  
  public function getCellText(int $rowid, int $cellid) :  string
  {
      if ($rowid >= 0 && $rowid < $this->rowCount() && $cellid >= 0 && $cellid < $this->columnCount($rowid)) { 	  

        $tdNodelist = $this->getTdNodelist($rowid);
          
        $td = $tdNodelist->item($cellid);  

	$nodeValue = trim($td->nodeValue);

        // TODO: I think we need to change the code to get the length of the cell text?

	return $nodeValue;

      } else {

          $row_count = $this->rowCount();

          $column_count = $this->columnCount($rowid);

	  //throw new \RangeException("Either row id of $rowid or cellid of $cellid is out of range. Row count is $row_count. Column count is $column_count\n");
	  echo "Either row id of $rowid or cellid of $cellid is out of range. Row count is $row_count. Column count is $column_count\n";
	  return "meaningless";
      }
  }

  protected function getRowsNodelist() : \DOMNodeList
  {
      return $this->trNodesList;
  }

  // get td node list for row 
  protected function getTdNodelist($row_id) : \DOMNodeList
  {
     // get DOMNode for row $row_id
     $rowNode =  $this->getRowsNodelist()->item($row_id);

     // get DOMNodeList for td cells in the row     
     return $rowNode->getElementsByTagName('td');
  }
 
} // end class
