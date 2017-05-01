<?php declare(strict_types=1);
namespace Yahoo;

class CSVWriter {

  private $splfile;
  private $line_count;
  private $formatter;
  
  public function __construct(CSVFormatter $formatter, SplFileObjectExtended $fobject=null)
  {
    $this->formatter = $formatter;

    $this->splfile = $fobject;

    $this->line_count = 0;
  }

  public function attach(\SplFileObject $fobject)
  {
    // Note: Not sure if I must first do "$this->splfile = null" if $this->splfile exists?
    $this->splfile = $fobject; 
  }

  public function unlink() // deletes file
  {
    $this->splfile = null;
    unlink($this->file_name);
  }

  public function getFileName() : string
  {
     return $this->file_name;
  }

  public function __destruct()
  {
     unset($this->splfile);
  }

  public function writeLine(\SplFixedArray $row_data,\DateTime $date)  //  : void is php 7.1
  { 
      /*
       * Format the $row_data	  
       */ 	  

      $csv_str = $this->formatter->format($row_data, $date); 
      
      $csv_str .= "\n"; // replace terminating ',' with newline.
                   
      $this->splfile->fwrite($csv_str);
      
      $this->line_count++;
  }
  
  public function getLineCount() : int
  {
      return $this->line_count;
  }

}
