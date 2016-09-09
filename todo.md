# php7 Support Added

# BUGS

## YahooTable Use fixed ini values for start and end column

There is a problem with the output sometimes being narrower than the width the stock symbol. The start\_column and end\_colun used in
YahooTable and also YahooTableIterator is simply the hard coded values in the .ini file. 

Maybe these should not be used and instead the code should determine at run time how wide the text is of the largest stock symbol on the page?
