<?php

/**
 * Class for Pumping-or-Bleeding Strategy
 * @author Mumazza <mumazza@gmail.com>
 */

class PumpingOrBleedingStrategy
{
	protected $globalAltCoinsChangeForLastDay;
	
	public function __construct()
	{
	}
		
	/*
   * Get the alt coins change (pump or bleed) for the last day (it checks only changes inside Bittrex market)
   * $baseMarket: it is optional. Get general or for "BTC", "ETH" or "USDT"...
   */
  public function calculateGlobalAltCoinsChangeForLastDay($baseMarket = null) {
	
	$returnArray = array();
	
	$marketHistoryDAO = new MarketHistoryDAO();
    $marketList = $marketHistoryDAO->getMarketListFromMarketHistory($baseMarket);
	
    $pumpSinceLastDaySum = 0;
    
	$marketListCount = 0;
    foreach($marketList as $key => $value) {
        $marketHistoryRecord = $marketHistoryDAO->getMostRecentMarketHistoryRecord($value);
		/*
		echo "<pre>";
		print_r($marketHistoryRecord);
		echo "</pre>";
		exit();
		*/
		if (isset($marketHistoryRecord->pumpSinceLastDay)) {
			$pumpSinceLastDaySum += $marketHistoryRecord->pumpSinceLastDay;
			$marketListCount++;
		}   
    }
	
	if ($marketListCount == 0) {
		$returnArray["globalAltCoinsChangeForLastDay"] = null;
		$returnArray["dataAvailable"] = false;
	} else {
		$this->globalAltCoinsChangeForLastDay = round($pumpSinceLastDaySum/$marketListCount, 2);
		$returnArray["globalAltCoinsChangeForLastDay"] = $this->globalAltCoinsChangeForLastDay;
		$returnArray["dataAvailable"] = true;
	}
	
	return $returnArray;
  }
  
  public function didAltcoinsPump() {
	if (isset($this->globalAltCoinsChangeForLastDay) && is_numeric($this->globalAltCoinsChangeForLastDay) &&
		$this->globalAltCoinsChangeForLastDay > $_SESSION["settings"]->strategy->pumpingOrBleeding->pumpThreshold) {
		//Since the last day, altcoins PUMPED!
		return true;
	}
	
	return false;
  }
  
  public function didAltcoinsBleed() {
	if (isset($this->globalAltCoinsChangeForLastDay) && is_numeric($this->globalAltCoinsChangeForLastDay) &&
		$this->globalAltCoinsChangeForLastDay < $_SESSION["settings"]->strategy->pumpingOrBleeding->bleedThreshold) {
		//Since the last day, altcoins BLEEDED!
		return true;
	}
	
	return false;
  }
  
}

