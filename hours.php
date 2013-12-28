<?php 

/*

Public Display of Library Hours
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

////////////////////////////////
// For the calendar
// script modified from http://www.plus2net.com/php_tutorial/php_calendar.php
////////////////////////////////

// Make sure the user input is numeric and not nasty
if (is_numeric($_GET["prm"]) && is_numeric($_GET["chm"])) {
	$prm = $_GET["prm"];
	$chm = $_GET["chm"];
}

putenv("TZ=US/Eastern");

$d = date("d");     // Finds today's date
$y = date("Y");     // Finds today's year

if(isset($prm) and $prm > 0) {
	$m = $prm+$chm;
} else {
	$m = date("m");
}

$no_of_days = date('t',mktime(0,0,0,$m,1,$y)); // This is to calculate number of days in a month

$mn  = date('F',mktime(0,0,0,$m,1,$y)); // Month is calculated to display at the top of the calendar
$mql = date('m',mktime(0,0,0,$m,1,$y)); // Month is zero padding integer
$yn  = date('Y',mktime(0,0,0,$m,1,$y)); // Year is calculated to display at the top of the calendar
$j   = date('w',mktime(0,0,0,$m,1,$y)); // This will calculate the week day of the first day of the month

//////////////////////////////////

// Connect to database (PDO object in config.php)

try 
{
	$q = "SELECT opening, closing, is_closed, ymd
			FROM `libhours`
			WHERE ymd LIKE :prepared_date
			ORDER BY ymd"; 	
	
	$placeholders = array();
	$placeholders[':prepared_date'] = $yn . '-' . $mql . '-%';
	$s = $pdo->prepare($q);
	$s->execute($placeholders);
}
catch (PDOException $e)
{
	$error = 'Error selecting hours from libhours table: ' . $e->getMessage();
	include 'error.html.php';
	exit();
}

foreach ($s as $myrow)
{
	list($year, $month, $day) = split("-", $myrow[3]);
	
	// Get rid of the leading 0 so that things will match up below
	$day = preg_replace("/^0/", "", $day);
	
	$date_data[$day] = array ($myrow[0], $myrow[1], $myrow[2], $myrow[3]);
}

$calendar = "<div align=\"center\">
<table cellspacing=\"1\" cellpadding=\"3\" align=\"center\" width=\"\" border=\"0\" id=\"hours\" class=\"striped_data\">\n
	<td colspan=\"7\" class=\"hours_header\"><a href=\"hours.php?prm=$m&chm=-1\">&#171;</a> &nbsp;&nbsp; <strong>$mn $yn</strong> &nbsp;&nbsp; <a href='hours.php?prm=$m&chm=1'>&#187;</a>\n
	<br /><br />\n
	</td>\n
</tr>\n
<tr class=\"hours_subheader\">
	<td size=\"14%\">Sunday</td>
	<td size=\"14%\">Monday</td>
	<td size=\"14%\">Tuesday</td>
	<td size=\"14%\">Wednesday</td>
	<td size=\"14%\">Thursday</td>
	<td size=\"14%\">Friday</td>
	<td size=\"14%\">Saturday</td>
</tr>
<tr>\n";

////// End of the top line showing name of the days of the week//////////

////// Adjustment of date starting ///////
for($k=1; $k<=$j; $k++) { 
	$calendar .= "\t<td>&nbsp;</td>\n";
}

//////// Starting of the days//////////
for($i=1;$i<=$no_of_days;$i++){
	$calendar .= "\t<td valign=\"top\" class=\"hoursbox";

	// Add the coloured box for today
	if ($i == $d && $m == date("m")) {
		$calendar .= " hours_today";
	} 
	
	$calendar.= "\">
\t\t<div style=\"color: #efefef; font-size: 18pt; padding: 5px;\">$i</div>\n"; // This will display the date inside the calendar cell

	if ($date_data[$i][2] == 1) {
	// Enter the "Closed" Text
		$calendar .= "\t\tClosed";
	} elseif ($date_data[$i][0] == "") {
	// No data for this day; leave blank
		$calendar .= "\t\t<br /><br />";
	} else {
		$listing = str_replace(" am", "<span style=\"font-size: 9px;\">am</span>", $date_data[$i][0]); 
		$listing = str_replace(" pm", "<span style=\"font-size: 9px;\">pm</span>", $listing); 
		
		$listing2 = str_replace(" am", "<span style=\"font-size: 9px;\">am</span>", $date_data[$i][1]); 
		$listing2 = str_replace(" pm", "<span style=\"font-size: 9px;\">pm</span>", $listing2); 
	
		// Make sure there's a listing for this day (actually, just a closing hour)
		if ($listing2 != "") {
			$calendar.= "\t\t" . $listing . " - " . $listing2; // Library's open
		}	
	}
	
	$calendar.= "\n\t</td>\n";

	$j++;
	if($j==7){
		$calendar.= "</tr>\n<tr>\n";
		$j=0;
	}

}

$calendar .= "</tr>\n</table>\n</div>\n";

// Uncomment the lines below to see an array of the values returned from the DB
/* print "<pre>";
print_r($date_data);
print "</pre>"; 
 */

include "calendar.html.php";
