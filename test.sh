#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;96m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}      FloatPHP Classes Tests               ${NC}"
echo -e "${BLUE}===========================================${NC}"
echo ""

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Path to the tests directory
TESTS_DIR="$SCRIPT_DIR/tests"

# Check if tests directory exists
if [ ! -d "$TESTS_DIR" ]; then
    echo -e "${RED}Tests directory not found: $TESTS_DIR${NC}"
    exit 1
fi

# Change to the tests directory
cd "$TESTS_DIR"

echo -e "${YELLOW}Running all FloatPHP Classes tests in: $TESTS_DIR${NC}"
echo -e "${YELLOW}=====================================================${NC}"

# Use absolute path to PHPUnit since floatphp is a symbolic link
# Navigate up to find the main project vendor/bin/phpunit
PHPUNIT_PATH="/c/laragon/www/skeleton.dev/App/vendor/bin/phpunit"

# Run all tests in the current directory
$PHPUNIT_PATH --testdox .

# Check if tests passed
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}🎉 All FloatPHP Classes tests passed successfully!${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}❌ Some tests failed. Please check the output above.${NC}"
    exit 1
fi
