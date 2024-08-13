[{{$poolName}}]

; Unix user/group of processes
user = {{$username}}
group = {{$username}}

; The address on which to accept FastCGI requests
listen = 127.0.0.1:{{$port}}

; Set permissions for Unix socket
listen.owner = {{$username}}
listen.group = {{$username}}
listen.mode = 0660

; Process manager settings
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 2
pm.max_spare_servers = 5
