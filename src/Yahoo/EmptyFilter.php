<?php declare(strict_types=1);
namespace Yahoo;
      
class EmptyFilter extends \FilterIterator {
    
   public function __construct(\Iterator $iter)
   {
	parent::__construct($iter);
   }	   

   public function accept() : bool
   {
        return false;
    }
}
