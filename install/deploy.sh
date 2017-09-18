#!/bin/bash

rsync -avze ssh --exclude install --exclude *~ --exclude .git ../ gustav@192.168.1.220:/var/www/html/mahjong/

