#!/bin/bash

docker run -ti --rm -u $UID -v `pwd`:/var/www/ -p 8080:8080 gatphp php -S 0.0.0.0:8080 -t /var/www/public