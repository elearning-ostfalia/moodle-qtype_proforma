cmake_minimum_required(VERSION 3.16)

project(palindrome)

add_executable(palindrome_test main.c palindrome.c)
target_link_libraries(palindrome_test cunit)