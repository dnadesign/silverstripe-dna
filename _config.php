<?php

// only log warnings and errors to Raygun rather than notices. Notices should
// be logged to the web server and available on demand rather than in error
// reports.
if(defined('SS_RAYGUN_APP_KEY')) {
	SS_Log::add_writer(new DnaRaygunLogWriter_Zend(), SS_Log::WARN, "<=");
}
