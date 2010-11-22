<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Cleanup
{

	/**
	 * Cleanup patterns
	 *
	 * @class $Control: Compression Controller
	 * @param (regex) rsemi: Checks for last semit colon in details
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (regex) rurl: Matches url definition
	 * @param (array) escaped: Contains patterns and replacements for espaced characters
	 */
	private $Control;
	private $rsemi = "/;$/";
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rurl = "/url\((.*?)\)/";
	private $escaped = array(
		'patterns'=> array( "\\:", "\\;", "\\ " ),
		'replacements' => array( ':', ';', ' ' )
	);

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
	}

	/**
	 * Central cleanup process, removes all injections
	 *
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 * @param (boolean) simple: If true, keeps injections
	 */
	public function cleanup( $selectors, $details, $simple = false ) {
		foreach ( $details as &$value ) {
			$value = $this->removeMultipleDefinitions( $value );
			$value = $this->removeUnnecessarySemicolon( $value );
			if ( $simple === false ) {
				$value = $this->removeEscapedURLs( $value );
			}
		}

		return array( $selectors, $details );
	}

	/**
	 * Removes multiple definitions that were created during compression
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function removeMultipleDefinitions( $val = '' ) {
		$storage = array();
		$arr = preg_split( $this->rsemicolon, $val );

		foreach ( $arr as $x ) {
			if ( $x ) {
				list( $a, $b ) = preg_split( $this->rcolon, $x, 2 );
				$storage[ $a ] = $b;
			}
		}

		if ( $storage ) {
			$val = '';
			foreach ( $storage as $x => $y ) {
				$val .= "$x:$y;";
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Removes '\' from possible splitter characters in URLs
	 *
	 * @params none
	 */ 
	private function removeEscapedURLs($str){
		preg_match_all( $this->rurl, $str, $matches, PREG_OFFSET_CAPTURE );

		for ( $i = 0, $imax = count( $matches[0] ); $i < $imax; $i++ ) {
			$value = 'url(' . str_replace( $this->escaped['patterns'], $this->escaped['replacements'], $matches[1][$i][0] ) . ')';
			$str = substr_replace( $str, $value, $matches[0][$i][1], strlen( $matches[0][$i][0] ) );
		}

		// Return unescaped string
		return $str;
	}

	/**
	 * Removes last semicolons on the final property of a set
	 *
	 * @params none
	 */ 
	private function removeUnnecessarySemicolon( $value ) {
		return preg_replace( $this->rsemi, '', $value );
	}

	/**
	 * Access to private methods for testing
	 *
	 * @param (string) method: Method to be called
	 * @param (array) args: Array of paramters to be passed in
	 */
	public function access( $method, $args ) {
		if ( method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}
		else {
			throw new Exception( "Unknown method in Color Class - " . $method );
		}
	}
};

?>
