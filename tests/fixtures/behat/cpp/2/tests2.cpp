
#include "squareroot.cpp"
#include <gtest/gtest.h>
 
TEST(Test, Positive) { 
    ASSERT_EQ(1, squareroot(1));
    ASSERT_EQ(2, squareroot(4));
    ASSERT_EQ(3, squareroot(9));
}
 
 
int main(int argc, char **argv) {
    testing::InitGoogleTest(&argc, argv);
    return RUN_ALL_TESTS();
}