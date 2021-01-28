package de.ostfalia.proforma.test.reverse_package_task;

import static org.junit.jupiter.api.Assertions.assertEquals;

import java.util.Arrays;
import java.util.List;
import org.junit.jupiter.api.Disabled;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.DynamicTest;
import org.junit.jupiter.api.RepeatedTest;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestFactory;
import org.junit.jupiter.params.ParameterizedTest;
import org.junit.jupiter.params.provider.CsvSource;
import org.junit.jupiter.api.TestMethodOrder;
import org.junit.jupiter.api.MethodOrderer;



@TestMethodOrder(MethodOrderer.Alphanumeric.class)
public class FlipTestJUnit5 {
		
	@Test
	@DisplayName(value = "tests input with odd number of characters: hallo")
	public void testOddNumberOfCharacters() {
		assertEquals("ollah", MyString.flip("hallo"));
	}

	@Test
	public void testEvenNumberOfCharacters() {
		assertEquals("4321", MyString.flip("1234"));
	}
	
	
	@Test
	public void testEmptyString() {
		assertEquals("", MyString.flip(""));
	}
	
	@Test
	public void testFailing() {
		assertEquals("abc", MyString.flip("123"));
	}	
	
	@Test
	public void testThrowException() {
		assertEquals(1, 7/0);
	}		
	

	@TestFactory
	List<DynamicTest> testFactory() {
		return Arrays.asList(
			DynamicTest.dynamicTest(
				"123456",
				() -> { assertEquals("654321", MyString.flip("123456")); }),
			DynamicTest.dynamicTest(
				"aibohphobia",
				() -> { assertEquals("aibohphobia", MyString.flip("aibohphobia")); })
		);
	}	
	

	@ParameterizedTest(name = "{0} => {1}")
	@CsvSource({
	  "eins, snie",
	  "one, eno",
	  "anna, anna"
	})
	void list(String input, String expectedResult) { 
		assertEquals(expectedResult, MyString.flip(input)); 
	}
	
	@RepeatedTest(3)
	void repeatedTest() {
		assertEquals("A654321", MyString.flip("123456A"));
	}	
	
	@Disabled
	public void testDoNotExecute() {
		assertEquals("", MyString.flip(""));
	}
}
