<?php
/*
	Copyright © 2011 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP”.
	
	“SimplOn PHP” is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation version 3 of the License.
	
	“SimplOn PHP” is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with “SimplOn PHP”.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace DOF\Datas;

class Email extends String {
	function val($val = null) {
		if(isset($val)) {
			if(self::is_email($val))
				$this->val = $val;
			else
				user_error('Invalid email address received.');
		} else {
			return $this->val;
		}
	}
	
	static function is_email($email) {
		if( (strlen($email) < 5) || !strpos($email, '@', 1) ) {
			return false;
		}

		// Split out the local and domain parts
		list($local, $domain) = explode( '@', $email, 2 );
		if(		!preg_match('/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local) ||
				preg_match('/\.{2,}/', $domain) ||
				trim($domain, " \t\n\r\0\x0B.") !== $domain
			) {
			return false;
		}

		// Split the domain into subs
		$subs = explode( '.', $domain );
		// Assume the domain will have at least two subs
		if (2 > count( $subs )) {
			return false;
		}
		// Loop through each sub
		foreach($subs as $sub) {
			// Test for leading and trailing hyphens and whitespace
			if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub || !preg_match('/^[a-z0-9-]+$/i', $sub ) ) {
				return false;
			}
		}

		// Congratulations your email made it!
		return true;
	}
}