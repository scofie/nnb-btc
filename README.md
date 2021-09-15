# nnb-btc
#### 启动k线等服务的计划任务
```
启动es
cd/bin
./elasticreserach -d
cd elasticsearch/bin
su es&& ./elasticsearch -d
cd  public/vendor/webmsgsender
php start.php start -d
php artisan websocket:client restart
```
#### php扩展
```
[Zend Opcache]
zend_extension=opcache.so
opcache.enable = 1
opcache.memory_consumption=128
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=80000
opcache.revalidate_freq=3
opcache.fast_shutdown=1
opcache.enable_cli=1

[redis]
extension=redis.so
extension=fileinfo.so

[ImageMagick]
extension="imagick.so"
extension=imap.so
extension=exif.so
;extension=intl.so
extension=xsl.so
```
#### nginx配置
```
server
{
    listen 80;
	  listen 443 ssl http2;
    server_name www.nnbltd.com;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/nnb-btc/public;
    
    #SSL-START SSL相关配置，请勿删除或修改下一行带注释的404规则
    #error_page 404/404.html;
    #HTTP_TO_HTTPS_START
    if ($server_port !~ 443){
        rewrite ^(/.*)$ https://$host$1 permanent;
    }
    #HTTP_TO_HTTPS_END
    ssl_certificate    /nginx/cert/nnbltd.pem;
    ssl_certificate_key    /nginx/cert/nnbltd.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    add_header Strict-Transport-Security "max-age=31536000";
    error_page 497  https://$host$request_uri;

    #SSL-END
    
    #ERROR-PAGE-START  错误页配置，可以注释、删除或修改
    #error_page 404 /404.html;
    #error_page 502 /502.html;
    #ERROR-PAGE-END
    
    #PHP-INFO-START  PHP引用配置，可以注释或修改
    include enable-php-72.conf;
    #PHP-INFO-END
    
    #REWRITE-START URL重写规则引用,修改后将导致面板设置的伪静态规则失效
    location / {  
	    try_files $uri $uri/ /index.php$is_args$query_string;  
    } 
    #REWRITE-END
    
    #禁止访问的文件或目录
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.svn|\.project|LICENSE|README.md)
    {
        return 404;
    }
    
    #一键申请SSL证书验证目录相关设置
    location ~ \.well-known{
        allow all;
    }
    
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
        expires      30d;
        error_log /dev/null;
        access_log off;
    }
    
    location ~ .*\.(js|css)?$ {
        expires      12h;
        error_log /dev/null;
        access_log off; 
    }
    access_log  skc.bmexcoin.vip.log;
    error_log  skc.bmexcoin.vip.error.log;
}
```


