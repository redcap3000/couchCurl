<?php
// Your couch database location (complete with http or https)
define('COUCH_HOST','http://localhost');
// Your database name, although most methods allow you to override this
define('COUCH_DB','couchdb');
// For methods that need authentication, your server setup may require this for more,
// so change COUCH_HOST to this variable, ex: updates (by default) need authentication
define('COUCH_SHOST','https://username:password@localhost');
// Returns a reduced version of emit couch output for the view() method to show design document views
define('REDUCE_EMIT',true);