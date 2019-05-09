<?php
/*
	This plugin uses the javascript library https://github.com/gionkunz/chartist-js
	This plugin originated from Bludit's Simple Stats.
*/
class pluginSimpleStatsPlus extends Plugin {

	private $loadOnController = array(
		'dashboard'
	);

	public function init()
	{
		global $L;

		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'enableOngoingCounter'=>true,
			'resetOngoingCounterValue'=>'-1',
			'chartType'=>'Weekly',
			'numberOfDaysToKeep'=>8,
			'numberOfWeeksToKeep'=>7,
			'numberOfMonthsToKeep'=>13,
			'showContentStats'=>true,
			'pageSessionActiveMinutes'=>5,
			'excludeAdmins'=>true
		);
	}

	public function form()
	{
		global $L;

		$html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

		// Check if the Bludit plugin Simple Stats is activated
		if (pluginActivated('pluginSimpleStats')) {
			// Show an alert about the conflict of the original plugin
			$html .= '<div class="alert alert-warning" role="alert">';
			$html .= $L->get('bludit-plugin-simple-stats-active-warning');
			$html .= '</div>';
		}

		// Define ongoing running total counter
		$html .= '<div>';
		$html .= '<label>'.$L->get('ongoing-counter-label').'</label>';
		$html .= '<select name="enableOngoingCounter">';
		$html .= '<option value="true" '.($this->getValue('enableOngoingCounter')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
		$html .= '<option value="false" '.($this->getValue('enableOngoingCounter')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
		$html .= '</select>';
		$html .= '<span class="tip">'.$L->get('ongoing-counter-tip').'</span>';
		$html .= '</div>';

		// Controls the resetting of the ongoing counter
		$html .= '<div>';
		$html .= '<label>'.$L->get('reset-counter-label').'</label>';
		$html .= '<input id="jsresetOngoingCounterValue" name="resetOngoingCounterValue" type="number" value="'.$this->getValue('resetOngoingCounterValue').'">';
		$html .= '<span class="tip">'.$L->get('reset-counter-tip-one').'</span>';
		$html .= '<span class="tip">'.$L->get('reset-counter-tip-two').'</span>';
		$html .= '</div>';

		// Define the chart type
		$html .= '<div>';
		$html .= '<label>'.$L->get('chart-type-label').'</label>';
		$html .= '<select name="chartType">';
		$html .= '<option value="Daily" '.($this->getValue('chartType')==='Daily'?'selected':'').'>'.$L->get('daily-chart').'</option>';
		$html .= '<option value="Weekly" '.($this->getValue('chartType')==='Weekly'?'selected':'').'>'.$L->get('weekly-chart').'</option>';
		$html .= '<option value="Monthly" '.($this->getValue('chartType')==='Monthly'?'selected':'').'>'.$L->get('monthly-chart').'</option>';
		$html .= '</select>';
		$html .= '<span class="tip">'.$L->get('chart-type-tip').'</span>';
		$html .= '</div>';

		// Define how long to keep stats. Zero also turns unwanted collections off.
		$html .= '<div>';
		$html .= '<label>'.$L->get('number-of-days-label').'</label>';
		$html .= '<input id="jsnumberOfDaysToKeep" name="numberOfDaysToKeep" type="number" value="'.$this->getValue('numberOfDaysToKeep').'">';
		$html .= '<span class="tip">'.$L->get('number-of-days-tip').'</span>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('number-of-weeks-label').'</label>';
		$html .= '<input id="jsnumberOfWeeksToKeep" name="numberOfWeeksToKeep" type="number" value="'.$this->getValue('numberOfWeeksToKeep').'">';
		$html .= '<span class="tip">'.$L->get('number-of-weeks-tip').'</span>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('number-of-months-label').'</label>';
		$html .= '<input id="jsnumberOfMonthsToKeep" name="numberOfMonthsToKeep" type="number" value="'.$this->getValue('numberOfMonthsToKeep').'">';
		$html .= '<span class="tip">'.$L->get('number-of-months-tip').'</span>';
		$html .= '</div>';
		// Define other options
		$html .= '<div>';
		$html .= '<label>'.$L->get('show-content-stats-label').'</label>';
		$html .= '<select name="showContentStats">';
		$html .= '<option value="true" '.($this->getValue('showContentStats')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
		$html .= '<option value="false" '.($this->getValue('showContentStats')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
		$html .= '</select>';
		$html .= '<span class="tip">'.$L->get('show-content-stats-tip').'</span>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('page-session-active-minutes').'</label>';
		$html .= '<input id="jspageSessionActiveMinutes" name="pageSessionActiveMinutes" type="number" value="'.$this->getValue('pageSessionActiveMinutes').'">';
		$html .= '<span class="tip">'.$L->get('page-session-active-minutes-tip').'</span>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('exclude-admin-users').'</label>';
		$html .= '<select name="excludeAdmins">';
		$html .= '<option value="true" '.($this->getValue('excludeAdmins')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
		$html .= '<option value="false" '.($this->getValue('excludeAdmins')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
		$html .= '</select>';
		$html .= '</div>';

		return $html;
	}

	public function beforeSiteLoad()
	{
		$login = new Login();

		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		if ( $login->isLogged()) {
			$GLOBALS['RoleName'] = $login->Role();
		}
		else {
			$GLOBALS['RoleName'] = 'No Role Identified';
		}
	}

	public function adminHead()
	{
		if (!in_array($GLOBALS['ADMIN_CONTROLLER'], $this->loadOnController)) {
			return false;
		}

		// Include plugin's CSS files
		$html  = $this->includeCSS('chartist.min.css');
		$html .= $this->includeCSS('style.css');

		// Include plugin's Javascript files
		$html .= $this->includeJS('chartist.min.js');

		return $html;
	}

	public function dashboard()
	{
		global $L;
		$formatStyle=NumberFormatter::TYPE_INT32;
		$formatter= new NumberFormatter(@$locale, $formatStyle);
		$currentDate = Date::current('Y-m-d');
		$mondayDateThisWeek = date("Y-m-d", strtotime('monday this week'));
		$firstDateOfThisThisMonth = date("Y-m-d", strtotime('first day of this month'));

		$pageViewsToday = $this->getPageViewCount($currentDate, 'Daily');
		$uniqueVisitorsToday = $this->getUniqueVisitorCount($currentDate, 'Daily');
		$pageViewsThisWeek = $this->getPageViewCount($mondayDateThisWeek, 'Weekly');
		$uniqueVisitorsThisWeek = $this->getUniqueVisitorCount($mondayDateThisWeek, 'Weekly');
		$pageViewsThisMonth = $this->getPageViewCount($firstDateOfThisThisMonth, 'Monthly');
		$uniqueVisitorsThisMonth = $this->getUniqueVisitorCount($firstDateOfThisThisMonth, 'Monthly');
		$chartType = $this->getValue('chartType');

		$runningTotals = json_decode(file_get_contents($this->workspace().'running-totals.json'),TRUE);
		$pageCount = $runningTotals['runningTotals']['pageCounter'];

		IF ($chartType == 'Monthly') {
			$offsetNumber = $numberOfMonthsToKeep = $this->getValue('numberOfMonthsToKeep');

			$chartStartDate = date('Y-m-d' , strtotime ( '-'.$offsetNumber.' month' , strtotime ( $firstDateOfThisThisMonth ) ) );
			$chartEndDate = date("Y-m-d", strtotime ( $firstDateOfThisThisMonth ) );
		}
		ELSEIF ($chartType == 'Weekly') {
			$offsetNumber = $numberOfWeeksToKeep = $this->getValue('numberOfWeeksToKeep');
			$chartStartDate = date('Y-m-d' , strtotime ( '-'.$offsetNumber.' week' , strtotime ( $mondayDateThisWeek ) ) );
			$chartEndDate = date("Y-m-d", strtotime ( $mondayDateThisWeek ) );
		}
		ELSE {	// $chartType == 'Daily'
			$offsetNumber = $numberOfDaysToKeep = $this->getValue('numberOfDaysToKeep');
			$chartStartDate = date('Y-m-d' , strtotime ( '-'.$offsetNumber.' week' , strtotime ( $currentDate ) ) );
			$chartEndDate = date("Y-m-d", strtotime ( $currentDate) );
		}

$html = <<<EOF
<div class="simple-stats-plugin">
	<div class="my-5 pt-4 border-top">
		<h4 class="pb-3">$chartType {$L->get('stats-title-label')}</br>($chartStartDate to $chartEndDate)</h4>
		<h5 class="pb-3">Total Page Count: {$formatter->format($pageCount)}</h5>
		<div class="ct-chart ct-perfect-fourth"></div>

		<!- Show all the totals for each of the current periods -->
		<div class="divTable" style="width: 100%;" >
			<div class="divTableBody">
				<div class="divTableRow">
					<div class="divTableCell">
						<p class="legends visits-today">{$L->get('page-view-today-label')}: {$formatter->format($pageViewsToday)}</p>
						<p class="legends unique-today">{$L->get('unique-visitors-today-label')}: {$formatter->format($uniqueVisitorsToday)}</p>
					</div>
					<div class="divTableCell">
						<p class="legends visits-today">{$L->get('page-view-this-week-label')}: {$formatter->format($pageViewsThisWeek)}</p>
						<p class="legends unique-today">{$L->get('unique-visitors-this-week-label')}: {$formatter->format($uniqueVisitorsThisWeek)}</p>
					</div>
					<div class="divTableCell">
						<p class="legends visits-today">{$L->get('page-view-this-month-label')}: {$formatter->format($pageViewsThisMonth)}</p>
						<p class="legends unique-today">{$L->get('unique-visitors-this-month-label')}: {$formatter->format($uniqueVisitorsThisMonth)}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
EOF;

	IF ($chartType == 'Monthly') {
		$numberOfMonthsToKeep = $this->getValue('numberOfMonthsToKeep');
		$numberOfMonthsToKeep = $numberOfMonthsToKeep - 1;

		for ($i=$numberOfMonthsToKeep; $i >= 0 ; $i--) {

			$dateWithOffset = date('Y-m-d' , strtotime ( '-'.$i.' month' , strtotime ( $firstDateOfThisThisMonth ) ) );

			$visits[$i] = $this->getPageViewCount($dateWithOffset, 'Monthly');
			$unique[$i] = $this->getUniqueVisitorCount($dateWithOffset, 'Monthly');
			$days[$i] = Date::format($dateWithOffset, 'Y-m-d', 'M y'); /// M
		}

		$labels = "'" . implode("','", $days) . "'";
		$seriesVisits = implode(',', $visits);
		$seriesUnique = implode(',', $unique);
	}
	ELSEIF ($chartType == 'Weekly') {
		$numberOfWeeksToKeep = $this->getValue('numberOfWeeksToKeep');
		$numberOfWeeksToKeep = $numberOfWeeksToKeep - 1;

		for ($i=$numberOfWeeksToKeep; $i >= 0 ; $i--) {

			$dateWithOffset = date('Y-m-d' , strtotime ( '-'.$i.' week' , strtotime ( $mondayDateThisWeek ) ) );

			$visits[$i] = $this->getPageViewCount($dateWithOffset, 'Weekly');
			$unique[$i] = $this->getUniqueVisitorCount($dateWithOffset, 'Weekly');
			$days[$i] = Date::format($dateWithOffset, 'Y-m-d', 'd M');
		}

		$labels = "'" . implode("','", $days) . "'";
		$seriesVisits = implode(',', $visits);
		$seriesUnique = implode(',', $unique);
	}
	ELSE {	// $chartType == 'Daily'
		$numberOfDaysToKeep = $this->getValue('numberOfDaysToKeep');
		$numberOfDaysToKeep = $numberOfDaysToKeep - 1;

		for ($i=$numberOfDaysToKeep; $i >= 0 ; $i--) {

			$dateWithOffset = Date::currentOffset('Y-m-d', '-'.$i.' day');

			$visits[$i] = $this->getPageViewCount($dateWithOffset, 'Daily');
			$unique[$i] = $this->getUniqueVisitorCount($dateWithOffset, 'Daily');
			$days[$i] = Date::format($dateWithOffset, 'Y-m-d', 'D');
		}

		$labels = "'" . implode("','", $days) . "'";
		$seriesVisits = implode(',', $visits);
		$seriesUnique = implode(',', $unique);
	}

$script = <<<EOF
<script>
	var data = {
		labels: [$labels],
		series: [
			[$seriesVisits],
			[$seriesUnique]
		]
	};

	var options = {
		height: 250,
		axisY: {
			onlyInteger: true,
		}
	};

	new Chartist.Line('.ct-chart', data, options);
</script>
EOF;

		$this->deleteOldLogs( 'Daily',	$this->getValue('numberOfDaysToKeep') );
		$this->deleteOldLogs( 'Weekly', $this->getValue('numberOfWeeksToKeep') );
		$this->deleteOldLogs( 'Monthly',$this->getValue('numberOfMonthsToKeep') );

		/**
		 * Optional Content Stats Feature
		 **/
		if ($this->getValue('showContentStats'))  {

			global $pages, $categories, $tags;

			$data['title'] = $L->get('content-statistics-label');
			$data['tabTitleChart'] = $L->get('tab-chart-label');
			$data['tabTitleTable'] = $L->get('tab-table-label');
			$data['data'][$L->get('published-label')] = count($pages->getPublishedDB());
			$data['data'][$L->get('static-label')] 	= count($pages->getStaticDB());
			$data['data'][$L->get('drafts-label')]	= count($pages->getDraftDB());
			$data['data'][$L->get('scheduled-label')] = count($pages->getScheduledDB());
			$data['data'][$L->get('sticky-label')] 	= count($pages->getStickyDB());
			$data['data'][$L->get('categories-label')]= count($categories->keys());
			$data['data'][$L->get('tags-label')] 		= count($tags->keys());
			$html .= $this->renderContentStatistics($data);
		}

		return $html.PHP_EOL.$script.PHP_EOL;
	}

	public function siteBodyBegin()
	{
		global $WHERE_AM_I;
		global $RoleName;		
		global $page;

		switch ($WHERE_AM_I) {
			case "search":
				$pageTitleHash = 'search';
				break;
			default:
				$pageTitleHash = ($page->uuid());
		}
		
		$pageSessionLimit = (60*$this->getValue('pageSessionActiveMinutes'));	// 60*5=300 seconds
		$excludeAdmins = ($this->getValue('excludeAdmins'));

		IF (!( $excludeAdmins AND in_array($RoleName, array("editor","admin") )) ) 
		{
			// Counters will be increased only once per page title session to prevent abuse of F5 refresh to increase count.
			IF ( (!isset($_SESSION[$pageTitleHash])) || ((time()-$_SESSION[$pageTitleHash]) > $pageSessionLimit ) )
			{
				//Set Variable for this session so user cannot increase counter by pressing F5
				$_SESSION[$pageTitleHash] = time();
			
				IF ($this->getValue('numberOfDaysToKeep') > 0) {
					$this->addVisitorDaily();
				}

				IF ($this->getValue('numberOfWeeksToKeep') > 0) {
					$this->addVisitorWeekly();
				}

				IF ($this->getValue('numberOfMonthsToKeep') > 0) {
					$this->addVisitorMonthly();
				}

				IF ($this->getValue('enableOngoingCounter')) {
					$this->increaseCounter();
				}
			}
		}
	}
	// Keep only number of logs defined in numberOfDaysToKeep, numberOfWeeksToKeep & numberOfMonthsToKeep.
	public function deleteOldLogs( $periodType, $numberToKeep )
	{
		$logs = Filesystem::listFiles($this->workspace(), '*-'.$periodType, 'log', true);
		$remove = array_slice($logs, $numberToKeep);

		foreach ($remove as $log) {
			Filesystem::rmfile($log);
		}
	}

	// Returns the number of page visits by date per day
	public function getPageViewCount($date, $periodType)
	{
		$file = $this->workspace().$date.'-'.$periodType.'.log';
		$handle = @fopen($file, 'rb');
		if ($handle===false) {
			return 0;
		}

		// The number of page visits are the number of lines on the file
		$lines = 0;
		while (!feof($handle)) {
			$lines += substr_count(fread($handle, 8192), PHP_EOL);
		}
		@fclose($handle);
		return $lines;
	}

	// Returns the number of unique visitors by date
	public function getUniqueVisitorCount($date, $periodType)
	{
		$file = $this->workspace().$date.'-'.$periodType.'.log';
		$lines = @file($file);
		if (empty($lines)) {
			return 0;
		}

		$tmp = array();
		foreach ($lines as $line) {
			$data = json_decode($line);
			$hashIP = $data[0];
			$tmp[$hashIP] = true;
		}
		return count($tmp);
	}

	public function increaseCounter() 
	{
		$runningTotals = array();
		$totalPageViews = 0;
		$totalUniqueVisitors = 0;
		$resetOngoingCounterValue = $this->getValue('resetOngoingCounterValue');
		try
		{

			if (!file_exists($this->workspace().'running-totals.json') ) {
				$runningTotals['runningTotals'] = array(
						'pageCounter' => 0,
						'uniqueCounter' => 0 // not used at the moment - would need to read today's log file to determin if visitor exists
				);
			}
			else {
				$runningTotals = json_decode(file_get_contents($this->workspace().'running-totals.json'),TRUE);
			}
				
			if ($resetOngoingCounterValue < 0 ) {
				$runningTotals['runningTotals']['pageCounter']++;
			}
			else {
				$runningTotals['runningTotals']['pageCounter'] = $resetOngoingCounterValue;
			}

			//Encode the array back into a JSON string.
			$json = json_encode($runningTotals);

			//Save the file.
			file_put_contents($this->workspace().'running-totals.json', $json);
		}
		catch (Exception $e) {
			echo 'Caught exception: '.$e->getMessage();
		}
	}

	// Add a line to the current Daily log
	// The line is a json array with the hash IP of the visitor and the time
	public function addVisitorDaily()
	{

		$currentTime = Date::current('Y-m-d H:i:s');
		$ip = TCP::getIP();
		$hashIP = md5($ip);

		$line = json_encode(array($hashIP, $currentTime));
		$currentDate = Date::current('Y-m-d');
		$logDailyFile = $this->workspace().$currentDate.'-Daily.log';

		return file_put_contents($logDailyFile, $line.PHP_EOL, FILE_APPEND | LOCK_EX)!==false;

	}

	// Add a line to the current Weekly log
	// The line is a json array with the hash IP of the visitor and the time
	public function addVisitorWeekly()
	{

		$mondayDateTimeThisWeek = date("Y-m-d", strtotime('monday this week')).' '. date('H:i:s', strtotime("now"));

		$ip = TCP::getIP();
		$hashIP = md5($ip);

		$line = json_encode(array($hashIP, $mondayDateTimeThisWeek));

		$mondayDateThisWeek = date("Y-m-d", strtotime('monday this week'));

		$logWeeklyFile = $this->workspace().$mondayDateThisWeek.'-Weekly.log';

		return file_put_contents($logWeeklyFile, $line.PHP_EOL, FILE_APPEND | LOCK_EX)!==false;

	}

	// Add a line to the current Monthly log
	// The line is a json array with the hash IP of the visitor and the time
	public function addVisitorMonthly()
	{

		$firstDateTimeOfThisThisMonth = date("Y-m-d", strtotime('first day of this month')).' '. date('H:i:s', strtotime("now"));

		$ip = TCP::getIP();
		$hashIP = md5($ip);

		$line = json_encode(array($hashIP, $firstDateTimeOfThisThisMonth));

		$firstDateOfThisThisMonth = date("Y-m-d", strtotime('first day of this month'));

		$logMonthyFile = $this->workspace().$firstDateOfThisThisMonth.'-Monthly.log';

		return file_put_contents($logMonthyFile, $line.PHP_EOL, FILE_APPEND | LOCK_EX)!==false;
	}

	public function renderContentStatistics($data)
	{
		$html = '<div class="my-5 pt-4 border-top">';
		$html .= "<h4 class='pb-2'>{$data['title']}</h4>";
		$html .= '
		<nav>
		  <div class="nav nav-tabs" id="nav-tab" role="tablist">
		    <a class="nav-item nav-link active" id="nav-stats-chart-tab" data-toggle="tab" href="#nav-stats-chart" role="tab" aria-controls="nav-stats-chart" aria-selected="true">' . $data['tabTitleChart'] .'</a>
		    <a class="nav-item nav-link" id="nav-stats-table-tab" data-toggle="tab" href="#nav-stats-table" role="tab" aria-controls="nav-stats-table" aria-selected="false">' . $data['tabTitleTable'] .'</a>
		  </div>
		</nav>
		<div class="tab-content my-2" id="nav-tabContent">
		  <div class="tab-pane fade show active" id="nav-stats-chart" role="tabpanel" aria-labelledby="nav-stats-chart-tab">
		  	<div class="ct-chart-content pt-2"></div>
		  </div>
		  <div class="tab-pane fade" id="nav-stats-table" role="tabpanel" aria-labelledby="nav-stats-table-tab">
			<table class="table table-borderless table-sm table-striped mt-3">
			  <tbody>';

		foreach ($data['data'] as $th => $td) {
			$html .= "
				<tr>
					<th>$th</th>
					<td>$td</td>
				</tr>
			";
		}

		$html .= '
			  </tbody>
			</table>
		  </div>
		</div>

		</div>

		<script>
		new Chartist.Bar(".ct-chart-content", {
		  labels: ' . json_encode(array_keys($data['data'])) . ',
		  series: ' . json_encode(array_values($data['data'])) . '
		}, {
		  distributeSeries: true
		});
		</script>';

		return $html;
	}
}