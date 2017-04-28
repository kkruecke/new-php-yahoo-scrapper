<?php declare(strict_types=1);	
namespace Yahoo;

class EarningsTable implements \IteratorAggregate, TableInterface {
   
   private   $domTable; 

   private static $data_table_query = "//table[contains(@class, 'data-table')]";
   private static $total_results_query = "//div[@id='fin-cal-table']//span[contains(text(), 'results')]";
   
   private   $row_count;
   private   $url;

   private $input_column_indecies;   // indecies of column names ordered in those names appear in config.xml  
   private $input_order = array();   // Associative array of abbreviations mapped to indecies indicating their location in the table.

  public function __construct(\DateTime $date_time, array $column_names, array $output_ordering) 
  {
    $dom_first_page = $this->loadHTML($date_time); // load initial more, there may be more which buildDOMTable() will fetch.

    $this->createDOMTable($dom_first_page); 

    $this->getColumnOrder($dom_first_page, $column_names, $output_ordering);

    $this->createInputOrdering($output_ordering);

    $this->buildDOMTable($dom_first_page, $date_time);
      
    //--$this->debug_show_table();
  }

  private function loadHTML(\DateTime $date_time, $extra_page_num=0) : \DOMDocument
  {
    $url = self::make_url($date_time, $extra_page_num);
  
    $page = $this->get_html_file($date_time, $url);

    $dom = new \DOMDocument('1.0', 'utf-8');
     
    // load the html into the object
    $dom->strictErrorChecking = false; // default is true.
      
    // discard redundant white space
    $dom->preserveWhiteSpace = false;
  
    @$boolRc = $dom->loadHTML($page);  // Turn off error reporting
    
    return $dom;
  } 
  
  private function getExtraPagesCount(\DOMDocument $dom_first_page) : int
  {
      /* 
       * All these XPath queries work, starting with the most general at the top:
       *
       * //div[@id='fin-cal-table']                            find any div whose id is 'fin-cal-table'
       * //div[@id='fin-cal-table']//span                      After find that div, find any spans anywhere under it
       * //div[@id='fin-cal-table']//span[text()='1-100 of 1169 results']      ...that have the specified text    
       * //div[@id='fin-cal-table']//span[contains(text(), 'results')]         ...that contain the specified text   
       *
       *  The last query above is the one we really want: 
       */
      $xpath = new \DOMXPath($dom_first_page);

      $nodeList = $xpath->query(self::$total_results_query); 
            
      if ($nodeList->length == 0) { // div was not found.
      
          throw new \Exception("Total Results could not be found. Total Results XPath query failed!");
      }    
          
      $nodeElement = $nodeList->item(0);
          
      preg_match("/(\d+) results/", $nodeElement->nodeValue, $matches);
      
      $earning_results = (int) ($matches[1]);
      
      return (int) floor($earning_results/100);
  } 

  private function createDOMTable(\DOMDocument $dom_first_page)
  {
      $this->domTable = new \DOMDocument('1.0', 'utf-8');
      
      $page_text = <<<EOT
<html>
    <head>
        <title></title>
        <meta charset="UTF-8">
    </head>
    <body>
      <table><tbody></tbody></table> 
    </body>
</html>
EOT;

     $this->domTable->loadHTML($page_text);
  }

  private function buildDOMTable(\DOMDocument $dom_first_page, \DateTime $date_time)
  {
     $this->appendRows($this->domTable, $dom_first_page);

     $extra_pages = $this->getExtraPagesCount($dom_first_page); 

     $this->row_count = 0;
           
      // Add a table node to $domTable. See Paula code.
     for($extra_page = 1; $extra_page <= $extra_pages; ++$extra_page)  {    
         
         echo  "...fetching additional results page $extra_page of $extra_pages extra pages";       

         $dom_extra_page = $this->loadHTML($date_time, $extra_page);  // BUG: We are not adding the rows of the first page.

         $row_count = $this->appendRows($this->domTable, $dom_extra_page);

         $this->row_count += $row_count;
     }
  }

  private function appendRows(\DOMDocument $domTable, \DOMDocument $dom_page) 
  { 
     $xpath_src = new \DOMXPath($dom_page); 
    
     $trNodeList_src = $xpath_src->query(self::$data_table_query . "/tbody/tr"); // was "(//table)[2]/tbody/tr"
    
     echo  "...appending its rows\n"; 
     
     $dest_node_list = $domTable->getElementsByTagName("tbody"); 
     
     $dest_node = $dest_node_list->item($dest_node_list->length - 1);
    
     foreach($trNodeList_src as $trNode) { // append extra rows to the 
        
         $importedNode = $domTable->importNode($trNode, true);        
        
         $dest_node->appendChild($importedNode); // Append imported node to the tableNode of the DOMDocument at $this->domTable.
     }    

     return $trNodeList_src->length;
  }
 
  /*
   *  Input:
   *  $column_names = Configuration::config('column-column_names') 
   *  $output_ordering  =  Configuration::config('output-order') 
   */ 
  private function getColumnOrder(\DOMDocument $dom_first_page, $column_names, $output_ordering) 
  {  
     $config_col_cnt = count($column_names);

     $xpath = new \DOMXPath($dom_first_page);
     
     $query = self::$data_table_query . "/thead/tr/th";
     
     $thNodelist = $xpath->query($query);

     $arr = array();
     
     $col_num = 0; 

     do {
 
        $thNode = $thNodelist->item($col_num);
        
        $index = array_search($thNode->nodeValue, $column_names); 
                
        if ($index !== FALSE) {
            
            $this->input_column_indecies[] = $col_num; 
        } 

        if (++$col_num == $thNodelist->length) { // If no more columns to examine...
            
            if (count($this->input_column_indecies) != $config_col_cnt) { // ... if we didn't find all the $column_names
                
                throw new Exception("One or more column names specificied in config.xml were not found in the earning's table's column headers.");
            }
            break;    
        }
        
     } while(count($this->input_column_indecies) < $config_col_cnt);
  }
  
  private function createInputOrdering(array $output_ordering)
  {
     $abbrevs = array_keys($output_ordering);
    
    for($i = 0; $i < count($abbrevs); ++$i) { // we ignore output_input
      
       $this->input_order[$abbrevs[$i]] = $this->input_column_indecies[$i];
    }
  }

  public function getInputOrder() : array
  {
    return $this->input_order;
  }
  
  /*
    Input: abbrev from confg.xml
    Output: Its index in the returned getRowData() 
   */
  public function getRowDataIndex(string $abbrev) : int
  {
    // If it is a valid $abbrev, return its index.  
    return array_search($abbrev, $this->input_order) !== false ? $this->input_order[$abbrev] : false;
  }
   
  function getChildNodes(\DOMNodeList $NodeList)  : \DOMNodeList // This might not be of use.
  {
      if ($NodeList->length != 1) { 
         
          throw new \Exception("DOMNodeList length is not one.\n");
      } 
      
      // Get DOMNode representing the table. 
      $DOMElement = $NodeList->item(0);
      
      if (!$DOMElement->hasChildNodes()) {
         
         throw new \Exception("hasChildNodes() failed.\n");
      } 
  
      // DOMNodelist for rows of the table
      return $DOMElement->childNodes;
  }

  /*
   * returns SplFixedArray of all text for the columns indexed by $this->input_column_indecies in row number $row_num
   */ 
  public function getRowData($row_num) : \SplFixedArray
  {
     $row_data = new \SplFixedArray(count($this->input_column_indecies));

     $xpath = new \DOMXPath($this->domTable);

     $tdNodelist =  $xpath->query("//table/tbody/tr[" . $row_num + 1 . "]/td"); // BUG: "Invalid expression" && TODO: Make this a class static?

     /*
     // get DOMNode for row number $row_id
     
     $rowNode =  $trNodelist->item($row_num);

     // get DOMNodeList of <td></td> elemnts in the row     
     $tdNodelist = $rowNode->getElementsByTagName('td');
     */
 
     $i = 0;

     foreach($this->input_column_indecies as $index) {

         $td = $tdNodelist->item($index);     

         $nodeValue = trim($td->nodeValue);
        
         $row_data[$i++] = html_entity_decode($nodeValue);

     }
     return $row_data;
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
  
  static private function make_url(\DateTime $date_time, int $extra_page_num=0) : string
  {
      $offset = $extra_page_num * 100;
      
      return Configuration::config('url') . '?day=' . $date_time->format('Y-m-d') . "&offset={$offset}&size=100";
  }

  private function get_html_file(\DateTime $date_time, string $url) : string
  {
      for ($i = 0; $i < 2; ++$i) {
          
         $page = @file_get_contents($url, false, $context);
                 
         if ($page !== false) {
             
            break;
         }   
         
         $friendly_date = $date_time->format("m-d-Y"); 
         
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
  public function getIterator() : \Yahoo\EarningsTableIterator
  {
     return new EarningsTableIterator($this);
  }

  public function row_count() : int
  {
     return $this->row_count;
  } 

  public function column_count() : int
  {
     return count($this->input_column_indecies);
  }
  
  private function debug_show_table()
  {
      echo "\nDisplaying rows for \$this->domTable\n";
      
      $xpath = new \DOMXPath($this->domTable);
      
      $trNodeList = $xpath->query("//table/tbody/tr");
      
      echo "Row count of \$this->domTable is " . $trNodeList->length . "\n";
      
      foreach($trNodeList as $trNode) {
          
          echo $trNode->nodeValue . "\n";
      }
  }
} 