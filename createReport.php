<?php

$logformat='{timestamp} {source}[{process}]: at={log_level} method={http_method} path={http_path} host={http_host} fwd="{client_ip}" dyno={responding_dyno} connect={connection_time}ms service={processing_time}ms status={http_status} bytes={bytes_sent}';

$SearchPatterns = array(
	'GET /api/users/{user_id}/count_pending_messages',
	'GET /api/users/{user_id}/get_messages',
	'GET /api/users/{user_id}/get_friends_progress',
	'GET /api/users/{user_id}/get_friends_score',	
	'POST /api/users/{user_id}',
	'GET /api/users/{user_id}'
);

require_once("class_PPLCParser.php");
$PPLCParser = new PPLCParser($logformat);

$PPLCParser->AnalyseLogFile("sample.log",$SearchPatterns,"report.txt");
