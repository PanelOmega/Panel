#!/bin/bash

CURRENT_IP=$(curl -s ipinfo.io/ip)

echo " \
 ____   _    _   _ _____ _        ___  __  __ _____ ____    _
|  _ \ / \  | \ | | ____| |      / _ \|  \/  | ____/ ___|  / \
| |_) / _ \ |  \| |  _| | |     | | | | |\/| |  _|| |  _  / _ \
|  __/ ___ \| |\  | |___| |___  | |_| | |  | | |__| |_| |/ ___ \
|_| /_/   \_\_| \_|_____|_____|  \___/|_|  |_|_____\____/_/   \_\

 Welcome to Panel Omega!
 OS: Ubuntu 22.04
 You can login at: http://$CURRENT_IP:8443
"

# File can be saved at: /etc/profile.d/greeting.sh
