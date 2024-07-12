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

#jsonEscaped=$(echo $json | jq -cM '. | @text ')

omega-shell omega:cloud-linux-api --request $1 --json-options $json
