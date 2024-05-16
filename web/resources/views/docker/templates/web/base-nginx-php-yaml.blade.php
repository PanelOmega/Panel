services:
    nginx-{{$containerNameLower}}:
        image: nginx:latest
        ports:
            - "{{$externalPort}}:80"
        environment:
            - NGINX_HOST={{$containerName}}
            - NGINX_PORT=80
        volumes:
            - ./src:/var/www/html
            - ./default.conf:/etc/nginx/conf.d/default.conf
        links:
            - php-fpm-{{$containerNameLower}}

    php-fpm-{{$containerNameLower}}:
        image: php:8-fpm
        volumes:
          - ./src:/var/www/html
