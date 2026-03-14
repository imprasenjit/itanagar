<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Megamillions extends CI_Controller {

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{	
		$counter_rk=0;
		$data = $this->getData("https://www.megamillions.com/cmspages/utilservice.asmx/GetLatestDrawData");
		$website_id = 2;
		$drawing_array = array();
		$drawingData = (!empty($data['Drawing'])) ? $data['Drawing'] : "";
		if(!empty($drawingData)){
			$drawing_array['latest_date'] = ($drawingData['PlayDate']) ? current(explode("T", $drawingData['PlayDate'])) : "";
			$drawing_array['whiteball1'] = ($drawingData['N1']) ? $drawingData['N1'] : 0;
			$drawing_array['whiteball2'] = ($drawingData['N2']) ? $drawingData['N2'] : 0;
			$drawing_array['whiteball3'] = ($drawingData['N3']) ? $drawingData['N3'] : 0;
			$drawing_array['whiteball4'] = ($drawingData['N4']) ? $drawingData['N4'] : 0;
			$drawing_array['whiteball5'] = ($drawingData['N5']) ? $drawingData['N5'] : 0;
			$drawing_array['megaball'] = ($drawingData['MBall']) ? $drawingData['MBall'] : 0;
			$drawing_array['megaball1'] = 0;
		}
		$JackpotData = (!empty($data['Jackpot'])) ? $data['Jackpot'] : "";
		if(!empty($JackpotData)){
			$drawing_array['latest_price'] = ($JackpotData['CurrentPrizePool']) ? $this->convert_in_dollar($JackpotData['CurrentPrizePool']) : 0;
			$drawing_array['latest_cash_value'] = ($JackpotData['CurrentCashValue']) ? $this->convert_in_dollar($JackpotData['CurrentCashValue']) : 0;
			$drawing_array['next_price'] = ($JackpotData['NextPrizePool']) ? $this->convert_in_dollar($JackpotData['NextPrizePool']) : 0;
			$drawing_array['next_cash_value'] = ($JackpotData['NextCashValue']) ? $this->convert_in_dollar($JackpotData['NextCashValue']) : 0;
		}
		$drawing_array['next_date'] = (!empty($data['NextDrawingDate'])) ? current(explode("T", $data['NextDrawingDate'])) : "";
		$this->load->model('Drawing_model');
		$existsdata = $this->Drawing_model->get_drawing_data(array('website_id' => $website_id,'latest_date' => $drawing_array['latest_date']));
		if(empty($existsdata)){
			$this->Drawing_model->insert_entry($website_id,$drawing_array);
			if(!empty($data['PrizeMatrix']['PrizeTiers'])){
				foreach ($data['PrizeMatrix']['PrizeTiers'] as $key => $value) {
					$history_array = array();
					$history_array['unique_id'] =  (!empty($value['MatrixRowId'])) ? $value['MatrixRowId'] : 0;
					$history_array['latest_date'] =  $drawing_array['latest_date'];
					$history_array['whiteball'] =  (!empty($value['TierWhiteBall'])) ? $value['TierWhiteBall'] : 0;
					$history_array['megaball'] =  (!empty($value['TierMegaBall'])) ? $value['TierMegaBall'] : 0;
					$history_array['is_jackpot'] =  (!empty($value['IsJackpot'])) ? $value['IsJackpot'] : 0;
					$history_array['price_amount'] =  (!empty($value['PrizeAmount'])) ? $this->convert_in_dollar($value['PrizeAmount']) : 0;

					$this->load->model('Winner_history_model');
					$this->Winner_history_model->insert_entry($website_id,$history_array);
					// $exists_h_data = $this->Winner_history_model->get_winner_history_data(array('unique_id' => $history_array['unique_id']));
					// if(!empty($exists_h_data)){
					// 	$id = (!empty($exists_h_data[0]->id)) ? $exists_h_data[0]->id : 0;
					// 	$this->Winner_history_model->update_entry($website_id,$history_array,$id);
					// }else{
					// 	$this->Winner_history_model->insert_entry($website_id,$history_array);
					// }
				}

				$counter_rk++;
			}
		}else{
			// $id = (!empty($existsdata[0]->id)) ? $existsdata[0]->id : 0;
			// $this->Drawing_model->update_entry($website,$drawing_array,$id);
		}

		if($counter_rk!=0){
			$this->winner_script($website_id);
		}
	}
	/**
	* scrap powerball data
	**/
	public function powerball(){

		$counter_rk=0;
		$data = $this->getData("https://www.powerball.com/api/v1/estimates/powerball?_format=json");
		$website_id = 1;
		$drawing_array = array();
		if(!empty($data)){
			$drawing_array['next_date'] = (!empty($data[0]['field_next_draw_date'])) ? current(explode("T", $data[0]['field_next_draw_date'])) : "";
			$drawing_array['next_price'] = (!empty($data[0]['field_prize_amount'])) ? $data[0]['field_prize_amount'] : "";
			$drawing_array['next_cash_value'] = (!empty($data[0]['field_prize_amount_cash'])) ? $data[0]['field_prize_amount_cash'] : "";
		}
		$rdata = $this->getData("https://www.powerball.com/api/v1/numbers/powerball/recent?_format=json");
		if(!empty($rdata)){
			if(!empty($rdata[0]['field_winning_numbers'])){
				$numb_array =  explode(",", $rdata[0]['field_winning_numbers']);
				for ($i=0; $i < count($numb_array); $i++) {
					$index = $i+1;
					if($index == count($numb_array)){
						$drawing_array['megaball'] = $numb_array[$i];
					}else{
						$drawing_array['whiteball'.$index] = $numb_array[$i];
					}
				}
			}
			$drawing_array['latest_date'] = (!empty($rdata[0]['field_draw_date'])) ? $rdata[0]['field_draw_date'] : "";
			$drawing_array['megaball1'] = 0;
			$drawing_array['latest_price'] = NULL;
			$drawing_array['latest_cash_value'] = NULL;
		}
		$this->load->model('Drawing_model');
		$existsdata = $this->Drawing_model->get_drawing_data(array('website_id' => $website_id,'latest_date' => $drawing_array['latest_date']));
		if(empty($existsdata)){
			$this->Drawing_model->insert_entry($website_id,$drawing_array);
			
			$historydata = $this->getData("https://www.powerball.com/themes/rapid/images/prizes-powerball.json");
			if(!empty($historydata['body'])){
				foreach ($historydata['body'] as $key => $value) {
					$history_array = array();
					$history_array['unique_id'] =  NULL;
					$history_array['latest_date'] =  $drawing_array['latest_date'];
					$history_array['whiteball'] =  (!empty($value[0]['matches'])) ? $value[0]['matches'] : 0;
					$history_array['megaball'] =  (!empty($value[0]['special'])) ? $value[0]['special'] : 0;
					$history_array['price_amount'] =  (!empty($value[1])) ? $value[1] : "";
					$history_array['is_jackpot'] =  (stristr($history_array['price_amount'], "Grand Prize")) ? 1 : 0;
					$this->load->model('Winner_history_model');
					$this->Winner_history_model->insert_entry($website_id,$history_array);

				$counter_rk++;
				}
			}
		}

		if($counter_rk!=0){
			$this->winner_script($website_id);
		}
	}
	/**
	* scrap eurojackpot data
	**/
	public function eurojackpot(){

		$counter_rk=0;
		$website_id = 6;
		$drawing_array = array();
		$data = $this->getpageData("https://www.eurojackpot.org/en/results/");
		if(!empty($data)){
			$contentData = $this->get_content($data,'<h3>EuroJackpot</h3>','</table>');
			$latestData = $this->get_content($data,'<h1 class="results-h1">EuroJackpot Results</h1>','<div class="right-column">');
			$latest_grab_date = $this->get_text($data,'<div class="calendar">','</div>');
			$latestDate = current(explode(" ", $latest_grab_date));
			$lDateArray = explode(".", $latestDate);
			if(!empty($lDateArray)){
				$latest_date = $lDateArray['2']."-".$lDateArray['1']."-".$lDateArray['0'];
				$drawing_array['latest_date'] = $latest_date;
			}
			$count_date = $this->get_text($data,'countdown({date: "',' ');
			if(!empty($count_date)){
				$count_date_array = explode("/", $count_date);
				$next_date = $count_date_array['2']."-".$count_date_array['0']."-".$count_date_array['1'];
				$drawing_array['next_date'] = $next_date;
			}
			preg_match_all("{<li>(.*?)</li>}", $latestData, $latestwhiteData);
			preg_match_all("{<li class=\"extra\">(.*?)</li>}", $latestData, $latestmegaData);
			$whiteballArray = (!empty($latestwhiteData[1])) ? $latestwhiteData[1] : "";
			$megaballArray = (!empty($latestmegaData[1])) ? $latestmegaData[1] : "";
			$index = 1;
			foreach ($whiteballArray as $wkey => $wvalue) {
				$drawing_array['whiteball'.$index] = $wvalue;
				$index++;
			}
			$drawing_array['megaball'] = (!empty($megaballArray[0])) ? $megaballArray[0] : NULL;
			$drawing_array['megaball1'] = (!empty($megaballArray[1])) ? $megaballArray[1] : NULL;
			$drawing_array['latest_cash_value'] = NULL;
			$drawing_array['next_cash_value'] = NULL;
			$drawing_array['latest_price'] = NULL;
			$drawing_array['next_price'] = $this->get_text($contentData,'<span class="jackpot">','<div class="counter">');
			$this->load->model('Drawing_model');
			$existsdata = $this->Drawing_model->get_drawing_data(array('website_id' => $website_id,'latest_date' => $drawing_array['latest_date']));
			if(empty($existsdata)){
				$this->Drawing_model->insert_entry($website_id,$drawing_array);
				preg_match_all("{<tr>(.*?)</tr>}", $contentData, $contentDatas);
				$rowArray = (!empty($contentDatas[1])) ? $contentDatas[1] : "";
				if(!empty($rowArray)){
					foreach ($rowArray as $key => $value) {
						$history_array = array();
						$history_array['unique_id'] =  NULL;
						$history_array['latest_date'] =  $drawing_array['latest_date'];
						$bollText = $this->get_text($value,'<td class="name">','</td>');
						if(!empty($bollText)){
							$replace_array = array('(',')'," EuroNumbers"," Numbers"," ");
							$bollText = str_replace($replace_array, "", $bollText);
							$bollArray = explode(",", $bollText);
							$history_array['whiteball'] = $bollArray[0];
							$history_array['megaball'] = $bollArray[1];
							$winText = $this->get_text($value,'<td class="win">','</td>');
							$history_array['is_jackpot'] = ($winText == "1 x") ? 1 : 0;
							$history_array['price_amount'] = $this->get_text($value,'<td class="prize">','</td>');
							$this->load->model('Winner_history_model');
							$this->Winner_history_model->insert_entry($website_id,$history_array);

				$counter_rk++;
						}
					}
				}
			}
		}

		if($counter_rk!=0){
			$this->winner_script($website_id);
		}
	}
	/**
	* scrap euro millions data
	**/
	public function euro_millions(){

		$counter_rk=0;
		$website_id = 3;
		$drawing_array = array();
		$domain = "https://www.euro-millions.com";
		$data = $this->getpageData("https://www.euro-millions.com/");
		$result_link = $this->get_text($data,'EuroMillions Results">Latest Results</a></li><li><a href="','"');
		$result_link = (stristr($result_link, $domain)) ? $result_link : $domain.$result_link;
		$drawing_array['next_date'] = $this->get_text($data,'new Date("','T');
		$drawing_array['next_price'] = $this->get_text($data,'<div class="jackpotAmount">','</div>');
		$resultData = $this->getpageData($result_link);
		$contentData = $this->get_content($resultData,'<div id="content"><h1>','</div><div class="box">');
		$historyData = $this->get_content($resultData,'Prize Breakdown</h2>','</table>');
		$latestData = $this->get_text($contentData,'EuroMillions Results for ','</h1>');
		$latest_date = (!empty($latestData)) ? date('Y-m-d',strtotime($latestData)) : NULL;
		$drawing_array['latest_date'] = $latest_date;
		preg_match_all("{<li class=\"new ball\">(.*?)</li>}", $contentData, $latestwhiteData);
		preg_match_all("{<li class=\"new lucky-star\">(.*?)</li>}", $contentData, $latestmegaData);
		$whiteballArray = (!empty($latestwhiteData[1])) ? $latestwhiteData[1] : "";
		$megaballArray = (!empty($latestmegaData[1])) ? $latestmegaData[1] : "";
		$index = 1;
		if(!empty($whiteballArray)){
			foreach ($whiteballArray as $wkey => $wvalue) {
				$drawing_array['whiteball'.$index] = $wvalue;
				$index++;
			}			
		}
		$drawing_array['megaball'] = (!empty($megaballArray[0])) ? $megaballArray[0] : NULL;
		$drawing_array['megaball1'] = (!empty($megaballArray[1])) ? $megaballArray[1] : NULL;
		$drawing_array['latest_cash_value'] = NULL;
		$drawing_array['next_cash_value'] = NULL;
		$drawing_array['latest_price'] = NULL;
		$this->load->model('Drawing_model');
		$existsdata = $this->Drawing_model->get_drawing_data(array('website_id' => $website_id,'latest_date' => $drawing_array['latest_date']));
		if(empty($existsdata)){
			$this->Drawing_model->insert_entry($website_id,$drawing_array);
			preg_match_all("{<tr>(.*?)</tr>}", $historyData, $historyDatas);
			$history_array = (!empty($historyDatas[1])) ? $historyDatas[1] : "";
			if(!empty($history_array)){
				foreach ($history_array as $key => $value) {
					$history_array = array();
					$history_array['unique_id'] =  NULL;
					$history_array['latest_date'] =  $drawing_array['latest_date'];
					$bollText = $this->get_text($value,'data-title="Numbers Matched">','<br>');
					if(!empty($bollText)){
						$replace_array = array('Match ',' Stars'," ",'Star');
						$bollText = str_replace($replace_array, "", $bollText);
						$bollArray = explode("and", $bollText);
						$history_array['whiteball'] = (!empty($bollArray[0])) ? $bollArray[0] : 0;
						$history_array['megaball'] = (!empty($bollArray[1])) ? $bollArray[1] : 0;
						$winText = $this->get_text($value,'Total Winners">','</td>');
						$history_array['is_jackpot'] = (stristr($winText, 'Rollover')) ? 1 : 0;
						$history_array['price_amount'] = $this->get_text($value,'Winner">','</td>');
						$this->load->model('Winner_history_model');
						$this->Winner_history_model->insert_entry($website_id,$history_array);

				$counter_rk++;
					}
				}
			}		
		}
		if($counter_rk!=0){
			$this->winner_script($website_id);
		}
	}
	/**
	* scrap euro millions data
	**/
	public function uk_euro(){
		$counter_rk=0;
		$website_id = 4;		
		$domain = "https://www.lottery.co.uk";
		$drawing_array = array();
		$data = $this->getpageData("https://www.lottery.co.uk/euromillions/results");
		$link_text = $this->get_content($data,'resultsBottom latest">','</div></div>');
		$result_link = $this->get_text($link_text,'href="','"');
		$result_link = (stristr($result_link, $domain)) ? $result_link : $domain.$result_link;
		$resultData = $this->getpageData($result_link);
		$latestData = $this->get_content($data,'<h2>Latest Result</h2>','<div class="rolloverCorner');
		$nextData = $this->get_content($resultData,'Next Estimated EuroMillions Jackpot</span>','</div><div');
		$drawing_array['next_price'] = $this->get_text($nextData,'class="bigJackpotWhite">','</span>');
		$nextDateData = $this->get_text($nextData,'<br><strong>','</strong>');
		$drawing_array['next_date'] = (!empty($nextDateData)) ? date('Y-m-d',strtotime($nextDateData)) : NULL;
		if(empty($drawing_array['next_price'])){
			$npdata = $this->getpageData("https://www.thelotter.com/lottery-results/euromillions-uk/");			
			$drawing_array['next_price'] = $this->get_text($npdata,'<span class="menu-syndicates-info-jackpot">','</span>');
		}
		$latestDateData = $this->get_text($latestData,'class="smallerHeading">','</span>');
		$latest_date = (!empty($latestDateData)) ? date('Y-m-d',strtotime($latestDateData)) : NULL;
		$drawing_array['latest_date'] = $latest_date;
		preg_match_all("{euromillions-ball floatLeft\">(.*?)</div>}", $latestData, $latestwhiteData);
		preg_match_all("{euromillions-lucky-star floatLeft\">(.*?)</div>}", $latestData, $latestmegaData);
		$whiteballArray = (!empty($latestwhiteData[1])) ? $latestwhiteData[1] : "";
		$megaballArray = (!empty($latestmegaData[1])) ? $latestmegaData[1] : "";
		$index = 1;
		if(!empty($whiteballArray)){
			foreach ($whiteballArray as $wkey => $wvalue) {
				$drawing_array['whiteball'.$index] = $wvalue;
				$index++;
			}			
		}
		if(empty($drawing_array['next_date'])){
			$date = strtotime($drawing_array['latest_date']);
			$date = strtotime("+4 day", $date);
			$drawing_array['next_date'] = date('Y-m-d', $date);
		}
		$drawing_array['megaball'] = (!empty($megaballArray[0])) ? $megaballArray[0] : NULL;
		$drawing_array['megaball1'] = (!empty($megaballArray[1])) ? $megaballArray[1] : NULL;
		$drawing_array['latest_cash_value'] = NULL;
		$drawing_array['next_cash_value'] = NULL;
		$drawing_array['latest_price'] = $this->get_text($latestData,'<span class="resultJackpot">','</span>');
		$this->load->model('Drawing_model');
		$existsdata = $this->Drawing_model->get_drawing_data(array('website_id' => $website_id,'latest_date' => $drawing_array['latest_date']));
		if(empty($existsdata)){
			$this->Drawing_model->insert_entry($website_id,$drawing_array);
			$historyData = $this->get_content($resultData,'<h2>Prize Breakdown</h2>','<tr class="totals">');
			preg_match_all("{<tr(.*?)</tr>}", $historyData, $historyDatas);
			$history_array = (!empty($historyDatas[1])) ? $historyDatas[1] : "";
			if(!empty($history_array)){
				foreach ($history_array as $key => $value) {
					$history_array = array();
					$history_array['unique_id'] =  NULL;
					$history_array['latest_date'] =  $drawing_array['latest_date'];
					$bollText = $this->get_text($value,'noBefore">','</td>');
					if(!empty($bollText)){
						$replace_array = array('Match ',' Stars'," ",'Star');
						$bollText = str_replace($replace_array, "", $bollText);
						$bollArray = explode("and", $bollText);
						$history_array['whiteball'] = (!empty($bollArray[0])) ? $bollArray[0] : 0;
						$history_array['megaball'] = (!empty($bollArray[1])) ? $bollArray[1] : 0;
						$winText = $this->get_text($value,'Total Winners">','</td>');
						$history_array['is_jackpot'] = (stristr($winText, 'Rollover')) ? 1 : 0;
						$history_array['price_amount'] = $this->get_text($value,'Winner">','</td>');
						$this->load->model('Winner_history_model');
						$this->Winner_history_model->insert_entry($website_id,$history_array);
						$counter_rk++;
					}
				}
			}
		}
		if($counter_rk!=0){
			$this->winner_script($website_id);
		}	
	}
	public function german_lottery(){
		$counter_rk=0;
		$website_id = 5;
		$data = $this->getGermanyData("https://wm.thelotter.com/HttpHandlers/GetDrawWithResults.ashx?callback=jQuery171011663047835219476_1570154520518&lotteryref=20");
		$drawingData = (!empty($data['Data']['DrawResultsData'])) ? $data['Data']['DrawResultsData'] : "";
		$drawing_array = array();
		if(!empty($drawingData)){
			$drawing_array['latest_date'] = ($drawingData['DrawLocalCloseDate']) ? date('Y-m-d',$drawingData['DrawLocalCloseDate']) : "";
			$drawing_array['latest_price'] = ($drawingData['FormattedJackpot']) ? $drawingData['FormattedJackpot'] : NULL;
		}
		$drawing_array['next_price'] = ($data['Data']['DrawBaseData']['FormattedJackpot']) ? $data['Data']['DrawBaseData']['FormattedJackpot'] : 0;
		$drawing_array['next_cash_value'] = NULL;
		$drawing_array['latest_cash_value'] = NULL;
		if(!empty($drawingData['WiningNumbers'])){
			if(!empty($drawingData['WiningNumbers'][0]['RegularResults'])){
				$index = 1;
				foreach ($drawingData['WiningNumbers'][0]['RegularResults'] as $wkey => $wvalue) {
					$drawing_array['whiteball'.$index] = $wvalue;
					$index++;
				}
				$drawing_array['megaball'] = $drawingData['WiningNumbers'][0]['AdditionalResults'][0];
				$drawing_array['megaball1'] = 0;
			}
			
		}
		
		$GData = $this->getpageData("https://www.lottoland.com/en/german-lottery");
		$g_date_data = $this->get_content($GData,'<footer onclick="dla.navigate.onClick.call(this, event, \'/en/german-lottery','</footer>');
		$next_dateData = $this->get_text($g_date_data,'lotteryDrawingMoment">','</div>');
		if(!empty($next_dateData)){
			$n_date_array = explode(" ", $next_dateData);
			if(count($n_date_array) > 2){
				$ndCnt = 1;
				$createDate = "";
				foreach ($n_date_array as $key => $value) {
					if($ndCnt>3){
						$year = date("Y");
						$createDate = $createDate." ".$year;
						$drawing_array['next_date'] = date('Y-m-d',strtotime($createDate));
						break;
					}
					$createDate = (!empty($createDate)) ? $createDate." ".$value : $value;
					$ndCnt++;
				}
			}
		}else{
			if(!empty($drawing_array['latest_date'])){
				$date = strtotime($drawing_array['latest_date']);
				$date = strtotime("+7 day", $date);
				$drawing_array['next_date'] = date('Y-m-d', $date);
			}			
		}
		$this->load->model('Drawing_model');
		$existsdata = $this->Drawing_model->get_drawing_data(array('website_id' => $website_id,'latest_date' => $drawing_array['latest_date']));
		if(empty($existsdata)){
			$this->Drawing_model->insert_entry($website_id,$drawing_array);
			if(!empty($drawingData['PrizeBreackdowns'])){
				$ind = 1;
				foreach ($drawingData['PrizeBreackdowns'] as $key => $value) {
					$history_array = array();
					$history_array['unique_id'] =  NULL;
					$history_array['latest_date'] =  $drawing_array['latest_date'];

					$history_array['whiteball'] =  $value['Guess']['GuessCount'];
					$history_array['megaball'] =  $value['Guess']['AdditionalCount'];
					if($ind == 1){
						$history_array['is_jackpot'] =  ($value['NoWinner'] == 1) ? 0 : 1;
					}else{
						$history_array['is_jackpot'] =  0;
					}
					$history_array['price_amount'] =  ($value['LocalWinningAmountToDisplay'] == '-999.00') ? 'No Winners' : $value['LocalWinningAmountToDisplay'];
					$ind++;
					$this->load->model('Winner_history_model');
					$this->Winner_history_model->insert_entry($website_id,$history_array);
					$counter_rk++;
				}
			}
		}
		if($counter_rk!=0){
			$this->winner_script($website_id);
		}
	}
	/**
	* scrap megamillions data
	**/
	public function getData($url){
		sleep(rand(2,6));
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_NOBODY, false);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
	    curl_close($ch);
	    $data = str_replace('</string>','',$data);
	    $data = str_replace('<string xmlns="http://tempuri.org/">','',$data);
	    $data = str_replace('<?xml version="1.0" encoding="utf-8"?>','',$data);
	    $data_array = json_decode($data,true);
	    return $data_array;
	}
	/**
	* scrap germany data
	**/
	public function getGermanyData($url){
		sleep(rand(2,6));
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_NOBODY, false);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
	    curl_close($ch);
	    $data = str_replace('jQuery171011663047835219476_1570154520518','',$data);
	    $data = str_replace('({','{',$data);
	    $data = str_replace('})','}',$data);
	    $data_array = json_decode($data,true);
	    return $data_array;
	}
	/**
	* scrap megamillions data
	**/
	public function getpageData($url){
		sleep(rand(2,6));
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_NOBODY, false);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
	    curl_close($ch);
	    $newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
    	$data = str_replace($newlines, "", html_entity_decode($data));
	    return $data;
	}

	public function convert_in_dollar($amount){
		$sign = "$";
		if(strlen($amount)>6){
			$amount = $amount/1000000;
			$amount = $amount." Million";
		}
		$amount = $sign."".$amount;
		return $amount;
	}

	// Get inner html value
	public function get_text($data, $start, $end, $allow=""){
		$start = str_replace('"', '\"', $start);
		$start = str_replace('/', '\/', $start);
		$start = str_replace('(', '\(', $start);
		$start = str_replace(')', '\)', $start);
		$start = str_replace('{', '\{', $start);
		$start = str_replace('}', '\}', $start);
		$start = str_replace('*', '\*', $start);
		$start = str_replace('.', '\.', $start);
		$start = str_replace('?', '\?', $start);
		$end = str_replace('"', '\"', $end);
		$end = str_replace('/', '\/', $end);
		$end = str_replace('(', '\(', $end);
		$end = str_replace(')', '\)', $end);
		$end = str_replace('{', '\{', $end);
		$end = str_replace('}', '\}', $end);
		$end = str_replace('*', '\*', $end);
		$end = str_replace('.', '\.', $end);
		$end = str_replace('?', '\?', $end);
		preg_match("{".$start."(.*?)".$end."}", $data, $datas);
		$name = (empty($datas[1])) ? "" : trim($datas[1], " ");
		$name = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $name);
		$name = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $name);
		return ($name == "NA") ? "" : strip_tags(trim($name, " "), $allow);
	}

	// Get inner html content
	public function get_content($data, $start, $end){
		$start = str_replace('"', '\"', $start);
		$start = str_replace('/', '\/', $start);
		$start = str_replace('(', '\(', $start);
		$start = str_replace(')', '\)', $start);
		$end = str_replace('"', '\"', $end);
		$end = str_replace('/', '\/', $end);
		$end = str_replace('(', '\(', $end);
		$end = str_replace(')', '\)', $end);
		preg_match("{".$start."(.*?)".$end."}", $data, $datas);
		$name = (empty($datas[1])) ? "" : trim($datas[1], " ");
		return ($name == "NA") ? "" : trim($name, " ");
	}

	// Update Winner Script By Rk
	public function winner_script($website_id){
		$id = $website_id;
		$this->load->model('web_model');
		$data['WebInfo'] = $this->web_model->getWebInfo($id);
		$data['RangeInfo'] = $this->web_model->getrangeInfo($id);
		
		$winner_last = $this->web_model->winner_last($id,$data);
		echo "Done";


	}

// Send file code
	public function send_file(){
		$array  = array();

		$headerrr = array("Ticket ID","Participant ID","Lottery","Number 1","Number 2","Number 3","Number 4","Number 5","Number 6","Star 1","Star 2","Insurance winning class I","Insurance winning class II","Insurance winning class III");

		$array[]=array(
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX","XXX",
			"XXX",
			"XXX",
			"XXX"
		);
		$array[]=array(
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX",
			"XXX"
		);


		$csv = implode(",",$headerrr)." \n";
		

		foreach ($array as $record){
			for($i=0; $i<14; $i++){
				if($i==0){
					$csv.= $record[0];	
				}
				else{
					$csv .= ','.$record[1];
				}
			}
			$csv .= " \n";
			
		}
		

		$csv_handler = fopen ('csv/csvfile.csv','w');
		fwrite ($csv_handler,$csv);
		fclose ($csv_handler);

		$targetPath="csv/csvfile.csv";
		$data = file_get_contents($targetPath);
		// echo "<pre>";
		// print_r($data);
		// echo "</prE>";


		$content= base64_decode($data);


		// echo $content;
		// die();

		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => "http://rtrt.emirat.de/APIs/esmartgames/webservice.asmx",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<soap12:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap12=\"http://www.w3.org/2003/05/soap-envelope\">\n  <soap12:Body>\n    <TransferFile xmlns=\"http://emirat.de/\">\n      <key>A60D9E7C-B584-4EDA-A0CA-6DF8F90789DE</key>\n      <filenameIncludingExtension>0000.csv</filenameIncludingExtension>\n      <filecontents>\n      ".$content."\n      </filecontents>\n    </TransferFile>\n  </soap12:Body>\n</soap12:Envelope>",
		CURLOPT_HTTPHEADER => array(
			"Accept: */*",
			"Accept-Encoding: gzip, deflate",
			"Cache-Control: no-cache",
			"Connection: keep-alive",
			"Content-Length: 507",
			"Content-Type: text/xml",
			"Host: rtrt.emirat.de",
			"cache-control: no-cache"
		),
		));

		$response = curl_exec($curl);

		echo "<pre>";
			print_R($response);
			echo "</pre>";
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
			
		}
		echo "Done";

	}
}
