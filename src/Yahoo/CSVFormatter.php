<?php declare(strict_types=1);
namespace Yahoo;

interface CSVFormatter {
  public function format(\SplFixedArray $row, \DateTime $date) : string;
}
