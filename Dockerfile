# Default Dockerfile
#
# @link     https://www.hyperf.io
# @document https://hyperf.wiki
# @contact  group@hyperf.io
# @license  https://github.com/hyperf/hyperf/blob/master/LICENSE

FROM registry.cn-hangzhou.aliyuncs.com/dleno-server/php:alp321-php84-sw6.1.0
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="Hyperf"

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone
ARG APP_ENV

ENV APP_ENV=${APP_ENV:-"local"}
ENV TIMEZONE=${timezone:-"Asia/Shanghai"}
ENV SCAN_CACHEABLE=(true)

COPY . /opt/www

# update
RUN set -ex \
    # show php version and extensions
    && php -v \
    && php -m \
    && php --ri swoole \
    #  ---------- some config ----------
    && cd /etc/php* \
    # - config PHP
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && cd /opt/www \
    #创建系统日志文件夹
    && mkdir -p /opt/www/runtime \
    #日志目录权限开放
    && chmod 0777 /opt/www/runtime -R \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

WORKDIR /opt/www

# Composer Cache
# COPY ./composer.* /opt/www/
# RUN composer install --no-dev --no-scripts

RUN  cd /opt/www \
     #&& composer config -g repos.packagist composer https://mirrors.cloud.tencent.com/composer/ \
     #&& composer config -g repos.packagist composer https://mirrors.aliyun.com/composer/ \
     #&& composer config -g repos.packagist composer https://packagist.org \
     && composer install --no-dev -o && php bin/hyperf.php

EXPOSE 9504 9505

ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]

#热重载
#php bin/hyperf.php server:watch
#强制结束所有进程
#kill -9 $(ps -ef|grep Server|grep -v grep|awk '{print $2}')


