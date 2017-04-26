<?php declare(strict_types=1);
namespace Yahoo;

class CSVWriter {

  private $splfile;
  private $file_name;
  private $line_count;
  private $formatter;
  
  public function __construct($file_name, CSVFormatter $formatter, string $open_mode)
  {
    $this->file_name = $file_name;	  

   /* j --> day without leading zeroes
    * m --> month with leading zeroes
    * T --> four digit year
    */
    $this->formatter = $formatter;
                
    $this->splfile = new \SplFileObject($file_name, $open_mode);

    $this->splfile->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
       
    $this->line_count = 0;
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
