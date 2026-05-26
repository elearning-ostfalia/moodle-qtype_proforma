public class MyString {

	static public Boolean isPalindrom(String aString) {
		String reverse = new StringBuilder(aString).reverse().toString();
		return (aString.equalsIgnoreCase(reverse));
		// return false;
	}
}