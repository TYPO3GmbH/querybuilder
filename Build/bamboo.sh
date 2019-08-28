#!/bin/bash
if [ "$(ps -p "$$" -o comm=)" != "bash" ]; then
    bash "$0" "$@"
    exit "$?"
fi

# fail immediately if some command failed
set -e
# output all commands
set -x

source Build/php_versions.sh
source Build/bamboo_container_functions.sh

# Create log directory
mkdir -p logs

# Array for invalid options
INVALID_OPTIONS=();
TEST_SUITE="functional"
while getopts "p:s:" OPT; do
  case ${OPT} in
        s)
            TEST_SUITE="${OPTARG}"
            ;;
        \?)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
        :)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
    esac
done;

# Exit on invalid options
if [ ${#INVALID_OPTIONS[@]} -ne 0 ]; then
    echo "Invalid option(s):" >&2
    for I in "${INVALID_OPTIONS[@]}"; do
        echo "-${I}" >&2
    done
    exit 1
fi

for TEST_PHP_VERSION in "${PHP_VERSIONS[@]}"; do
    if [ -f composer.lock ]; then
        rm composer.lock
    fi

    # Suite execution
    case ${TEST_SUITE} in
        functional)
            runComposer install --no-interaction --no-progress
            runPhpunit -c Build/phpunit.xml --testsuite \"Functional Test Suite\" --log-junit logs/phpunit.xml  --coverage-clover logs/coverage.xml --coverage-html logs/coverage/
            ;;
        cgl)
            runComposer install --no-interaction --no-progress
            runPhpCsFixer fix --config Build/.php_cs.dist --format=junit > logs/php-cs-fixer-.xml
            ;;
        lint)
            runLint
            ;;
        *)
            echo "Invalid -s option argument ${TEST_SUITE}" >&2
            exit 1
    esac;
done;
