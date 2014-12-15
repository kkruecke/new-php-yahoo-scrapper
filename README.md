This is a data scrapper for Yahoo financial data written in PHP. Syntax:

php -f main.php mm/dd/yyyy n

where n is the number of days, or 

yahoo.cmd mm/dd/yyyy n.

The code is configuration-driven by an .ini file that specifies:

* The base url of the Yahoo financial data
* the xpath query used to return the HTML table with the data
* The start column in the html table from which to extract cell text
* One past the last column in the html table from which to extract cell text

The date entered, passed on the command line, is used to construct the specific url path that is then appended to the base url. A second parameter is used to specify
the number of subsequent days for which data should be extracted and written to the .csv file. 

**YahooTable** holds the table with all the financial data. Use the constructor to specify the start and end column that you want its external iterator **YaooTableIterator**
to return.  To fruther limit the range of rows of the iteration, pass **YahooTableIterator** to a **LimitIterator**, for example:

	  // To skip the first two rows, the table description and column headers, as well as the last row, use a LimitIterator.
	  $limitIter = new \LimitIterator($table->getIterator(), 2, $max_rows - 2); 

To further filter the rows returned, extend **FilterIterator** and pass it either a **YahooTableIterator** instance or (as explained above) a **LimitIterator** or a **CallbackFilterIterator**:

	  $filterIter = new \CustomStockFilterIterator($limitIter);

The format of the outputted .csv can be customized by specifying a different formatter, i.e., a class the implements CSVFormatter interface, on the second parameter to
the CSVWriter constructor.
