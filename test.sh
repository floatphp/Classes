#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;96m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}      FloatPHP Classes - All Tests        ${NC}"
echo -e "${BLUE}===========================================${NC}"
echo ""

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Path to the tests directory
TESTS_DIR="$SCRIPT_DIR/tests"

# Change to the tests directory
cd "$TESTS_DIR"

# Initialize test results
DB_UNIT_TESTS_PASSED=0
DB_INTEGRATION_TESTS_PASSED=0
ARCHIVE_TESTS_PASSED=0

echo -e "${YELLOW}Running Database Unit Tests (Simplified)...${NC}"
echo -e "${YELLOW}--------------------------------------------${NC}"
../../../vendor/bin/phpunit --bootstrap ../../../test.php DbSimpleTest.php --testdox

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database unit tests passed!${NC}"
    DB_UNIT_TESTS_PASSED=1
else
    echo -e "${RED}✗ Database unit tests failed!${NC}"
    DB_UNIT_TESTS_PASSED=0
fi

echo ""
echo -e "${YELLOW}Running Database Integration Tests...${NC}"
echo -e "${YELLOW}-------------------------------------${NC}"
../../../vendor/bin/phpunit --bootstrap ../../../test.php DbIntegrationTest.php --testdox

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database integration tests passed!${NC}"
    DB_INTEGRATION_TESTS_PASSED=1
else
    echo -e "${RED}✗ Database integration tests failed!${NC}"
    DB_INTEGRATION_TESTS_PASSED=0
fi

echo ""
echo -e "${YELLOW}Running Archive Tests...${NC}"
echo -e "${YELLOW}------------------------${NC}"
../../../vendor/bin/phpunit --bootstrap ../../../test.php ArchiveTest.php --testdox

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Archive tests passed!${NC}"
    ARCHIVE_TESTS_PASSED=1
else
    echo -e "${RED}✗ Archive tests failed!${NC}"
    ARCHIVE_TESTS_PASSED=0
fi

echo ""
echo -e "${YELLOW}Running All Tests with Coverage...${NC}"
echo -e "${YELLOW}----------------------------------${NC}"
../../../vendor/bin/phpunit --configuration phpunit.xml --coverage-text --testdox

# Final results
echo ""
echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}              TEST SUMMARY                ${NC}"
echo -e "${BLUE}===========================================${NC}"

if [ $DB_UNIT_TESTS_PASSED -eq 1 ]; then
    echo -e "${GREEN}✓ Database Unit Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Database Unit Tests: FAILED${NC}"
fi

if [ $DB_INTEGRATION_TESTS_PASSED -eq 1 ]; then
    echo -e "${GREEN}✓ Database Integration Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Database Integration Tests: FAILED${NC}"
fi

if [ $ARCHIVE_TESTS_PASSED -eq 1 ]; then
    echo -e "${GREEN}✓ Archive Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Archive Tests: FAILED${NC}"
fi

echo ""
echo -e "${BLUE}Test Reports Generated:${NC}"
echo "  📊 coverage-html/ - HTML coverage report"
echo "  📄 test-results.xml - JUnit XML results"
echo "  📋 testdox.html - Test documentation (HTML)"
echo "  📝 testdox.txt - Test documentation (Text)"
echo "  📈 coverage.xml - Clover coverage format"
echo "  📊 coverage.txt - Text coverage summary"

# Exit with appropriate code
if [ $DB_UNIT_TESTS_PASSED -eq 1 ] && [ $DB_INTEGRATION_TESTS_PASSED -eq 1 ] && [ $ARCHIVE_TESTS_PASSED -eq 1 ]; then
    echo ""
    echo -e "${GREEN}🎉 All tests passed successfully!${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}❌ Some tests failed. Please check the output above.${NC}"
    exit 1
fi
