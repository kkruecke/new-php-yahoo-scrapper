<?php declare(strict_types=1);	
namespace Yahoo;

class EarningsTable implements \IteratorAggregate, TableInterface {

   // TODO: Return an asso. array indexed by the abbreviation.????
   private   $dom;	
   private   $trDOMNodeList;
   private   $row_count;
   private   $url;

   private $input_column_indecies; // Associative array of names mapped to indecies.
   private $abbrev_mapping = array();

  public function __construct(\DateTime $date_time, array $column_names, array $column_info) 
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
 
    $this->url = self::make_url($date_time);
  
    $page = $this->get_html_file($this->url);
       
    $this->loadHTML($page);

    $this->loadRowNodes($column_names, $column_info); 
  }

  function loadRowNodes(array $column_names, array $column_info)
  {
      $xpath = new \DOMXPath($this->dom);
            
      $nodeList = $xpath->query("(//table)[2]");

      $tblElement = $nodeList->item(0);

      $nodeList = $xpath->query("tbody", $tblElement);

      $this->trDOMNodeList = $this->getChildNodes($nodeList);

      $nodeList = $xpath->query("thead/tr", $tblElement);

      $childNodes = $this->getChildNodes($nodeList);
 
      $this->findRelevantColumns($xpath, $tblElement, $column_names, $column_info);
  } 

  /*
   *  Input:
   *  $column_names = Configuration::config('column-column_names') 
   *  $column_info  =  Configuration::config('column-info') 
   */ 
  private function findRelevantColumns(\DOMXPath $xpath, \DOMElement $DOMElement, $column_names, $column_info) 
  {  
     $col_cnt = count($column_names);

     $thNodelist = $xpath->query("thead/tr/th", $DOMElement);

     $arr = array();
     
     for ($col_num = 0; $col_num < $thNodelist->length; ++$col_num) {

        $thNode = $thNodelist->item($col_num);
        
        $index = array_search($thNode->nodeValue, $column_names); 
                
        if ($index === FALSE) {
             continue; 
         } 
         
        $this->input_column_indecies[] = $col_num; 

        if (count($this->input_column_indecies) == $col_cnt) 
            break;
    } 
    
    $this->createAbbrevMapping($column_info);
  }

  private function createAbbrevMapping(array $col_info)
  {
    $col_info = Configuration::config('column-info'); 
    
    $abbrevs = array_keys($col_info);
    
    for($i = 0; $i < count($col_info); ++$i) { // we ignore output_input
      
      $this->abbrev_mapping[$abbrevs[$i]] = $this->input_column_indecies[$i];
    }
  }

  public function getAbbrevMapping() : array
  {
    return $this->abbrev_mapping;
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
 
     // get DOMNode for row number $row_id
     $rowNode =  $this->trDOMNodeList->item($row_num);

     // get DOMNodeList of <td></td> elemnts in the row     
     $tdNodelist = $rowNode->getElementsByTagName('td');

     $i = 0;

     foreach($this->input_column_indecies as $index) {

         $td = $tdNodelist->item($index);     

         $nodeValue = trim($td->nodeValue);
        
         $row_data[$i++] = html_entity_decode($nodeValue);

     }
     return $row_data;
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
        //--return Registry::registry('url-path') . '?day=' . $date_time->format('Y-m-d');
        return Configuration::config('url') . '?day=' . $date_time->format('Y-m-d');
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
  public function getIterator() : \Yahoo\EarningsTableIterator
  {
     return new EarningsTableIterator($this);
  }

  public function row_count() : int
  {
     return $this->trDOMNodeList->length;
  } 

  public function column_count() : int
  {
     return count($this->input_column_indecies);
  }
} 