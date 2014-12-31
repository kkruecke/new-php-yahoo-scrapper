<?php	
namespace Yahoo;

class YahooTable implements \IteratorAggregate {

   private   $dom;	
   private   $xpath;	
   private   $trNodesList;
   protected $trdNodesList;
   private   $start_column;
   private   $end_column;

  /*
   * Preconditions: 
   * url exists
   * xpath is accurate
   * start and end column are within range of columns that exist
   */ 
 
  public function __construct($url, $xpath_table_query, $start_column, $end_column)        
  {
   /*
    * The column of the table that the external iterator should return
    */ 	  
     $this->start_column = $start_column;	  
     $this->end_column = $end_column;;	  

     //$page = file_get_contents($url);
     $page = @file_get_contents($url);

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
    
     if ($xpathNodeList->length != 1) { // TODO: Sometimes this if test succeeds. Is it only when there is  
        
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
  public function getIterator()
  {
     return new YahooTableIterator($this, $this->start_column, $this->end_column);
  }

  //--public function rowCount() : int
  public function rowCount()
  {
     return $this->getRowsNodelist()->length;
  } 

  //--public function columnCount(int $rowid) : int
  public function columnCount($rowid)
  {
     return $this->getTdNodelist($rowid)->length;
  }

  /*
   * return cell text trimmed
   */  
  public function getCellText($rowid, $cellid) // returns string          
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

  protected function getRowsNodelist() // returns \DOMNodeList
  {
      return $this->trNodesList;
  }

  // get td node list for row 
  protected function getTdNodelist($row_id)
  {
     // get DOMNode for row $row_id
     $rowNode =  $this->getRowsNodelist()->item($row_id);

     // get DOMNodeList for td cells in the row     
     return $rowNode->getElementsByTagName('td');
  }
 
} // end class
