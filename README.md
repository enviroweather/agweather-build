<!-- edit this file in /static/README.md -->
## agMSU Agweather website


This is the site hosted on https://agweather.geo.msu.edu.  This repository is the 
'built' version and is ready to deploy. The source code to build this site is in 
the repository https://github.com/enviroweather/agweather.  

**Do not edit this code, it will be overwritten.**

This repository is created by a 
github workflow from the other siite.  See the repo above for directions 
on editing the site and how to build it.  

The pages are HTML, CSS, and Javascript and do not require a javascript 
server (aka node).   However the forecast pages require a PHP proxy server 
to pull forecast data file from another GEO website.  This is a single 
script (`api/forecast-proxy.php`).  It's basic PHP and does not
require any modules (no compose needed), and is tested with PHP 8.2.something
Hence it can't be hosted by most 'static' websites (like github pages, etc), but 
can by many shared hosting systems.  

### Try it out

Copy (or pull) these files into a folder in any PHP-enabled webserver.   

To test or see what the pages look like on a machine with PHP installed  ( 
    [Windows](https://www.php.net/manual/en/install.windows.php) or 
    [MacOS](https://www.php.net/manual/en/install.macosx.packages.php)   
)

**mac/linux:**

```bash
mkdir agweather
cd agweather
git clone  https://github.com/enviroweather/agweather-build .
php -S localhost:8000  # change 8000 to any port that's open
```
---

![website screenshot](agweather_screenshot.jpg)


