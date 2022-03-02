cmake_minimum_required(VERSION 3.14)
project(DEMO)

# GoogleTest requires at least C++11
set(CMAKE_CXX_STANDARD 11)

find_package(GTest REQUIRED)
include_directories(${GTEST_INCLUDE_DIRS})
 
add_executable(demo tests.cpp)
target_link_libraries(demo ${GTEST_LIBRARIES} pthread)