
#include "squareroot.cpp"
#include <gtest/gtest.h>
 
TEST(Test, Positive) { 
    ASSERT_EQ(3, squareroot(9));
    ASSERT_EQ(4, squareroot(16));
    ASSERT_EQ(6, squareroot(36));
    ASSERT_EQ(56, squareroot(3136));
}
 
TEST(Test, Negative) {
    ASSERT_EQ(-1, squareroot(-5));
}

TEST(Test, Zero) {
    ASSERT_EQ(0, squareroot(0));
}
 
int main(int argc, char **argv) {
    testing::InitGoogleTest(&argc, argv);
    return RUN_ALL_TESTS();
}