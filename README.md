pplc-php
===

pplc-php is very simple php impelentation to analyze heroku log files.

For every input search patterns, script can calculate:

* The amount of times the URL was called.
* The mean (average), median and mode of the response time (connect time + service time).
* The "dyno" that responded the most.

Goals
---
pplc-php is very simple script without optimizations for performance. General goal - quick and simple implementation of test task.
pplc-php works slowly and may have problems with huge log files. 

How to run
---
`php createReport.php`

Features
---
1. pplc-php support different log files formats.

Format patterns easy to define via $logformat variable in createReport.php:

`$logformat='{timestamp} {source}[{process}]: at={log_level} method={http_method} path={http_path} host={http_host} fwd="{client_ip}" dyno={responding_dyno} connect={connection_time}ms service={processing_time}ms status={http_status} bytes={bytes_sent}';`

2. Search Patterns defines via $SearchPatterns variable (createReport.php). Example:
```
$SearchPatterns = array(
    'GET /api/users/{user_id}/count_pending_messages',
	'GET /api/users/{user_id}/get_messages',
	'GET /api/users/{user_id}/get_friends_progress',
	'GET /api/users/{user_id}/get_friends_score',
	'POST /api/users/{user_id}',
	'GET /api/users/{user_id}'
);
```

3. Output and input files can difines in createReport.php file.
`$PPLCParser->AnalyseLogFile("sample.log",$SearchPatterns,"report.txt");`

Example of output:
---
```
-------------------------------------
Report generated: 2014-10-08 05:20:47
-------------------------------------

Pattern "GET /api/users/{user_id}/count_pending_messages"
matched: 2430 times, average response time: 25.99670781893ms, median of response time: 65ms, mode of reponse time: 11ms, the most responded dyno: web.6

Pattern "GET /api/users/{user_id}/get_messages"
matched: 652 times, average response time: 62.170245398773ms, median of response time: 188ms, mode of reponse time: 23ms, the most responded dyno: web.7

Pattern "GET /api/users/{user_id}/get_friends_progress"
matched: 1117 times, average response time: 111.89704565801ms, median of response time: 34ms, mode of reponse time: 35ms, the most responded dyno: web.10

Pattern "GET /api/users/{user_id}/get_friends_score"
matched: 1533 times, average response time: 228.76516634051ms, median of response time: 162ms, mode of reponse time: 67ms, the most responded dyno: web.13

Pattern "POST /api/users/{user_id}"
matched: 2036 times, average response time: 82.453831041257ms, median of response time: 294ms, mode of reponse time: 23ms, the most responded dyno: web.1

Pattern "GET /api/users/{user_id}"
matched: 6293 times, average response time: 96.707611631972ms, median of response time: 936ms, mode of reponse time: 11ms, the most responded dyno: web.13
```
