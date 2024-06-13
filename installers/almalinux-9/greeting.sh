#!/bin/bash

CURRENT_IP=$(hostname -I | awk '{print $1}')

echo "
 Welcome to Panel Omega!
 OS: AlmaLinux 9
 You can login at: http://$CURRENT_IP:8443
"

# File can be saved at: /etc/profile.d/greeting.sh
