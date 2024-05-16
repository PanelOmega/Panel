services:
  nginx_{{$containerNameLower}}:
    container_name: {{$containerName}}
    image: nginx:latest
    ports:
      - "{{$externalPort}}:80"
    environment:
      - NGINX_HOST={{$containerName}}
      - NGINX_PORT=80
