pplc-php
===

pplc-php is very simple php impelentation to analyze heroku log files.

For every input search patterns, script can calculate:

* The amount of times the URL was called.
* The mean (average), median and mode of the response time (connect time + service time).
* The "dyno" that responded the most.

Example of output:

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
