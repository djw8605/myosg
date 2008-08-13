#!/bin/bash

urlbase="/trunk"

#setup directory structure
chmod o+w logs
chmod o+w cache

#install .htaccess file
cat >> ../.htaccess << EOF
RewriteEngine on
RewriteRule !\.(txt|js|ico|gif|jpg|png|css)$ index.php
RewriteBase $urlbase
EOF

#TODO - setup db

#TODO - install cron
