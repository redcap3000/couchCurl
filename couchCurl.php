<?php

require('cc_config.php');
class couchCurl{
	// if no title provided statement changes to a PUT and the title is generated by couchdb
	static function put($json,$title=NULL,$db=NULL){return self::__cc( ($title == NULL?'POST':'PUT'  ) , ($title==NULL?"'":"/$title'"). " -d \ '$json'",$db);}
	
	static function update($json,$db=NULL){
		// doing it here because of annoying quote bash error for the database..
		$db = self::___db($db);
		// checking for _id and _rev before attempting a curl..	
		$doc = json_decode($json,true);
		
		if($doc['_id'] && $doc['_rev']){
			// this is shennanigans because couch / curl requires that the first two fields in passed json must be the _id and _rev, in that order
			// otherwise you get nothing returned.
			$json= array();	
			$json['_id'] = $doc['_id'];
			$json['_rev'] = $doc['_rev'];
			
			unset($doc['_id'],$doc['_rev']);
			// do some other filtering too ...
			foreach($doc as $key=>$value){
				$json [$key]= (is_numeric($value) ? (int) $value : $value);
			}
			$doc['_id'] = $json['_id'];
			
			$json = json_encode($json);
			exec("curl -X PUT -d \ '$json' '".COUCH_HOST . "/$db/".$doc['_id']  ."' -s -H HTTP/1.0 -H ".'"Content-Type: application/json"',$output);
			return $output[0];
		}else
			return "\nMissing _id and/or _rev fields\n";
	}
	
	static function get($title,$range_stop=FALSE,$db=NULL){return self::__cc('GET',"/$title'",$db);}
	// could combine these but this is probably easier to work with for developers..
	
	static function show($show_title,$design_doc,$id=NULL,$db=NULL,$server=NULL){
	// will show a 'show' with id/without
		$db = ($db == NULL?COUCH_DB:$db);
		$server = ($server == NULL?COUCH_HOST:$server);
		$query = "curl -X GET '$server/$db/_design/$design_doc/_show/$show_title" . ($id?"/$id":'') . "'";
		$json_string = self::_query($query);
		$json_obj = json_decode($json_string,true);
		// not 100% tested on this one..
		return ($json_obj?$json_obj:$json_string);
	}
	
	static function view($key,$view,$design_doc,$db=NULL,$server=NULL,$by_value = false){
		$db = ($db == NULL?COUCH_DB:$db);
		$server = ($server == NULL?COUCH_HOST:$server);
		if(is_string($key)) $key = '"' . urlencode($key).'"';
		$query = "curl -X GET '$server/$db/_design/$design_doc/_view/$view?".($by_value== false?'key':'value')."=$key'";
		$json_string = self::_query($query);
		$json_obj = json_decode($json_string,true);
		// to return an object that is a little easier to work with the id becomes the key and its value is key:-:value
		// a verbose delimiter is used to allow users to use dashes and colons in keys/values
		if(REDUCED_EMIT)
			foreach($json_obj['rows'] as $loc=>$emit){
			// ommits the key and just shows id and value ...
			// great for making selection lists ... but should probably be a method directive ..
				$json_obj['rows'] [$emit['id']] = ($by_value == false? $emit['value'] : $emit['key']);
				unset($json_obj['rows'][$loc]);
			}
		// if json fails then return result which might be an exit status for the exec
		return ($json_obj?$json_obj:$json_string);
	}

	static function delete($title,$rev,$db=NULL){return self::__cc('DELETE',"/$title?rev=$rev'".' -H "Content-Length:1"',$db);}

	static function set_revs_limit($limit=1000,$db=NULL){return(exec('curl -X PUT -d "'.$limit.'" '. "'".COUCH_HOST."/".self::___db($db)."/_revs_limit' -s"));}

	static function changes($db=NULL,$options=NULL){return self::__cc('GET',"/_changes".self::___b_opt($options) ."'",$db);}

	static function get_revs_limit($db=NULL){return(self::_host("/".self::___db($db)."/_revs_limit'"));}

	static function all_docs($db=null,$options=null,$ids=null){
	// provide id's as second param (array with strings) if wanting to do a multi doc select
		return self::__cc( ($ids!=NULL?'POST':'GET')  , "/_all_docs".self::___b_opt($options) .  ($ids!=NULL? "' -d \ '".json_encode(array('keys'=>$ids))."'":"'") ,$db);
	}

	static function copy($doc,$dest,$rev_id=NULL,$db=NULL){
		$query= "/".self::___db($db)."/$doc'". ' -H "Destination: '.$dest.'" -H "Content-Type: application/json"';
		return  self::_host($query,'COPY');
	}
	
	static function copy_to($doc,$dest,$dest_c_rev,$db){
	// Could combine above, but this is easier to understand (experimental...)
	// Provide document id,destination id, and the current ID of that destiation
	// future enhancements could automatically look this up via this library..
		$query= "/".self::___db($db)."/$doc'". ' -H "Destination: '.$dest.'?rev='.$dest_c_rev.'" -H "Content-Type: application/json"';
		return  self::_host($query,'COPY');
	}


	public static function handle_couch_id($id,$base = 12,$decode = false){
	// for compressing couch_ids (integers), can accept a single integer, or a coded string like 
	// "10293:1023:2012:123" for working with relations
	// base refers to the number conversion to perform, When decoding use the same syntax (except with
	// the encoded string as the ID, and designate the final parameter as 'true'
		foreach(explode(':',$id) as $num)
			$r []= ($decode == true? base_convert($num,$base,10): base_convert($num, 10,$base));
		return implode(':',$r);
		
	}

	private static function __cc($method='GET',$query,$db=NULL,$no_exe=false){
		$query = "curl -X $method '" . COUCH_HOST."/".self::___db($db)."$query" . ' -s  -H "HTTP/1.0" '. ($method=='PUT' || $method == 'POST'? ' -H "Content-type: application/json"':NULL) ;
		// returns the appropriate curl call, no exe is for debugging (returns the command as a string)
		$result = ($no_exe==false? exec($query,$output):$query);
		// incase we have a resultset with more than a row - otherwise result is permissible
		if(count($output > 1) && is_array($output)) {
				$output = implode('',$output);
				return $output;
		}
		return ($result?$result:($no_exec==true?$query:false));
	}

	private static function _host($call,$method='GET'){
	// provides more direct access to the couch_host for some specific api calls and returns the first element in an array return
	// found in static functions like get_all_dbs , get_revs_limit
		$query = "curl -X $method '" . COUCH_HOST. $call . " -s";
		$result = exec($query,$output);
		return ($output?$output[0]:$result);
	}
	// built to reduce code, checks options array, returns built query if is array, otherwise null
	// used with static functions that allow options
	private static function ___b_opt($options){return ($options!=NULL && is_array($options)? '?'. http_build_query($options):NULL);}
	private static function ___db($db){return ($db == NULL?COUCH_DB :$db);}
	private static function _query($query){
	// for passing in a query (usually get) and processing the output/ returning as json_string
	// instead of an array with two entries (with json strings)
		$result = exec($query,$output);
		foreach($output as $json)
			$json_string .= $json;
		return ($output?$json_string:$result);
	}
}