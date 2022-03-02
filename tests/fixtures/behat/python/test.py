# coding=utf-8

import unittest
from palindrome import is_palindrome

# simple python test
class PalindromePositiveTest(unittest.TestCase):

    _result = True

    def test_long(self):
        self.assertEqual(type(self)._result, is_palindrome('Roma tibi subito motibus ibit amor'), 'Roma tibi subito motibus ibit amor')
 
    def test_short(self):
        self.assertEqual(type(self)._result, is_palindrome('otto'), 'otto')    
        self.assertEqual(type(self)._result, is_palindrome('rentner'), 'rentner')  
        self.assertEqual(type(self)._result, is_palindrome('a'), 'a')    
    
    def test_empty(self):
        self.assertEqual(type(self)._result, is_palindrome(''), '<empty>')    
