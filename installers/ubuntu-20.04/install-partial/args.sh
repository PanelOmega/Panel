# Default values for command-line arguments
GIT_BRANCH="stable"

# Function to display usage information
usage() {
    echo "Usage: $0 [-b branch_name]"
    echo "  -b branch_name FOR GIT BRANCH (if not provided, default is: stable)"
    exit 1
}

# Parse command-line arguments
while getopts "b:" opt; do
    case $opt in
        b)
            GIT_BRANCH=$OPTARG
            ;;
        *)
            usage
            ;;
    esac
done

echo "GIT_BRANCH: $GIT_BRANCH"
