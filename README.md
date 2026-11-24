## agMSU Agweather website

This is the site hosted on https://agweather.geo.msu.edu.  This repository is the 
'built' version and is ready to deploy. The source code to build this site is in 
the repository https://github.com/enviroweather/agweather (for now)

Most pages are basic HTML, CSS, and Javascript and do not require a javascript 
server (aka node).   However the forecasts pages requires a PHP proxy server which 
is a single script (`api/forecast-proxy.php`).  It's basic PHP and does not
require any modules (no compose needed), and is tested with PHP 8.2.something

Copy (or pull) these files into a folder in a PHP-enabled webserver


