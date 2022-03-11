FROM registry.cn-hangzhou.aliyuncs.com/dleno-server/php:alp3.12-php7.4-sw4.8.7
LABEL maintainer="dleno <dleno@126.com>" version="2.0"

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai APP_ENV=prod
ARG timezone
ARG APP_ENV

#环境信息
ENV APP_ENV=${APP_ENV:-"local"}
ENV TIMEZONE=${timezone:-"Asia/Shanghai"}

COPY . /opt/www

#设置最大句柄数
#RUN echo $(grep MemTotal /proc/meminfo |awk '{printf("%d",$2/2)}') > /proc/sys/fs/file-max \
#    && echo $(grep MemTotal /proc/meminfo |awk '{printf("%d",$2/2/4)}') > /proc/sys/fs/nr_open

#composer安装项目依赖
RUN cd /opt/www \
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
#    && composer config -g repo.packagist composer https://packagist.org \
    && composer install --no-dev -o

# update
RUN ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone

#php配置文件，其他文件根据需要增加
RUN cd /opt/www \
#    && mv -f /opt/www/docker/php/conf.d/99-overrides.ini /etc/php7/conf.d/99-overrides.ini \
    #创建系统日志文件夹
	&& mkdir -p /opt/www/runtime \
	#日志目录权限开放
	&& chmod 0777 /opt/www/runtime -R


WORKDIR /opt/www


EXPOSE 9504 9505

ENTRYPOINT ["sh", "/opt/www/docker/docker-entrypoint.sh"]

#热重载
#php bin/hyperf.php server:watch
#强制结束所有进程
#kill -9 $(ps -ef|grep Server|grep -v grep|awk '{print $2}')