<?php
require_once('codebird.php');

/*Get this from Twitter apps registration*/
define("KEY", "INSERT YOUR KEY");
define("KEY_SECRET", "INSERT YOUR SECRET KEY");
define("TOKEN", "INSERT YOUR TOKEN");
define("TOKEN_SECRET", "INSERT YOUR SECRET TOKEN");

\Codebird\Codebird::setConsumerKey(KEY, KEY_SECRET);
$cb = \Codebird\Codebird::getInstance();
$cb->setToken(TOKEN, TOKEN_SECRET);
class responses
{
	private $negativeResponses = array("all", "the", "negative", "responses");

	private $positiveResponses = array("all", "the", "positive", "responses", ":)");

	private $almost = array("all", "the", "'almost'", "responses");

	private $justPassed = array("all", "the", "'just passed'", "responses");


	private $holidays = array(); // this array will hold holidays to exclude from payday

	function __construct(){
		$this->holidays = array(
			date('Y-01-01'), // this is new years day
			date('Y-12-25') // this is christmas day
			//Fill the array with all the holidays according to the law
		);
	}

	public function getResp(){ // calculates the date and returns the according response
		// set $fPayDay and $lPayDay with the actual paydays of the month
		$fPayDay = $this->getLastPayDay(date("Y-m-15"));// first payday of month is the 15th
		$lPayDay = $this->getLastPayDay(date("Y-m-t")); // last payday of the month is on the last day of the month

		$now = time(); // or your date as well
		$DF1 = $this->timeToDays($now - strtotime($fPayDay)); // calculate de difference from current date to payday (in days)
		$DF2 = $this->timeToDays($now - strtotime($lPayDay));
		$closer = $this->getCloserDate($DF1, $DF2); //to know which one is closer

		if (in_array(date('Y-m-j'), array($fPayDay, $lPayDay) )) // if today is in the array, we're on a payday
		{
			$selected = $this->positiveResponses;
		}
		else // well, if it isn't we're not.
		{
			if (abs($closer) < 3 && $closer > 0) //if the closest payday is really close and it's a positive number, we just passed the date recently
			{
				$selected = $this->justPassed;
			} else if (abs($closer) < 3 && $closer < 0) //if the closest payday is really close and it's a negative number, we are close
			{
				$selected = $this->almost;
			} else { //if none of those fits we're just far.
				$selected = $this->negativeResponses;
			}
		}
		return $selected[mt_rand(0, count($selected)-1)]; // select one randomly
	}

	function getCloserDate($_D1, $_D2){ // the args are difference in days, so we return whichever is smaller.
		return (abs($_D1) < abs($_D2)) ? $_D1 : $_D2;
	}

	function getWeekday($_date) { // return weekday according to args
    		return date('D', strtotime($_date));
	}

	function getLastPayDay($_date){ //checks in weekends and holidays to know the exact payday
		while ($this->getWeekday($_date) == 'Sat' || $this->getWeekday($_date) == 'Sun' || in_array($_date, $this->holidays)) {
			// if it is a saturday, sunday or a holiday we substract one day from the date.
			$_date = date('Y-m-j', strtotime('-1 day', strtotime($_date)));
		}
		return $_date;
	}

	function timeToDays($_time){ //recives a Time object and returns an Int according to the days it holds.
		return floor($_time / (60 * 60 * 24));
	}
}
$resp = new responses(); // declare the object
$params = array(
	'status' => $resp->getResp() // get the response according to the day in the previewsly defined method
);
$reply = $cb->statuses_update($params); // send the tweet
?>
