#!/bin/bash

# Initialize an empty JSON array
json="["

# Iterate through all arguments
for (( i=2; i<=$#; i++ )); do
    # Append each argument as a JSON string
    json+="\"${!i}\""
    # If it's not the last argument, add a comma
    if [ $i -lt $# ]; then
        json+=","
    fi
done

# Close the JSON array
json+="]"
encodedOptions=$(echo "$json" | base64)

# log all requests
#mkdir -p /var/log/omega
#echo "Request: omega-php /usr/local/omega/web/artisan omega:cloud-linux-api --request $1 --encoded-options $encodedOptions" >> /var/log/omega/cloudlinux-api.log

omega-php /usr/local/omega/web/artisan omega:cloud-linux-api --request $1 --encoded-options "$encodedOptions"
