<?php

// https://gitlab.com/davical-project/davical.git
require_once(dirname(__FILE__) . '/davical/inc/caldav-client.php');
// https://github.com/u01jmg3/ics-parser.git
require_once(dirname(__FILE__) . '/ics-parser/src/ICal/ICal.php');
require_once(dirname(__FILE__) . '/ics-parser/src/ICal/Event.php');

use ICal\ICal;

const LINELENGTH = 24;
//const CALDAVURL = 'https://www.google.com/calendar/dav/xxxxxxxxxx@gmail.com/events';
const CALDAVURL = 'https://dav.mailbox.org/caldav/26';

function printnewline() {
	echo "\r\n";
}

function printline($str = '') {
	echo "$str";
	if ((strlen($str) % LINELENGTH) > 0)
		printnewline();
}

function printseparator() {
	printline(str_pad('', LINELENGTH, '-'));
}

function umlauts($str = '') {
	$sub = array('Ä'=>'Ae', 'Ö'=>'Oe', 'Ü'=>'Ue', 'ä'=>'ae', 'ö'=>'oe', 'ü'=>'ue', 'ß'=>'ss');
	return strtr($str, $sub);
}

header('Content-Type: text/plain; charset=iso-8859-1');
if (!isset($_SERVER['PHP_AUTH_USER'])) {
	header('WWW-Authenticate: Basic realm="Calendar"');
	header('HTTP/1.0 401 Unauthorized');
	exit('HTTP Basic authentication required.');
}

$today = date('Ymd');
$dav = new CalDAVClient(CALDAVURL, strip_tags($_SERVER['PHP_AUTH_USER']), strip_tags($_SERVER['PHP_AUTH_PW']));
$davoptions = $dav->DoOptionsRequest();
if (!isset($davoptions['PROPFIND'])) {
	exit('CalDAV request failed.');
}
$davevents = $dav->GetEvents("${today}T000000Z", "${today}T235959Z");

printline(date('d.m.Y', strtotime($today)));
foreach ($davevents as $davevent) {
	$cal = new ICal();
	$cal->initString($davevent['data']);
	$calevents = $cal->eventsFromRange("${today}T000000Z", "${today}T235959Z");
	foreach ($calevents as $calevent) {
		printseparator();
		printline(date('H:i', strtotime($calevent->dtstart)) . '-' . date('H:i', strtotime($calevent->dtend)) . ' ' . substr(umlauts($calevent->location), 0, LINELENGTH - 12));
		printline(substr(umlauts($calevent->summary), 0, 2 * LINELENGTH));
	}
}
printseparator();
printnewline();

?>
