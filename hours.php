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

$d= date("d");     // Finds today's date
$y= date("Y");     // Finds today's year

	if(isset($prm) and $prm > 0){
		$m=$prm+$chm;
	}else{
		$m= date("m");
	}

$no_of_days = date('t',mktime(0,0,0,$m,1,$y)); // This is to calculate number of days in a month

$mn=date('F',mktime(0,0,0,$m,1,$y)); // Month is calculated to display at the top of the calendar
$mql = date('m',mktime(0,0,0,$m,1,$y));
$yn=date('Y',mktime(0,0,0,$m,1,$y)); // Year is calculated to display at the top of the calendar
$j= date('w',mktime(0,0,0,$m,1,$y)); // This will calculate the week day of the first day of the month

	for($k=1; $k<=$j; $k++){ // Adjustment of date starting
	$adj .="<td>&nbsp;</td>";
	}

//////////////////////////////////

// Connect to database (function in config.php)

dbCall($uName, $pWord, $dbName);

$q = "SELECT opening, closing, is_closed, ymd
FROM `libhours`
WHERE ymd LIKE '$yn-$mql-%'
ORDER BY ymd";

$r = MYSQL_QUERY($q);

	while($myrow =  mysql_fetch_array($r))
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
<tr class=\"hours_subheader\">\n
	<td size=\"14%\">Sunday</td>\n
	<td size=\"14%\">Monday</td>\n
	<td size=\"14%\">Tuesday</td>\n
	<td size=\"14%\">Wednesday</td>\n
	<td size=\"14%\">Thursday</td>\n
	<td size=\"14%\">Friday</td>\n
	<td size=\"14%\">Saturday</td>\n
</tr>\n
<tr>\n";

////// End of the top line showing name of the days of the week//////////

//////// Starting of the days//////////
for($i=1;$i<=$no_of_days;$i++){
$calendar .= $adj . "<td valign=\"top\" class=\"hoursbox";

	// Add the coloured box for today
	if ($i == $d && $m == date("m")) {
	$calendar .= " hours_today";
	} 
	
$calendar.= "\">
<div style=\"color: #efefef; font-size: 18pt; padding: 5px;\">$i</div>"; // This will display the date inside the calendar cell

	if ($date_data[$i][2] == 1) {
	// Enter the "Closed" Text
	$calendar .= "Closed";
	} elseif ($date_data[$i][0] == "") {
	// No data for this day; leave blank
	$calendar .= "<br /><br />";
	} else {

	$listing = str_replace(" am", "<span style=\"font-size: 9px;\">am</span>", $date_data[$i][0]); 
	$listing = str_replace(" pm", "<span style=\"font-size: 9px;\">pm</span>", $listing); 
	
	$listing2 = str_replace(" am", "<span style=\"font-size: 9px;\">am</span>", $date_data[$i][1]); 
	$listing2 = str_replace(" pm", "<span style=\"font-size: 9px;\">pm</span>", $listing2); 
	
		// Make sure there's a listing for this day (actually, just a closing hour)
		if ($listing2 != "") {
		$calendar.= $listing . " - " . $listing2; // Library's open
		}
	
	}
	
$calendar.= "</td>\n";

$adj="";
$j++;
	if($j==7){$calendar.= "</tr>\n<tr>";
$j=0;}

}

$calendar.= "</tr>\n
</table>\n
</div>";

// Uncomment the lines below to see an array of the values returned from the DB
/* print "<pre>";
print_r($date_data);
print "</pre>"; 
 */


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Library Hours</title>
<style type="text/css" media="all">
#hours {
position:relative;
background-color: #fff;
border: 1px solid #e0e0e0;
font-family: Verdana, Arial, sans-serif;
font-size: 13px;
}	

.hoursbox {
background-color: #CFDAE6;
}	

.hours_header{
background-color: #fff;
text-align:center;
color: #000;
font-size: larger;
}	

.hours_subheader{
background-color: #8798AC;
text-align:center;
color: #fff;
}	

.hours_today {
background-color: #333;
color: #fff;
}
</style>
</head>
<body>

<?php print $calendar; ?>
<br />

</body>
</html>


