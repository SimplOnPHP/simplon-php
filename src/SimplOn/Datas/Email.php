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
namespace SimplOn\Datas;
/**
 * Email data type
 * 
 * This is an email data type which allow you create an input to introduce a correct 
 * email and might validate it. 
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Email extends String {
    /**
     *
     * @var string $validationRequired - is a message if the input field it's empty
     * @var string $validationEmail - is a message if the email introduced is invalid
     */
    var 
        $validationRequired = 'Email address is a required field!',
	$validationEmail = 'Invalid email address received!';
        /**
         * 
         * function val - This function verifies if the value declareded is an email, 
         * if isn't throw an exception and if it's return the email.
         * 
         * @param string $val
         * @return string
         * @throws \SimplOn\DataValidationException
         */
	
	function val($val = null) {
            // if $val is defined and isn't null, start to verify the value
            if(isset($val)) {
                //remove the empty spaces in the variable
                $val = trim($val);
                //check if val is empty and also is required if it's true throw an exception
		if(!$val && $this->required) {
			throw new \SimplOn\DataValidationException($this->validationRequired);
		}
                // if $val isn't empty and is required check if it's a correct email
                else if(self::is_email($val)) {
			$this->val = $val;
                }
                //if it's not a correct email throw an exception
                else {
                    throw new \SimplOn\DataValidationException($this->validationEmail);
		}
            }
            //if $val is null return the same variable
            else {
            	return $this->val;
            }
	}
	/**
         * function showInput - This function prints the label and the input with the 
         * correct format (id,class, name) to be used in the forms.
         * 
         * @param boolean $fill
         * @return string
         */
	public function showInput($fill) {
		return 
            ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label><br>' : '') .
            '<input id="'.$this->htmlId().'" class="'.$this->htmlClasses('email').'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text"/>';
	}
	/**
         * 
         * this function is to validate the email, if it's a correct a email return true 
         * else return false
         * 
         * @param string $email
         * @return boolean
         */
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