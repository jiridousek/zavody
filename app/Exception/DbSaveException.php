<?php

/**
 * DbSaveException
 * 
 * Obecná výjimka při špatném uložení do databáze
 *
 * @author Jiří Doušek <405245@mail.mini.cz>
 */
class DbSaveException extends \Exception {
	
	public function __construct($message, $prev) {
		parent::__construct($message, 0, $prev);
	}
}
