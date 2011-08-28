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
        //Include couchCurl.php
        // Store Json
        couchCurl::ccurl_put('json string','record_id');
        // Get Record
        couchCurl::ccurl_get('record_id');
        // Remove Record
        couchCurl_delete('record_id');