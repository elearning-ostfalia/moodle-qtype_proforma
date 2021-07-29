#include <stdio.h>
#include <string.h>
#include "palindrome.h"

char *strrev(char *str)
{
      char *p1, *p2;

      if (! str || ! *str)
            return str;
      for (p1 = str, p2 = str + strlen(str) - 1; p2 > p1; ++p1, --p2)
      {
            *p1 ^= *p2;
            *p2 ^= *p1;
            *p1 ^= *p2;
      }
      return str;
}

int is_palidrome(const char *input) {
    char newstring[100]; // should be allocated...
    strcpy(newstring, input);    
    strrev(newstring);
    return (strcmp(input, newstring) == 0);    
}
    
