<?php
$config['url-path'] = "http://finance.yahoo.com/calendar/earnings";

$config['help'] = "Usage: Enter a date in mm/dd/YYYYY format follow by a number between 0 and 40.";

$config['stock-symbol-column'] = 0;
$config['columns'][] = 'Symbol';
$config['columns'][] = 'Company';
$config['columns'][] = 'EPS Estimate';
$config['columns'][] = 'Reported EPS';
$config['columns'][] = 'Surprise(%)';
$config['columns'][] = 'Earnings Call Time';
