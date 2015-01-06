<?php
namespace Yahoo;

interface CSVFormatter {
  public function format(\SplFixedArray $row, \DateTime $date);
}
