#!/bin/bash

cp /etc/mysql/conf.d/source/* /etc/mysql/conf.d/

/entrypoint.sh mysqld
