<?php

/*
Connect to Google Calendar, Extract Hours & Populate Database
Andrew Darby, agdarby at gmail dot com

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

*/

require_once("config.php");
	
// Create some variables for handling the date
$today = date("Y-m-d h:i:s")."\n";
$this_month = date("m");
$this_year = date("Y");

// Make sure our server time is correct (We use an ISP on the West Coast; you probably don't need this)
putenv("TZ=US/Eastern");

// Include Zend libraries, load the classes we need

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Calendar');
Zend_Loader::loadClass('Zend_Gdata_Query');
Zend_Loader::loadClass('Zend_Http_Client');

// Generate $service and $client vars
$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);  // generate client

// Set start_date to today
$start_date = $today;

// Loop through the next X months (as set at the top of the file), adding each month's data to our SQL

for ($i=1;$i<=$num_months;$i++){

	// Get the day one month hence
	$end_date = date("Y-m-d", mktime(0, 0, 0, date("m")+$i, date("d"), date("Y"))) . " 23:59:59";
	
	// Grab the sql from our calendar function
	$sql .= outputCalendarByDateRange($client, $start_date, $end_date);

	// Set the start date for one day later than the end date
	$start_date = date("Y-m-d", mktime(0, 0, 0, date("m")+$i, date("d")+1, date("Y"))) . " 00:00:01";

}

// remove that final ", " from our string
$sql = substr($sql, 0, -2);

// Start building the query
$intro = "INSERT INTO libhours (ymd, dow, opening, closing, is_closed)
VALUES ";

dbCall($uName, $pWord, $dbName);

$query = $intro.$sql;

$delete_query = "delete from libhours where ymd >= '$today'";

$r = MYSQL_QUERY($delete_query);

print "<p>Attempting Delete Query: <span style=\"background-color: #e0e0e0;\">$delete_query</span><p>";

	if ($r) { print "<p>Result of Delete: <strong>Success</strong></p>";} else { print "<p>Result of Delete: <strong>Failure</strong></p>";}

$r2 = MYSQL_QUERY($query);

// Print out the SQL, for everyone's edification
print "<p>Attempting Insert Query:  <span style=\"background-color: yellow\">$query</span></p>";

	if ($r2) { print "<p>Result of Insert: <strong>Success</strong></p>";} else { print "<p>Result of Insert: <strong>Failure</strong>.  Are you sure you have data in your Google Calendar to be extracted?</p>" ;}

///////////////
/* Functions */
///////////////

// Function to convert our 24 hour time into twelve hour with am/pm, for friendlier display on HTML pages

function miltoampm($hour, $minute="00") {
	$ampm = ($hour >=12 && $hour <24) ? "pm" : "am";
	$newhour = ($hour % 12 === 0) ? 12 : $hour % 12;
		if ($minute != "00") {$newhour .= ":$minute";}
	return $newhour . ' ' . $ampm;
}

// Function to generate the SQL to update our calendar

function outputCalendarByDateRange($client, $startDate, $endDate)
{
	$gdataCal = new Zend_Gdata_Calendar($client);
	$query = $gdataCal->newEventQuery();
	$query->setUser('default'); // we're looking at the default calendar
	$query->setVisibility('private'); // is this a public or private feed?
	$query->setProjection('full'); // Select which projection of the data you want
	$query->setOrderby('starttime'); // Select how you want the results ordered
	$query->setStartMin($startDate);
	$query->setStartMax($endDate);
	$eventFeed = $gdataCal->getCalendarEventFeed($query);

	global $debugger;

	foreach ($eventFeed as $event) {

		foreach ($event->when as $when) {

			// suck out the date
			$start = $when->startTime;

			$start2 = explode("T", $start);
			list($startHour, $startMinute) = split(":", $start2[1]);
			$dmy = explode("-", $start2[0]);
			$day = $dmy[2];
			$month = $dmy[1];
			$year = $dmy[0];
			$end = $when->endTime;
			$end2 = explode("T", $end);
			list($endHour, $endMinute) = split(":", $end2[1]);
			$the_date = $start2[0];
			$day_of_week = date("l",mktime(0,0,0,$month, $day, $year));
			$date[$the_date] = array("$event->title", $day_of_week, $startHour, $startMinute, $endHour, $endMinute, "$event->content");

		}
	}

	// Make sure there is a result
	if ($date != "") {
	
		// Sort the array by the date field (key)
		ksort($date);
		
			if ($debugger == "yes") {print "<pre>"; print_r($date); print "</pre>";}
	
		$i=1;
	
		foreach($date as $date_label => $date_item) {
	
			$event_title = $date_item[0];
			$dow = $date_item[1];
			$startHr = $date_item[2];
			$startMin = $date_item[3];
			$endHr = $date_item[4];
			$endMin = $date_item[5];
			// If you wanted to get the description information, you could uncomment the following line 
			// (and then add a field to the database to store the info + to the sql)
			// $event_content = $date_item[6];
	
				// Look for the word "Closed" in the event title field (case-insensitive) to determine if 
				// the library is closed that day
				
				if (preg_match("/closed/i", $event_title)) {$closed = 1; } else {$closed = 0;}
				
			$newStart = miltoampm($startHr, $startMin);
			$newEnd = miltoampm($endHr, $endMin);

			$sql .= "('$date_label', '$dow', '$newStart', '$newEnd', '$closed'), ";
			$i++;
			
		}

	}
	return $sql;
}

?>


