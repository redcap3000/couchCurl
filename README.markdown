        Couch Curl
        ==========
        Ronaldo Barbachano
        8/11

        Super simple couch static class that plugs into PHP exec to do some awesome stuff...
        Stores json to your couch DB, give it the json and a title

        Assumes your php has exec() enabled and your php user can use curl.

        Aims for quick and easy access to local couch databases without authentication, with little php processing overhead.
        
        Not designed (yet) for updating records! You may want to use a different couch library..

        Originally designed to store output from API's as local cache.
        
        // If you need to use a different database for any function, designate it as the last argument in each method. (except _cc_changes)
        
        //Include couchCurl.php
        // Store Json
        couchCurl::_cc_put('json string','record_id');
        // Or
        couchCurl::_cc_put('json string');
        // Get Record
        couchCurl::_cc_get('record_id');
        // Remove Record
        couchCurl::_cc_delete('record_id','rev_id');
        // Set Revision Limit
        couchCurl::_cc_get_revs_limit();
        // Get All Dbs
        couchCurl::_cc_get_all_dbs()
        // Changes (also supports the options, provide assoc. array to use.
        couchCurl::_cc_changes());
	// makes a copy of document_id and saves it as new_document_id
	couchCurl::_cc_copy('document_id','new_document_id');
	// Copys a specific revision of the document to copy
	couchCurl::_cc_copy('document_id','new_document_id','document_id_rev');
	// Copies to an existing document
	couchCurl::_cc_copy_to('document_id','existing_doc_id','existing_doc_latest_rev')
	// gets all docs from the php definition COUCH_DB
	couchCurl::_cc__all_docs()
	// gets all docs from database named 'database'
	couchCurl::_cc_all_docs('database')
	// gets all docs from database 'database' with an optional associtatve array with parameters
	couchCurl::_cc_all_docs('database',array('descending'=>'true'));
	// gets documents doc_id_1 + doc_id_2 from the definition COUCH_DB, without options (uses POST not GET).
	couchCurl::_cc_all_docs(NULL,NULL,array('doc_id_1','doc_id_2'));
