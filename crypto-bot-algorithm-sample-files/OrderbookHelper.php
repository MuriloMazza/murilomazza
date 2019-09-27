<?php

/**
 * Helpful functions regsrding Orderbook
 * @author Mumazza <mumazza@gmail.com>
 */

class OrderbookHelper
{
	protected $analyzedOrderbook;
	
	public function __construct($analyzedOrderbook) {
		$this->analyzedOrderbook = $analyzedOrderbook;
	}


    /*
     * Approximate a given $rate to a good point nearby.
     *
     * $rate: the current rate you have. If we can't find a good point nearby this rate, the function returns null.
     * $type: "buy" or "sell" (must inform for what operation you are using the function)
     * $percentageRange: for a given $rate, inform the percentage range you allow the good point finding. Beside the percentage
     * range informed, any good point more distant than the range will be ignored. If $percentageRange was informed as null,
     * look for good points without limits.
     *  i.e.: if you inform "20", will look for 20% below the current rate, and 20% above the current rate. 
     */
    function approximateRateToAGoodPoint($rate, $type="buy", $percentageRange=null) {
		
		$keyNameTopRate = $type."-top-rate";
		
		//exception if key is not set:
		if (!isset($this->analyzedOrderbook["general-analysis"][$keyNameTopRate]) || is_null($this->analyzedOrderbook["general-analysis"][$keyNameTopRate])) {
			return null;
		}
		$currentBookRate = $this->analyzedOrderbook["general-analysis"][$keyNameTopRate];

		$keyNameGoodPoints = $type."-good-points";
		$goodPoints = $this->analyzedOrderbook["walls"][$keyNameGoodPoints];
		
        $orderRateDifference = abs(abs($currentBookRate) - abs($rate));
		$ratePercentageRange = $orderRateDifference * ($percentageRange/100);

        $goodPointBestDifference = null;
        
        foreach ($goodPoints as $goodPoint) {
            //if ($goodPoint["info"] != "first from orderbook") {
                $goodPointDifference = abs(abs($rate) - abs($goodPoint["rate"]));
                if (is_null($goodPointBestDifference) || $goodPointDifference < $goodPointBestDifference) {
                    
                    if (is_null($percentageRange) || $goodPointDifference < $ratePercentageRange) {
                        $goodPointBestDifference = $goodPointDifference;
                        $bestRate = $goodPoint["rate"];
                    }
                }
            //}
        }

        if (isset($bestRate)) {
            return $bestRate;
        }
        return null;
    }
}

