<?php
/*

Configuration File for Library Hours with Google API
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


// You will need to enter your own values below

// Number of months to harvest
$num_months = 6;

// Gmail username and password
$user = "";
$pass = "";

// Database connection //

// MySQL hostname
$hName = "localhost"; 
	
// MySQL username and password
$uName = ""; 
$pWord = "";

// Database name
$dbName = "";

// Uncomment the debugger variable if you wish to see what's happening in gdata_connect.php
// $debugger = "yes";

// Make sure we can find the Zend library, if it's not in the includes folder
// This path is not correct!!!
set_include_path('/path/to/zend');


/////////////////////////////////////
// Function to connect to MySQL
// You do not need to edit this . . .
/////////////////////////////////////

function dbCall($uName, $pWord, $dbName){

global $hName;
 
// make connection to database 
MYSQL_CONNECT($hName, $uName, $pWord) OR DIE("<p>Unable to connect to database <strong>$hName</strong> with username=$uName and password=$pWord.</p>");

@mysql_select_db("$dbName") or die( "<p>Unable to select database <strong>$dbName</strong></p>"); 

}	
?>