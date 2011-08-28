<?php
/*
	Couch Curl
	Ronaldo Barbachano
	8/11

	Assumes your php has exec() enabled and your php user can use curl.
*/

define('COUCH_HOST','http://localhost:5948');
define('COUCH_DB','example');

class couchCurl{
	function ccurl_put($json,$title){return self::couchCurl('PUT',"$title" . " -d \ '$json'");}

	function ccurl_get($title){return self::couchCurl('GET',"/$title");}

	function ccurl_delete($title){return self::couchCurl('DELETE',"/$title")}

	function couchCurl($query,$method='GET',$no_exe=false){
		$query .= "curl -X $method " . COUCH_HOST."/".COUCH_DB.$query;
	        // returns the appropriate curl call, no exe is for debugging (returns the command as a string)
                $result = ($no_exe==false? exec($query):$query);
        	return ($result?$result:false);
	}
}
