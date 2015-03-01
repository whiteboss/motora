<?php
class Qlick_Validate_Phone extends Zend_Validate_Abstract
{
	const NOT_VALID = 'phoneInvalid';

	protected $_messageTemplates = array(
		self::NOT_VALID => "Телефонный номер '%value%' содержит недопустимые символы (разрешено только цифры, пробелы и +)"
	);

	public function isValid($value)
	{
		$this->_setValue($value);

		//if (!preg_match('/\b[+]?[-0-9\(\) ]{6,20}\b/',$value)) {
                //if (!preg_match('/(\+56)(\s+)[2-9]+(\s+)\d{7,8}/',$value)) { 
                if (!preg_match('/^[0-9\s\+]+$/is',$value)) { // чтоб только осталось на цифры, +, пробелы
			$this->_error(self::NOT_VALID);
			return false;
		}

		return true;
	}
}
