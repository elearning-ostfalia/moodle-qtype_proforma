#include <CUnit/CUnit.h>
#include <CUnit/Basic.h>

#include "palindrome.h"


void test_init(void) {
    CU_PASS("");
}

void test_rentner(void) {
    if (!is_palidrome("rentner")) {
        CU_FAIL("rentner failed");    
    }
    CU_PASS("rentner pass");    
}

void test_auto(void) {
    if (is_palidrome("auto")) {
        CU_FAIL("auto failed");    
    }
    CU_PASS("auto pass");    
}


int main(int argc, char **argv)
{
    if(CU_initialize_registry() != CUE_SUCCESS) 
        return 1;
    
    CU_basic_set_mode(CU_BRM_VERBOSE);
    
    CU_pSuite testsuite;

    testsuite = CU_add_suite("CUint Testsuite", 0, 0);
    
    CU_add_test(testsuite, "Test init()", test_init);
    CU_add_test(testsuite, "Test rentner()", test_rentner);
    CU_add_test(testsuite, "Test auto()", test_auto);

    CU_ErrorCode success = CU_basic_run_suite(testsuite);
    // CU_cleanup_registry();´   
    
    if (CUE_SUCCESS != success)
        return success;
    
    if (CU_get_number_of_tests_run() == 0) {
        fprintf(stderr, "no tests run\n");
        return 1;
    }
       
    return CU_get_number_of_tests_failed();   
}