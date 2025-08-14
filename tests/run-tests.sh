#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;96m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}   FloatPHP Classes - Database Tests      ${NC}"
echo -e "${BLUE}===========================================${NC}"
echo ""

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Change to the tests directory
cd "$SCRIPT_DIR"

# Initialize test results
TOTAL_TESTS_PASSED=0
TOTAL_TESTS_FAILED=0

echo -e "${YELLOW}Running Unit Tests (Simplified)...${NC}"
echo -e "${YELLOW}------------------------------------${NC}"
../../../vendor/bin/phpunit --bootstrap ../../../test.php DbSimpleTest.php --testdox

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Unit tests passed!${NC}"
    UNIT_TESTS_PASSED=1
else
    echo -e "${RED}✗ Unit tests failed!${NC}"
    UNIT_TESTS_PASSED=0
fi

echo ""
echo -e "${YELLOW}Running Integration Tests...${NC}"
echo -e "${YELLOW}-----------------------------${NC}"
../../../vendor/bin/phpunit --bootstrap ../../../test.php DbIntegrationTest.php --testdox

# Check if tests passed
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Integration tests passed!${NC}"
    INTEGRATION_TESTS_PASSED=1
else
    echo -e "${RED}✗ Integration tests failed!${NC}"
    INTEGRATION_TESTS_PASSED=0
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

if [ $UNIT_TESTS_PASSED -eq 1 ]; then
    echo -e "${GREEN}✓ Unit Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Unit Tests: FAILED${NC}"
fi

if [ $INTEGRATION_TESTS_PASSED -eq 1 ]; then
    echo -e "${GREEN}✓ Integration Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Integration Tests: FAILED${NC}"
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
if [ $UNIT_TESTS_PASSED -eq 1 ] && [ $INTEGRATION_TESTS_PASSED -eq 1 ]; then
    echo ""
    echo -e "${GREEN}🎉 All tests passed successfully!${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}❌ Some tests failed. Please check the output above.${NC}"
    exit 1
fi
