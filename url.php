<?


function url_exists($url)
{
    if (!$fp = curl_init($url))
           return false;

    return true;
}

function urlExists(string $url) : bool
{
   $ch = curl_init($url);
   
   curl_setopt($ch, CURLOPT_TIMEOUT, 5);
   
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
   
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   
   $data = curl_exec($ch);
   
   $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   
   curl_close($ch); 
   
   if ($httpcode >= 200 && $httpcode < 300) {

        return true;

   } else {

	return false;
   }
}
