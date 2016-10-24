<?php
###############################
## ResourceSpace
## Local Configuration Script
###############################

# All custom settings should be entered in this file.
# Options may be copied from config.default.php and configured here.

# MySQL database settings
$mysql_server = 'localhost';
$mysql_username = 'root';
$mysql_password = '';
$mysql_db = 'c9';

$mysql_bin_path = '/usr/bin';

# Base URL of the installation
$baseurl = 'http://sandbox-krashnik.c9users.io';

# Email settings
$email_notify = 'Danny.Garcia.Martin@gmail.com';
$email_from = 'Danny.Garcia.Martin@gmail.com';
# Secure keys
$spider_password = 'WAqazE7yPe6y';
$scramble_key = '9e5AVyNUPaTU';
$api_scramble_key = '8yNA5atAMEbE';

# Paths
$ghostscript_path = '/usr/bin';
$exiftool_path = '/usr/local/bin';
$enable_remote_apis = true;
$applicationname = 'Daniel Martin';

#Design Changes
$slimheader=true;



/*

New Installation Defaults
-------------------------

The following configuration options are set for new installations only.
This provides a mechanism for enabling new features for new installations without affecting existing installations (as would occur with changes to config.default.php)

*/
                                
// Set imagemagick default for new installs to expect the newer version with the sRGB bug fixed.
$imagemagick_colorspace = "sRGB";

// No "contact us" link for new installations
$contact_link=false;

$slideshow_big=true;
$home_slideshow_width=1400;
$home_slideshow_height=900;

$homeanim_folder = 'filestore/system/slideshow';
