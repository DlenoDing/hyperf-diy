#!/bin/sh
#生成runtime文件(分开执行，避免进程内存占用)
php /opt/www/bin/hyperf.php
#启动hyperf
php /opt/www/bin/hyperf.php start