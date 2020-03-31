package de.ostfalia.proforma.sample;

public class MyString 
{
	static public String reverse( String aString)
	{	
		StringBuilder sb = new StringBuilder();
		
		for (int i = 0; i < aString.length(); i++)
			sb.append(aString.charAt(aString.length()-1-i));

		return sb.toString();
	}
}

