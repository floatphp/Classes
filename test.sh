#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;96m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}      FloatPHP Classes Tests               ${NC}"
echo -e "${BLUE}===========================================${NC}"
echo ""

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

TESTS_DIR="$SCRIPT_DIR/tests"

if [ ! -d "$TESTS_DIR" ]; then
    echo -e "${RED}Tests directory not found: $TESTS_DIR${NC}"
    exit 1
fi

cd "$TESTS_DIR"

echo -e "${YELLOW}Running all FloatPHP Classes tests in: $TESTS_DIR${NC}"
echo -e "${YELLOW}=====================================================${NC}"

PHPUNIT_PATH="/c/laragon/www/skeleton.dev/App/vendor/bin/phpunit"

$PHPUNIT_PATH --testdox .

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}🎉 All FloatPHP Classes tests passed successfully!${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}❌ Some tests failed. Please check the output above.${NC}"
    exit 1
fi
