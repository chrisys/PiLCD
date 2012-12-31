<?php

// HD44780 PHP Raspberry Pi class
// Requires pickley's GPIO class https://github.com/pickley/PHP-GPIO

// Author: Chris Crocker-White
// Date: 31/12/2012

// Loosely based upon Matt Hawkins Python script (http://www.raspberry-spy.co.uk)

require_once('GPIO.php');

define('LCD_RS',7);
define('LCD_E',8);
define('LCD_D4',25);
define('LCD_D5',24);
define('LCD_D6',23);
define('LCD_D7',18);

define('LCD_WIDTH',20);
define('LCD_CHR',1);
define('LCD_CMD',0);

define('LCD_LINE_1',0x80);
define('LCD_LINE_2',0xC0);

define('E_PULSE',0.00005);
define('E_DELAY',0.00005);

class PiLCD {
  function __construct()
	{
		$this->GPIO = new GPIO;

		// setup the inputs/outputs
		$outputs = array(LCD_E,LCD_RS,LCD_D4,LCD_D5,LCD_D6,LCD_D7);
		
		foreach($outputs as $output_pin)
		{
			$this->GPIO->setup($output_pin,'out');
		}
	}

	function init()
	{
		// initialise the LCD
		$this->byte(0x33,LCD_CMD);
		$this->byte(0x32,LCD_CMD);
		$this->byte(0x28,LCD_CMD);
		$this->byte(0x0C,LCD_CMD);
		$this->byte(0x06,LCD_CMD);
		$this->byte(0x01,LCD_CMD);
	}

	function string($message,$style)
	{
		switch($style)
		{
			case 1:
				$message = str_pad($message, LCD_WIDTH, ' ', STR_PAD_LEFT);
			break;
			case 2:
				$message = str_pad($message, LCD_WIDTH, ' ', STR_PAD_BOTH);
			break;
			case 3:
				$message = str_pad($message, LCD_WIDTH, ' ', STR_PAD_RIGHT);
			break;
		}

		for($i=0;$i<strlen($message);$i++)
		{
			$this->byte(ord(substr($message,$i,1)),LCD_CHR);
		}
	}

	function byte($bits,$mode)
	{
		// mode = 1 for character, 0 for command
		$this->GPIO->output(LCD_RS,$mode);

		// set all the data lines low
		$this->reset_data_lines();

		if(($bits & 0x10) == 0x10)
			$this->GPIO->output(LCD_D4,1);

		if(($bits & 0x20) == 0x20)
			$this->GPIO->output(LCD_D5,1);

		if(($bits & 0x40) == 0x40)
			$this->GPIO->output(LCD_D6,1);

		if(($bits & 0x80) == 0x80)
			$this->GPIO->output(LCD_D7,1);

		// send this nibble
		$this->toggle_enable();

		$this->reset_data_lines();

		if(($bits & 0x01) == 0x01)
			$this->GPIO->output(LCD_D4,1);

		if(($bits & 0x02) == 0x02)
			$this->GPIO->output(LCD_D5,1);

		if(($bits & 0x04) == 0x04)
			$this->GPIO->output(LCD_D6,1);

		if(($bits & 0x08) == 0x08)
			$this->GPIO->output(LCD_D7,1);

		// send this nibble
		$this->toggle_enable();
	}

	function toggle_enable()
	{
		sleep(E_DELAY);
		$this->GPIO->output(LCD_E,1);
		sleep(E_PULSE);
		$this->GPIO->output(LCD_E,0);
		sleep(E_DELAY);
	}

	function reset_data_lines()
	{
		$data_lines = array(LCD_D4,LCD_D5,LCD_D6,LCD_D7);

		foreach($data_lines as $data_line)
		{
			$this->GPIO->output($data_line,0);
		}		
	}
}

?>
