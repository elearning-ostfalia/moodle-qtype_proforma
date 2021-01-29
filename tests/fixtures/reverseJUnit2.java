package de.ostfalia.proforma.test.reverse_package_task;

import static org.junit.jupiter.api.Assertions.assertEquals;

import java.util.Arrays;
import java.util.List;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.DynamicTest;
import org.junit.jupiter.api.RepeatedTest;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestMethodOrder;
import org.junit.jupiter.api.MethodOrderer;



@TestMethodOrder(MethodOrderer.Alphanumeric.class)
public class FlipTestJUnit5 {
		
	@Test
	@DisplayName(value = "tests input with odd number of characters: hallo")
	public void testOddNumberOfCharacters() {
		assertEquals("ollah", MyString.flip("hallo"));
	}
}
