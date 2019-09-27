<?php

require_once __DIR__.'/db/OrderExecutedDAO.php';

/**
 * Class for Sell When Increases Strategy (when increased a determined percentage value)
 * @author Mumazza <mumazza@gmail.com>
 */

class SellWhenIncreasesStrategy
{
	protected $percentageThreshold;
	
	public function __construct($percentageThreshold) {
		$this->percentageThreshold = $percentageThreshold;
	}

    public function getSellRateThreshold($marketName) {
		$orderExecutedDAO = new OrderExecutedDAO();

        $buyOrderRate = $orderExecutedDAO->getRateFromRecentBuyOrders($marketName);
        if (!is_null($buyOrderRate) && $buyOrderRate > 0) {
            return btcFormat($buyOrderRate * $this->percentageThreshold);   
        }
        return null;
    }

	public function isIntendedSellRateOverThreshold($intendedSellRate, $marketName) {
		$sellRateThreshold = $this->getSellRateThreshold($marketName);
		if (is_null($sellRateThreshold)) {
			return false;
		}
		if ($intendedSellRate > $sellRateThreshold) {
			return true;
		}
		return false;
	}
}

