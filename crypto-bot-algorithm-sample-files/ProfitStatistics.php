<?php

require_once __DIR__.'/db/OrderExecutedDAO.php';
require_once __DIR__.'/src/mumazza/coinmarketcap/CoinMarketCapApi.php';

/**
 * Class for calculating Profit/Loss statistics.
 * @author Mumazza <mumazza@gmail.com>
 */

class ProfitStatistics
{
	const _tradePercentageWithCommissionDiscount = 0.9975; //every trade has a 0.25% comission.
	
	protected $statisticsData;
	
	public function __construct()
	{
	}
		
    /*
    * Calculate the profit/loss statistics and return an array containing the results.
    */
    public function calculateProfitStatistics($dateStart, $dateEnd) {
		
		$returnArray = array();
		$returnArray["botProfitImpact"] = 0;
		$returnArray["marketsTokensRemainingNull"] = array();
		$returnArray["marketsPositiveTokensRemaining"] = array();
		$returnArray["marketsNegativeTokensRemaining"] = array();
		$returnArray["marketsNegativeTokensRemainingButTracked"] = array();
		
		/*
		 * Get tickers from api.coinmarketcap.com:
		 */
		$coinMarketCapApi = new CoinMarketCapApi();
		
		$coinMarketCapTickers = null;
		while (is_null($coinMarketCapTickers)) {
			$coinMarketCapTickers = $coinMarketCapApi->ticker();
			//correct (replace) values from $coinMarketCapTickers if necessary, to match all market names correctly:
			$coinMarketCapTickers = replacementInArray($coinMarketCapTickers, "symbol", $_SESSION["settings"]->coinmarketcap->marketNameReplacement);
		}
		
		$coinMarketCapApi->setTickers($coinMarketCapTickers);
		$returnArray["bitcoinPriceInUsd"] = $coinMarketCapApi->getBitcoinUsdPrice();

		//get executer orders from database, for the period selected:
		$orderExecutedDAO = new OrderExecutedDAO();
		$returnArray["executedOrders"] = $orderExecutedDAO->getExecutedOrdersFromPeriodDates($dateStart, $dateEnd);

		//calculate some "count" and "sum" values for the executed orders:
		$returnArray["totalOfBuyOrders"] = 0;
		$returnArray["totalOfSellOrders"] = 0;
		$returnArray["totafPriceFromAllBuyOrders"] = 0;
		$returnArray["totafPriceFromAllSellOrders"] = 0;
		$returnArray["totalCommission"] = 0;
		foreach ($returnArray["executedOrders"] as $executedOrder) {
			if ($executedOrder->orderType == "LIMIT_BUY") {
				$returnArray["totalOfBuyOrders"]++;
				$returnArray["totafPriceFromAllBuyOrders"] += $executedOrder->price;
			} elseif ($executedOrder->orderType == "LIMIT_SELL") {
				$returnArray["totalOfSellOrders"]++;
				$returnArray["totafPriceFromAllSellOrders"] += $executedOrder->price;
			}
			$returnArray["totalCommission"] += $executedOrder->commission;
		}
		$returnArray["generalRawProfit"] = btcFormat($returnArray["totafPriceFromAllSellOrders"] - $returnArray["totafPriceFromAllBuyOrders"] - $returnArray["totalCommission"]);
		
		$marketsTokensRemainingNull = array();
		$marketsPositiveTokensRemaining = array();
		$marketsNegativeTokensRemaining = array();
		$marketsNegativeTokensRemainingButTracked = array();
		
		$currentMarket = null;
		$marketChanged;
		$countBuyQuantity;
		$countBuyFinalPrice;
		$countSellQuantity;
		$countSellFinalPrice;
		$countCommission = null;
		$i=0;
		
		//estimate profits for each market:
		foreach ($returnArray["executedOrders"] as $executedOrder) {
			
			if ($currentMarket != $executedOrder->marketName) {
				
				$marketChanged = true;
				
				if (!is_null($currentMarket)) {
					
					$profitInBtc = $countSellFinalPrice - $countBuyFinalPrice - $countCommission;
					$quantityRemaining = $countBuyQuantity - $countSellQuantity;
					
					if ($quantityRemaining == 0) {
						array_push($marketsTokensRemainingNull, ["marketName" => $currentMarket, "profit" => btcFormat($profitInBtc), "tokensRemaining" => $quantityRemaining]);
					} elseif ($quantityRemaining > 0) {
						$tokenPrice = $coinMarketCapApi->getCurrencyBtcPrice($currentMarket);
						$estimatedValueFromRemainingTokens = ($quantityRemaining * $tokenPrice) * self::_tradePercentageWithCommissionDiscount;
						$profitInBtc +=$estimatedValueFromRemainingTokens; //add $estimatedValueFromRemainingTokens to profit final calc.
						
						array_push($marketsPositiveTokensRemaining, ["marketName" => $currentMarket, "profit" => btcFormat($profitInBtc), "tokensRemaining" => $quantityRemaining]);
					} elseif ($quantityRemaining < 0) {
						$tokenPrice = $coinMarketCapApi->getCurrencyBtcPrice($currentMarket);
						$mostRecentExecutedOrder = $orderExecutedDAO->getMostRecentExecutedOrder($currentMarket, "LIMIT_BUY", $dateStart);
						if (!is_null($mostRecentExecutedOrder)) {
							$estimatedBoughtValue = btcFormat((abs($quantityRemaining) * $mostRecentExecutedOrder->rate));
							$profitInBtc = $profitInBtc - $estimatedBoughtValue; //subtract $estimatedBoughtValue to profit final calc.
							array_push($marketsNegativeTokensRemainingButTracked, ["marketName" => $currentMarket, "profit" => btcFormat($profitInBtc), "tokensRemaining" => $quantityRemaining]);
						} else {
							$estimatedValueFromRemainingTokens = ($quantityRemaining * $tokenPrice);
							$profitInBtc += $estimatedValueFromRemainingTokens; //add $estimatedValueFromRemainingTokens to profit final calc.
							array_push($marketsNegativeTokensRemaining, ["marketName" => $currentMarket, "profit" => btcFormat($profitInBtc), "tokensRemaining" => $quantityRemaining]);
						}
					}
				} 
				
				$currentMarket = $executedOrder->marketName;
				$countBuyQuantity = 0;
				$countBuyFinalPrice = 0;
				$countSellQuantity = 0;
				$countSellFinalPrice = 0;
				$countCommission = 0;
			} else {
				$marketChanged = false;
			}
			
			if ($executedOrder->orderType == "LIMIT_BUY") {
				$countBuyQuantity += ($executedOrder->quantity - $executedOrder->quantityRemaining);
				$countBuyFinalPrice += $executedOrder->price;
			}
			elseif ($executedOrder->orderType == "LIMIT_SELL") {
				$countSellQuantity += ($executedOrder->quantity - $executedOrder->quantityRemaining);
				$countSellFinalPrice += $executedOrder->price;
			}
			$countCommission += $executedOrder->commission;
			
			$i++;
		}
		
		if (!empty($marketsTokensRemainingNull)) {
			$returnArray["marketsTokensRemainingNull"]["profitSum"] = 0;
			foreach ($marketsTokensRemainingNull as $market) {
				$returnArray["marketsTokensRemainingNull"][$market["marketName"]] = ["profit" => btcFormat($market["profit"]), "tokensRemaining" => $market["tokensRemaining"]];
				$returnArray["marketsTokensRemainingNull"]["profitSum"] += $market["profit"];
			}
			$returnArray["botProfitImpact"] += btcFormat($returnArray["marketsTokensRemainingNull"]["profitSum"]);
		}

		if (!empty($marketsPositiveTokensRemaining)) {
			$returnArray["marketsPositiveTokensRemaining"]["profitSum"] = 0;
			foreach ($marketsPositiveTokensRemaining as $market) {
				$returnArray["marketsPositiveTokensRemaining"][$market["marketName"]] = ["profit" => btcFormat($market["profit"]), "tokensRemaining" => $market["tokensRemaining"]];
				$returnArray["marketsPositiveTokensRemaining"]["profitSum"] += $market["profit"];
			}
			$returnArray["botProfitImpact"] += btcFormat($returnArray["marketsPositiveTokensRemaining"]["profitSum"]);
		}

		if (!empty($marketsNegativeTokensRemainingButTracked)) {
			$returnArray["marketsNegativeTokensRemainingButTracked"]["profitSum"] = 0;
			foreach ($marketsNegativeTokensRemainingButTracked as $market) {
				$returnArray["marketsNegativeTokensRemainingButTracked"][$market["marketName"]] = ["profit" => btcFormat($market["profit"]), "tokensRemaining" => $market["tokensRemaining"]];
				$returnArray["marketsNegativeTokensRemainingButTracked"]["profitSum"] += $market["profit"];
			}
			$returnArray["botProfitImpact"] += btcFormat($returnArray["marketsNegativeTokensRemainingButTracked"]["profitSum"]);
		}
		
		if (!empty($marketsNegativeTokensRemaining)) {
			$returnArray["marketsNegativeTokensRemaining"]["profitSum"] = 0;
			foreach ($marketsNegativeTokensRemaining as $market) {
				$returnArray["marketsNegativeTokensRemaining"][$market["marketName"]] = ["profit" => btcFormat($market["profit"]), "tokensRemaining" => $market["tokensRemaining"]];
				$returnArray["marketsNegativeTokensRemaining"]["profitSum"] += $market["profit"];
			}
			$returnArray["botProfitImpact"] += btcFormat($returnArray["marketsNegativeTokensRemaining"]["profitSum"]);
		}

		$this->statisticsData = $returnArray;
		return $returnArray;
	}
  
	public function getPrintedResult($printText=true, $printEachMarketDetails=true, $printExecutedOrdersTable=true) {
		$data = $this->statisticsData;
		
		/*
		echo "<pre>";
		print($this->statisticsData);
		echo "</pre>";
		exit();
		*/
		
		$textOutput = "";

		if ($printText) {
			$textOutput .= $data["totalOfBuyOrders"]." buy orders in this period.<br />";
			$textOutput .= $data["totalOfSellOrders"]." sell orders in this period.<br />";
			$textOutput .= "Total price from all buy orders: ".btcFormat($data["totafPriceFromAllBuyOrders"])." BTC (".number_format($data["totafPriceFromAllBuyOrders"]*$data["bitcoinPriceInUsd"], 2)." USD)<br />";
			$textOutput .= "Total price from all sell orders: ".btcFormat($data["totafPriceFromAllSellOrders"])." BTC (".number_format($data["totafPriceFromAllSellOrders"]*$data["bitcoinPriceInUsd"], 2)." USD)<br />";
			$textOutput .= "Total of comission spent: ".btcFormat($data["totalCommission"])." BTC (".number_format($data["totalCommission"]*$data["bitcoinPriceInUsd"], 2)." USD)<br />";
			$textOutput .= "GENERAL RAW PROFIT: ".btcFormat($data["generalRawProfit"])." BTC (".number_format($data["generalRawProfit"]*$data["bitcoinPriceInUsd"], 2)." USD)<br />";  
			$textOutput .= "<br />";
		}

		if ($printEachMarketDetails) {
			if (!empty($data["marketsTokensRemainingNull"])) {
				$textOutput .= "Markets that we bought and sold everything back:<br />";
				// print resume for market
				foreach ($data["marketsTokensRemainingNull"] as $key => $value) {
					if (is_array($value)) {
						$textOutput .= "[".$key."]: profit: ".btcFormat($value["profit"])." BTC ; quantity of tokens remaining: " .$value["tokensRemaining"]."<br />";
					}
				}
				$textOutput .= "Profit from this list: ";
				$textOutput .= btcFormat($data["marketsTokensRemainingNull"]["profitSum"])." BTC (".number_format($data["marketsTokensRemainingNull"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is real)<br /><br />";
			}
		} elseif ($printText) {
			$textOutput .= "Profit from Markets that we bought and sold everything back: ";
			$textOutput .= $data["marketsTokensRemainingNull"]["profitSum"]." BTC (".number_format($data["marketsTokensRemainingNull"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is real)<br />";
		}

		if ($printEachMarketDetails) {
			if (!empty($data["marketsPositiveTokensRemaining"])) {
				$textOutput .= "Markets which we have tokens remaining:<br />";
				// print resume for market
				foreach ($data["marketsPositiveTokensRemaining"] as $key => $value) {
					if (is_array($value)) {
						$textOutput .= "[".$key."]: estimated profit: ".btcFormat($value["profit"])." BTC ; quantity of tokens remaining: " .number_format($value["tokensRemaining"], 2)."<br />";
					}
				}
				$textOutput .= "Profit from this list: ";
				$textOutput .= btcFormat($data["marketsPositiveTokensRemaining"]["profitSum"])." BTC (".number_format($data["marketsPositiveTokensRemaining"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is estimated if we would sell all these tokens now...)<br /><br />";
			}
		} elseif ($printText) {
			$textOutput .= "Profit from Markets which we have tokens remaining: ";
			$textOutput .= btcFormat($data["marketsPositiveTokensRemaining"]["profitSum"])." BTC (".number_format($data["marketsPositiveTokensRemaining"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is estimated if we would sell all these tokens now...)<br />";
		}

		if ($printEachMarketDetails) {
			if (!empty($data["marketsNegativeTokensRemainingButTracked"])) {
				$textOutput .= "Markets that we tracked we sold, and we have tracked we bought them before:<br />";
				// print resume for market
				foreach ($data["marketsNegativeTokensRemainingButTracked"] as $key => $value) {
					if (is_array($value)) {
						$textOutput .= "[".$key."]: estimated profit: ".btcFormat($value["profit"])." BTC ; quantity of tokens sold: " .number_format($value["tokensRemaining"], 2)."<br />";
					}
				}
				$textOutput .= "Profit from this list: ";
				$textOutput .= btcFormat($data["marketsNegativeTokensRemainingButTracked"]["profitSum"])." BTC (".number_format($data["marketsNegativeTokensRemainingButTracked"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is estimated considering with base in the value we previously bought)<br /><br />";
			}
		} elseif ($printText) {
			$textOutput .= "Profit from Markets that we tracked we sold, and we have tracked we bought them before:";
			$textOutput .= btcFormat($data["marketsNegativeTokensRemainingButTracked"]["profitSum"])." BTC (".number_format($data["marketsNegativeTokensRemainingButTracked"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is estimated considering with base in the value we previously bought)<br /><br />";
		}
		
		if ($printEachMarketDetails) {
			if (!empty($data["marketsNegativeTokensRemaining"])) {
				$textOutput .= "Markets that we tracked we sold, but we don't have the track we bought them, so the 'tokens remaining' are negative:<br />";
				// print resume for market
				foreach ($data["marketsNegativeTokensRemaining"] as $key => $value) {
					if (is_array($value)) {
						$textOutput .= "[".$key."]: estimated profit: ".btcFormat($value["profit"])." BTC ; quantity of tokens remaining: " .number_format($value["tokensRemaining"], 2)."<br />";
					}
				}
				$textOutput .= "Profit from this list: ";
				$textOutput .= btcFormat($data["marketsNegativeTokensRemaining"]["profitSum"])." BTC (".number_format($data["marketsNegativeTokensRemaining"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is estimated considering if we would need to buy all these tokens again now... BUT WE WON'T)<br /><br />";
			}
		} elseif ($printText) {
			$textOutput .= "Profit from Markets that we tracked we sold, but we don't have the track we bought them, so the 'tokens remaining' are negative:";
			$textOutput .= btcFormat($data["marketsNegativeTokensRemaining"]["profitSum"])." BTC (".number_format($data["marketsNegativeTokensRemaining"]["profitSum"]*$data["bitcoinPriceInUsd"], 2)." USD) (note: profit is estimated considering if we would need to buy all these tokens again now... BUT WE WON'T)<br /><br />";
		}
		
		if ($printText) {
			if ($data["botProfitImpact"] >= 0) {
				$textOutput .= "<span style='color: DarkGreen;'>The estimate is that the bot ON helped us earning ".btcFormat($data["botProfitImpact"])." BTC (".number_format($data["botProfitImpact"]*$data["bitcoinPriceInUsd"], 2)." USD) during this period.</span>";
			} else {
				$textOutput .= "<span style='color: Red;'>The estimate is that the bot ON made us lose ".btcFormat(abs($data["botProfitImpact"]))." BTC (".number_format($data["botProfitImpact"]*$data["bitcoinPriceInUsd"], 2)." USD) during this period.</span>";
			}
			$textOutput .= '<br /><br />';
		}

		//Table:
		if ($printExecutedOrdersTable) {
			$textOutput .= 'Table of all executed orders during the period: 
				<br /><br />
				<table class="table table-orders-executed">
					<thead>
					  <tr>
						<th></th>
						<th>Market</th>
						<th>Order Type</th>
						<th>Rate</th>
						<th>Quantity</th>
						<th>Order Price (BTC)</th>
						<th>Datetime Closed</th>
						<th>Commission (BTC)</th>
					  </tr>
					</thead>
					<tbody>';
				
			$i=0;
			foreach ($data["executedOrders"] as $executedOrder) {
				$textOutput .=  '<tr>';
					$textOutput .=  '<td>#'.($i+1).'</td>';
					$textOutput .=  '<td>'.$executedOrder->marketName.'</td>';
					$textOutput .=  '<td>'.$executedOrder->orderType.'</td>';
					$textOutput .=  '<td>'.$executedOrder->rate.'</td>';
					$textOutput .=  '<td>'.$executedOrder->quantity.'</td>';
					$textOutput .=  '<td>'.$executedOrder->price.'</td>';
					$textOutput .=  '<td>'.$executedOrder->datetimeClosed.'</td>';
					$textOutput .=  '<td>'.$executedOrder->commission.'</td>';
				$textOutput .=  '</tr>';
				$i++;
			}
			$textOutput .= '</tbody>
						</table>';
		}
		
		return $textOutput;
	}
  
}

