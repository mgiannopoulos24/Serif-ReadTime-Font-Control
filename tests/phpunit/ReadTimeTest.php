<?php
/**
 * Tests for Serif\ReadTime\Read_Time.
 *
 * @package Serif_ReadTime_Font_Control
 */

use PHPUnit\Framework\TestCase;
use Serif\ReadTime\Read_Time;

final class ReadTimeTest extends TestCase {

	private static function words( int $n, string $word = 'word' ): string {
		return implode( ' ', array_fill( 0, $n, $word ) );
	}

	public function test_short_text_is_under_a_minute(): void {
		$this->assertSame( 2, Read_Time::count_words( 'Hello world' ) );
		$this->assertSame( 0, Read_Time::minutes( 'Hello world' ) );
	}

	public function test_minutes_round_up(): void {
		$this->assertSame( 2, Read_Time::minutes( self::words( 400 ) ) );
		$this->assertSame( 3, Read_Time::minutes( self::words( 401 ) ) );
		$this->assertSame( 0, Read_Time::minutes( self::words( 199 ) ) );
		$this->assertSame( 1, Read_Time::minutes( self::words( 200 ) ) );
	}

	/**
	 * @dataProvider empty_content
	 */
	public function test_empty_content_has_no_words( string $content ): void {
		$this->assertSame( 0, Read_Time::count_words( $content ) );
		$this->assertSame( 0, Read_Time::minutes( $content ) );
	}

	public static function empty_content(): array {
		return array(
			'empty string'        => array( '' ),
			'whitespace'          => array( "  \n\t " ),
			'only block comments' => array( '<!-- wp:spacer {"height":"40px"} --><!-- /wp:spacer -->' ),
		);
	}

	public function test_shortcode_tags_are_stripped_but_enclosed_text_kept(): void {
		$content = '[note color="red"]one two three[/note] four [divider]';
		$this->assertSame( 'one two three four', Read_Time::to_text( $content ) );
		$this->assertSame( 4, Read_Time::count_words( $content ) );
	}

	public function test_media_shortcodes_are_removed_with_their_content(): void {
		$content = 'before [caption id="attachment_1" align="alignnone"]<img src="a.jpg" alt="ignored"> A caption here[/caption] after [gallery ids="1,2"] end';
		$this->assertSame( 'before after end', Read_Time::to_text( $content ) );
	}

	public function test_block_markup_delimiters_are_not_counted(): void {
		$content = "<!-- wp:paragraph -->\n<p>one two three</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading {\"level\":2} -->\n<h2>four</h2>\n<!-- /wp:heading -->";
		$this->assertSame( 'one two three four', Read_Time::to_text( $content ) );
		$this->assertSame( 4, Read_Time::count_words( $content ) );
	}

	public function test_non_prose_elements_are_excluded(): void {
		$content = '<p>one two</p><figure><img src="a.jpg" alt="not counted words"><figcaption>skip this caption</figcaption></figure>'
			. '<script>var x = "no words";</script><style>.a { color: red }</style><noscript>hidden</noscript><p>three</p>';
		$this->assertSame( 'one two three', Read_Time::to_text( $content ) );
	}

	public function test_html_entities_are_decoded(): void {
		$content = '<p>fish &amp; chips &nbsp; &lt;tag&gt;</p>';
		$this->assertSame( 'fish & chips <tag>', Read_Time::to_text( $content ) );
		$this->assertSame( 3, Read_Time::count_words( $content ) );
	}

	public function test_greek_words_are_counted(): void {
		$this->assertSame( 5, Read_Time::count_words( 'Η γρήγορη καφέ αλεπού πηδάει' ) );
	}

	public function test_hyphenated_and_apostrophe_words_count_once(): void {
		$this->assertSame( 3, Read_Time::count_words( "well-known don't it’s" ) );
	}

	public function test_numbers_count_as_words(): void {
		$this->assertSame( 3, Read_Time::count_words( 'in 2024 alone' ) );
	}

	public function test_custom_words_per_minute(): void {
		$content = self::words( 400 );
		$this->assertSame( 2, Read_Time::minutes( $content, 200 ) );
		$this->assertSame( 4, Read_Time::minutes( $content, 100 ) );
	}

	public function test_non_positive_wpm_is_treated_as_one(): void {
		$content = self::words( 7 );
		$this->assertSame( 7, Read_Time::minutes( $content, 0 ) );
		$this->assertSame( 7, Read_Time::minutes( $content, -5 ) );
	}
}
