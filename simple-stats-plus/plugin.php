<?php
/*
	This plugin uses the javascript library https://github.com/gionkunz/chartist-js
	This plugin originated from Bludit's Simple Stats.
	
*/
class pluginSimpleStatsPlus extends Plugin {

	private $loadOnController = array(
		'dashboard','configure-plugin'
	);

	public function init()
	{
		global $L;

		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'enableOngoingCounter'=>true,
			'resetOngoingCounterValue'=>'-1',
			'enableIndividualPageCounter'=>true,
			'includeStaticPageCounter'=>false,
			'chartType'=>'Weekly',
			'horizontalCharts'=>false,
			'numberOfPageStatsToShow'=>10,
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

		$html .= '<div class="simple-stats-plus-plugin">';

		// Check if the Bludit plugin Simple Stats is activated
		if (pluginActivated('pluginSimpleStats')) {
			// Show an alert about the conflict of the original plugin
			$html .= '<div class="alert alert-warning" role="alert">';
			$html .= $L->get('bludit-plugin-simple-stats-active-warning');
			$html .= '</div>';
		}

		//check if $formatter will work AND warn if not.
		$formatStyle=NumberFormatter::TYPE_INT32;
		$formatter= new NumberFormatter(@$locale, $formatStyle); // NB: NumberFormatter() needs PHP_Intl module installed on host
		
		TRY{
			$checkFormatter = $formatter->format('9999.99');
								// '<div class="alert alert-warning" role="alert">'.
								// 'This plugin uses PHP_Intl module: Check passed; 9999.99 displays as '.$formatter->format('9999.99').
								// '</div>';
			// $html .= $checkFormatter;
		}
		CATCH(Exception $e){
			$checkFormatter = '<div class="alert alert-warning" role="alert">'.
								'This plugin uses PHP_Intl module: The check failed - please install PHP_Intl to see formatted numbers.<br>'.
								'Google, "How do I install PHP intl extension on {your-host-os}.<br>'.
								'For example: On Centos 7, the command to install is "yum install rh-php72-php-intl".</div>';
		$html .= $checkFormatter;
		}
		// FINALLY {
			// $checkFormatter = '<div class="alert alert-warning" role="alert">'.
								// 'This plugin uses PHP_Intl module: The check failed - please install PHP_Intl to see formatted numbers.<br>'.
								// 'Google, "How do I install PHP intl extension on {your-host-os}.<br>'.
								// 'For example: On Centos 7, the command to install is "yum install rh-php72-php-intl".</div>';
		// $html .= $checkFormatter;
		// }

		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			// Define ongoing running total counter
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('ongoing-counter-label').'</label>';
				$html .= '<select name="enableOngoingCounter">';
				$html .= '<option value="true" '.($this->getValue('enableOngoingCounter')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
				$html .= '<option value="false" '.($this->getValue('enableOngoingCounter')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
				$html .= '</select>';
				$html .= '<span class="tip">'.$L->get('ongoing-counter-tip').'</span>';
			$html .= '</div>';
			// Controls the resetting of the ongoing counter
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('reset-counter-label').'</label>';
				$html .= '<input id="jsresetOngoingCounterValue" name="resetOngoingCounterValue" type="number" value="'.$this->getValue('resetOngoingCounterValue').'">';
				$html .= '<span class="tip">'.$L->get('reset-counter-tip-one').'</span>';
				$html .= '<span class="tip">'.$L->get('reset-counter-tip-two').'</span>';
			$html .= '</div>';		
		$html .= '</div></div></div>';

		$html .= '<hr>';
		
		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			// Define the chart type
			$html .= '<div class="divTableCell">';			
				$html .= '<label class="labelStyle">'.$L->get('chart-type-label').'</label>';
				$html .= '<select name="chartType">';
				$html .= '<option value="Daily" '.($this->getValue('chartType')==='Daily'?'selected':'').'>'.$L->get('daily-chart').'</option>';
				$html .= '<option value="Weekly" '.($this->getValue('chartType')==='Weekly'?'selected':'').'>'.$L->get('weekly-chart').'</option>';
				$html .= '<option value="Monthly" '.($this->getValue('chartType')==='Monthly'?'selected':'').'>'.$L->get('monthly-chart').'</option>';
				$html .= '</select>';
				$html .= '<span class="tip">'.$L->get('chart-type-tip').'</span>';
			$html .= '</div>';

			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('exclude-admin-users').'</label>';
				$html .= '<select name="excludeAdmins">';
				$html .= '<option value="true" '.($this->getValue('excludeAdmins')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
				$html .= '<option value="false" '.($this->getValue('excludeAdmins')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
				$html .= '</select>';
			$html .= '</div>';			
			
		$html .= '</div></div></div>';

		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			// Define how long to keep stats. Zero also turns unwanted collections off.
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('number-of-days-label').'</label>';
				$html .= '<input id="jsnumberOfDaysToKeep" name="numberOfDaysToKeep" type="number" value="'.$this->getValue('numberOfDaysToKeep').'">';
				$html .= '<span class="tip">'.$L->get('number-of-days-tip').'</span>';
			$html .= '</div>';

			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('number-of-weeks-label').'</label>';
				$html .= '<input id="jsnumberOfWeeksToKeep" name="numberOfWeeksToKeep" type="number" value="'.$this->getValue('numberOfWeeksToKeep').'">';
				$html .= '<span class="tip">'.$L->get('number-of-weeks-tip').'</span>';
			$html .= '</div>';
		$html .= '</div></div></div>';

		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('number-of-months-label').'</label>';
				$html .= '<input id="jsnumberOfMonthsToKeep" name="numberOfMonthsToKeep" type="number" value="'.$this->getValue('numberOfMonthsToKeep').'">';
				$html .= '<span class="tip">'.$L->get('number-of-months-tip').'</span>';
			$html .= '</div>';

			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('page-session-active-minutes').'</label>';
				$html .= '<input id="jspageSessionActiveMinutes" name="pageSessionActiveMinutes" type="number" value="'.$this->getValue('pageSessionActiveMinutes').'">';
				$html .= '<span class="tip">'.$L->get('page-session-active-minutes-tip').'</span>';
			$html .= '</div>';
		$html .= '</div></div></div>';

		$html .= '<hr>';
			// Define other options
		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('show-content-stats-label').'</label>';
				$html .= '<select name="showContentStats">';
				$html .= '<option value="true" '.($this->getValue('showContentStats')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
				$html .= '<option value="false" '.($this->getValue('showContentStats')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
				$html .= '</select>';
				$html .= '<span class="tip">'.$L->get('show-content-stats-tip').'</span>';
			$html .= '</div>';

			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('individual-page-counter-label').'</label>';
				$html .= '<select name="enableIndividualPageCounter">';
				$html .= '<option value="true" '.($this->getValue('enableIndividualPageCounter')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
				$html .= '<option value="false" '.($this->getValue('enableIndividualPageCounter')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
				$html .= '</select>';
				$html .= '<span class="tip">'.$L->get('individual-page-counter-tip').'</span>';
			$html .= '</div>';

		$html .= '</div></div></div>';
		
		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			// Define individual-page running total counter
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('show-static-page-count-label').'</label>';
				$html .= '<select name="includeStaticPageCounter">';
				$html .= '<option value="true" '.($this->getValue('includeStaticPageCounter')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
				$html .= '<option value="false" '.($this->getValue('includeStaticPageCounter')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
				$html .= '</select>';
				$html .= '<span class="tip">'.$L->get('show-static-page-count-tip').'</span>';
			$html .= '</div>';	
			$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('number-of-pagestats-label').'</label>';
				$html .= '<input id="jsnumberOfPageStatsToShow" name="numberOfPageStatsToShow" type="number" value="'.$this->getValue('numberOfPageStatsToShow').'">';
				$html .= '<span class="tip">'.$L->get('number-of-pagestats-tip').'</span>';
			$html .= '</div>';
		$html .= '</div></div></div>';
		
		//$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';

			//$html .= '<div class="divTableCell">';
				$html .= '<label class="labelStyle">'.$L->get('horizontal-charts-label').'</label>';
				$html .= '<select name="horizontalCharts">';
				$html .= '<option value="true" '.($this->getValue('horizontalCharts')===true?'selected':'').'>'.$L->get('enable-section').'</option>';
				$html .= '<option value="false" '.($this->getValue('horizontalCharts')===false?'selected':'').'>'.$L->get('disable-section').'</option>';
				$html .= '</select>';
				$html .= '<span class="tip">'.$L->get('horizontal-charts-tip').'</span>';
			//$html .= '</div>';
		//$html .= '</div></div></div>';
		$html .= '</div>';// Close class="simple-stats-plus-plugin"

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
		$html .= $this->includeCSS('simplestatsplus.style.css');

		// Include plugin's Javascript files
		$html .= $this->includeJS('chartist.min.js');

		return $html;
	}

	public function dashboard()
	{
		global $L;
		$formatStyle=NumberFormatter::TYPE_INT32;
		$formatter= new NumberFormatter(@$locale, $formatStyle); // NB: NumberFormatter() needs PHP_Intl module installed on host
		$currentDate = Date::current('Y-m-d');
		$mondayDateThisWeek = date("Y-m-d", strtotime('monday this week'));
		$firstDateOfThisThisMonth = date("Y-m-d", strtotime('first day of this month'));

		// Get all stat raw totals
		$pageViewsToday 			= $this->getPageViewCount($currentDate, 'Daily');
		$uniqueVisitorsToday		= $this->getUniqueVisitorCount($currentDate, 'Daily');
		$pageViewsThisWeek			= $this->getPageViewCount($mondayDateThisWeek, 'Weekly');
		$uniqueVisitorsThisWeek		= $this->getUniqueVisitorCount($mondayDateThisWeek, 'Weekly');
		$pageViewsThisMonth			= $this->getPageViewCount($firstDateOfThisThisMonth, 'Monthly');
		$uniqueVisitorsThisMonth	= $this->getUniqueVisitorCount($firstDateOfThisThisMonth, 'Monthly');

		// Get the running totals
		$runningTotalsFile			= $this->workspace().'running-totals.json';
		$runningTotalsArray			= json_decode(file_get_contents($runningTotalsFile),TRUE);
		$pageCount					= $runningTotalsArray['runningTotals']['pageCounter'];

		// Try to apply local international formatting or keep as raw
		TRY{ $pageViewsToday 			= $formatter->format($pageViewsToday);			} CATCH(Exception $e) {} // Catch left blank because raw value is retained.
		TRY{ $uniqueVisitorsToday		= $formatter->format($uniqueVisitorsToday);		} CATCH(Exception $e) {}
		TRY{ $pageViewsThisWeek			= $formatter->format($pageViewsThisWeek);		} CATCH(Exception $e) {}
		TRY{ $uniqueVisitorsThisWeek	= $formatter->format($uniqueVisitorsThisWeek);	} CATCH(Exception $e) {}
		TRY{ $pageViewsThisMonth		= $formatter->format($pageViewsThisMonth);		} CATCH(Exception $e) {}
		TRY{ $uniqueVisitorsThisMonth	= $formatter->format($uniqueVisitorsThisMonth);	} CATCH(Exception $e) {}
		TRY{ $pageCount					= $formatter->format($pageCount); 				} CATCH(Exception $e) {}

		$chartType = $this->getValue('chartType');
		
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
<div class="simple-stats-plus-plugin">
	<div class="my-5 pt-4 border-top">
		<h4 class="pb-3">$chartType {$L->get('stats-title-label')}</br>($chartStartDate to $chartEndDate)</h4>
		<h5 class="pb-3">Total Page Count: {$pageCount}</h5>
		<div class="ct-chart ct-perfect-fourth"></div>

		<!- Show all the totals for each of the current periods -->
		<div class="divTable" style="width: 100%;" >
			<div class="divTableBody">
				<div class="divTableRow">
					<div class="divDashTableCell">
						<p class="legends visits-today">{$L->get('page-view-today-label')}: {$pageViewsToday}</p>
						<p class="legends unique-today">{$L->get('unique-visitors-today-label')}: {$uniqueVisitorsToday}</p>
					</div>
					<div class="divDashTableCell">	
						<p class="legends visits-today">{$L->get('page-view-this-week-label')}: {$pageViewsThisWeek}</p>
						<p class="legends unique-today">{$L->get('unique-visitors-this-week-label')}: {$uniqueVisitorsThisWeek}</p>
					</div>
					<div class="divDashTableCell">
						<p class="legends visits-today">{$L->get('page-view-this-month-label')}: {$pageViewsThisMonth}</p>
						<p class="legends unique-today">{$L->get('unique-visitors-this-month-label')}: {$uniqueVisitorsThisMonth}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
EOF;

	IF ($chartType == 'Monthly') 
	{
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
	ELSEIF ($chartType == 'Weekly') 
	{
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
	ELSE	// $chartType == 'Daily'
	{	
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

		$showContentStats = $this->getValue('showContentStats');
		$showTopXpages = $this->getValue('numberOfPageStatsToShow');
		
		if ( ($showContentStats) OR ($showTopXpages > 0 ) )  {
			
			IF ( ($showContentStats) AND ($showTopXpages > 0 ) ) {
				$ContentPageTitle = $L->get('combi-content-page-stats-label');
			}
			ELSE {
				IF ($this->getValue('showContentStats')) {
					$ContentPageTitle = $L->get('content-statistics-label');					
				}
				ELSE {
					$ContentPageTitle = $L->get('page-statistics-label');					
				}
			}

			/**
			 * Optional Content Stats Feature
			 */

			if ( $showContentStats )  {
				global $pages, $categories, $tags;

				$ContentData['showContentStats'] = $showContentStats;
				$ContentData['tabContChartTitle'] = $L->get('tab-cont-chart-label');
				$ContentData['tabContTableTitle'] = $L->get('tab-cont-table-label');
				$ContentData['chartData'][$L->get('published-label')]	= count($pages->getPublishedDB());
				$ContentData['chartData'][$L->get('static-label')]		= count($pages->getStaticDB());
				$ContentData['chartData'][$L->get('drafts-label')]		= count($pages->getDraftDB());
				$ContentData['chartData'][$L->get('scheduled-label')]	= count($pages->getScheduledDB());
				$ContentData['chartData'][$L->get('sticky-label')]		= count($pages->getStickyDB());
				$ContentData['chartData'][$L->get('categories-label')]	= count($categories->keys());
				$ContentData['chartData'][$L->get('tags-label')] 		= count($tags->keys());
			}

			/**
			 * Optional Page Stats Feature
			 */
			if ( $showTopXpages > 0 )  {
				$includeStaticPageCounter = $this->getValue('includeStaticPageCounter');
				$PageData['showTopXpages'] = $showTopXpages;
				$PageData['tabPageChartTitle'] = $L->get('tab-page-chart-label')."$showTopXpages)";
				$PageData['tabPageTableTitle'] = $L->get('tab-page-table-label')."$showTopXpages)";

				$testArray = array_filter($runningTotalsArray);

				if (!empty($testArray)) {

					$IndividualPages = $runningTotalsArray[IndividualPages];
					// Sort the $IndividualPages array decending by Counter. Tip: swap return item 1 & 2 around to sort Ascending.
					usort($IndividualPages, function ($item1, $item2) {return $item2['Counter'] <=> $item1['Counter'];});

					$arrayIndex = 0;
					$shownCount = 0;
					$maxCanGather = count($IndividualPages);
					$arrayKeys = array_keys($IndividualPages);

					WHILE ( ($arrayIndex < $maxCanGather) AND ($shownCount < $showTopXpages) )	
					{
						IF ( ( ($includeStaticPageCounter) OR ($IndividualPages[$arrayKeys[$arrayIndex]][Type] <> 'static') )
								AND ($IndividualPages[$arrayKeys[$arrayIndex]][Category] <> 'Hidden') 
								AND ($IndividualPages[$arrayKeys[$arrayIndex]][Type] <> 'autosave') 
							) 
						{
							$shownCount++;
							$pageLabel = $IndividualPages[$arrayKeys[$arrayIndex]][Title] .' ('. $IndividualPages[$arrayKeys[$arrayIndex]][Category].')';
							$pageCount = $IndividualPages[$arrayKeys[$arrayIndex]][Counter];
							$PageData['pageData'][$pageLabel] = $pageCount;
						}
						$arrayIndex++;
					}
				}
				ELSE {
					$PageData['pageData']['No Page Stats Found'] = 0;
				}
			}

			$html .= $this->renderContentStatistics($ContentData, $PageData, $ContentPageTitle);	
		}

		$this->deleteOldLogs( 'Daily',	$this->getValue('numberOfDaysToKeep') );
		$this->deleteOldLogs( 'Weekly', $this->getValue('numberOfWeeksToKeep') );
		$this->deleteOldLogs( 'Monthly',$this->getValue('numberOfMonthsToKeep') );
		$this->deleteOldBaks( );

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
		$pageTitle = $page->title();
		$pageType = $page->type();
		$category = $page->category();

		IF ( ($pageType == 'static') and ($category===false) ) {$category = 'Static Stuff'; }
		IF ( ($pageType !== 'static') and ($category===false) ) {$category = 'No Category'; }

		IF (!( $excludeAdmins AND in_array($RoleName, array("editor","admin") )) AND $pageType <> 'autosave') 
		{
			// Counters will be increased only once per page title session to prevent abuse of F5 refresh to increase count.
			IF (
				( (!isset($_SESSION[$pageTitleHash])) || ((time()-$_SESSION[$pageTitleHash]) > $pageSessionLimit ) )
				AND ($pageTitleHash <> '')
				)
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
					$this->increaseCounter($pageTitleHash, $pageTitle, $pageType, $category );
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

	// Keep only the most recent x BAK files. 
	public function deleteOldBaks( )
	{
		$baks = Filesystem::listFiles($this->workspace(), '*running-totals.json', 'BAK', true);	// example file name: "2020-09-02-running-totals.json.BAK"
		rsort($baks);
		$removeBak = array_slice($baks, 40); // Slice out the files to delete, keeping the number specified.

		foreach ($removeBak as $bak) {
			Filesystem::rmfile($bak);
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
			$fileData = json_decode($line);
			$hashIP = $fileData[0];
			$tmp[$hashIP] = true;
		}
		return count($tmp);
	}

	public function increaseCounter($pageTitleHash, $pageTitle, $pageType, $category) 
	{
		$runningTotalsArray = array();
		$totalPageViews = 0;
		$totalUniqueVisitors = 0;
		$resetOngoingCounterValue = $this->getValue('resetOngoingCounterValue');

		// Get the running totals
		$runningTotalsFile = $this->workspace().'running-totals.json';

		try
		{
			IF (is_file($runningTotalsFile) AND is_readable($runningTotalsFile)) {
				// OK - Lets fill the Totals array
				$runningTotalsArray = json_decode(file_get_contents($runningTotalsFile),TRUE);

				// Before we do anything, lets take a daily backup
				$currentDate = Date::current('Y-m-d');
				$dailyBackupFile = $this->workspace().$currentDate.'-running-totals.json.BAK';
				
				IF (!is_file( $dailyBackupFile )) {
					IF(!copy($runningTotalsFile,$dailyBackupFile)) {
						$error =  'Failed to take daily backup of running-totals.json';
						$this->addErrorLog($error);
						throw new Exception($error);
					}
				}				

				$testArray = array_filter($runningTotalsArray);
				if (!empty($testArray)) {
					// Totals array successfuly filled - now up the counters
					if ($resetOngoingCounterValue < 0 ) {
						$runningTotalsArray['runningTotals']['pageCounter']++;
					}
					else {
						$runningTotalsArray['runningTotals']['pageCounter'] = $resetOngoingCounterValue;
					}

					$runningTotalsArray[IndividualPages][$pageTitleHash]['Title'] = $pageTitle;
					$runningTotalsArray[IndividualPages][$pageTitleHash]['Type'] = $pageType;
					$runningTotalsArray[IndividualPages][$pageTitleHash]['Category'] = $category;
					$runningTotalsArray[IndividualPages][$pageTitleHash]['Counter'] ++;
			
				}
				ELSE {
					$error =  'The runningTotalsFile file existed but array came back empty';
					$this->addErrorLog($error);
					throw new Exception($error);
				}
			}
			ELSE {
				// We need to initiate file to start with, but not if already populated
				$error =  'Could not read the runningTotalsFile file - it might not be there at all';
				$this->addErrorLog($error);
				throw new Exception($error);

				if (!file_exists($runningTotalsFile) ) {
					$runningTotalsArray['runningTotals'] = array(
							'pageCounter' => 0,
							'uniqueCounter' => 0 // not used at the moment - would need to read today's log file to determin if visitor exists
					);

					$error =  'Initilising the runningTotalsFile file - it look like it is not there.';
					$this->addErrorLog($error);
					throw new Exception($error);
				}
				else {
					$runningTotalsArray = json_decode(file_get_contents($runningTotalsFile),TRUE);
				}

			}



			// Check if array has content and write back to file
			$testArray = array_filter($runningTotalsArray);
			IF (!empty($testArray)) {				
				
				$json = json_encode( $runningTotalsArray );		//Encode the array back into a JSON string.
				file_put_contents($runningTotalsFile, $json);	//Save the file.
			}
			ELSE {
				$error =  'Was just about to write to running Totals file, but the array is empty!';
				$this->addErrorLog($error);
				throw new Exception($error);
			}
		}
		catch (Exception $e) {
			echo 'Caught exception: '.$e->getMessage();
		}
	}

	// Add a line to the Error log
	public function addErrorLog($error)
	{
		$currentTime = Date::current('Y-m-d H:i:s');

		$line = json_encode(array($error, $currentTime));
		$currentDate = Date::current('Y-m-d');
		$errorLogFile = $this->workspace().'Error.log';

		return file_put_contents($errorLogFile, $line.PHP_EOL, FILE_APPEND | LOCK_EX)!==false;

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

	public function renderContentStatistics($ContentData, $PageData, $ContentPageTitle)
	{ 
		global $L;

		try {
			$diskUsage = Filesystem::bytesToHumanFileSize(Filesystem::getSize(PATH_CONTENT));
		}
		catch (Exception $e) {
			echo '<div class="alert alert-warning" role="alert">'.
								'Caught exception:<br>'.
								$e->getMessage().'<br>'.
								'This is caused by a broken Symbolic link - find the above path and consider deleting it.</div>';
		}

		if ($this->getValue('horizontalCharts')) {
			// Horizontal Config
			$chartsOptions = 
				'	distributeSeries: true,
					reverseData: true,
					horizontalBars: true,
					height: 350,
					axisX: {onlyInteger: true},					
					axisY: {offset: 200}
				';
		}
		else {
			// Vertical Config
			$chartsOptions = 
				'	distributeSeries: true,
					onlyInteger: true,
					reverseData: false,
					horizontalBars: false,
					height: 350,
					axisX: {offset: 40},
					axisY: {onlyInteger: true}
				';
		}

		$chartRefreshFunction = "$('a[data-toggle=\"tab\"]').on(
									'shown.bs.tab', function (event) {
										$(event.currentTarget.hash).find('.ct-chart-content').each(function(eventl, tab) {
											tab.__chartist__.update();});
									}
								);";

		$html = '<div class="my-5 pt-4 border-top">';

		$html.= "<h4 class='pb-2'>".$ContentPageTitle."</h4>";

		$html.= '

			<nav>
				<div class="nav nav-tabs" id="nav-tab" role="tablist">
					<a class="nav-item nav-link active"	id="nav-stats-page-chart-tab" 	data-toggle="tab" href="#nav-stats-page-chart" 	role="tab" aria-controls="nav-stats-page-chart"	aria-selected="true">'	. $PageData['tabPageChartTitle'].'</a>
					<a class="nav-item nav-link"		id="nav-stats-page-table-tab"	data-toggle="tab" href="#nav-stats-page-table"	role="tab" aria-controls="nav-stats-page-table"	aria-selected="false">'	. $PageData['tabPageTableTitle'].'</a>
					<a class="nav-item nav-link" 		id="nav-stats-cont-chart-tab"	data-toggle="tab" href="#nav-stats-cont-chart"	role="tab" aria-controls="nav-stats-cont-chart"	aria-selected="false">'	. $ContentData['tabContChartTitle'].'</a>
					<a class="nav-item nav-link"		id="nav-stats-cont-table-tab"	data-toggle="tab" href="#nav-stats-cont-table"	role="tab" aria-controls="nav-stats-cont-table"	aria-selected="false">'	. $ContentData['tabContTableTitle'].'</a>
				</div>
			</nav>

			<div class="tab-content my-2" id="nav-tabContent">
				<div class="tab-pane fade show active"	id="nav-stats-page-chart"	role="tabpanel"	aria-labelledby="nav-stats-page-chart-tab">
					<div class="ct-chart-page"></div>
				</div>	

				<div class="tab-pane fade" 				id="nav-stats-page-table"	role="tabpanel"	aria-labelledby="nav-stats-page-table-tab">
					<table class="table table-borderless table-sm table-striped mt-3">
						<tbody>';
							foreach ($PageData['pageData'] as $th => $td) {
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

				<div class="tab-pane fade "	id="nav-stats-cont-chart" role="tabpanel"	aria-labelledby="nav-stats-cont-chart-tab">
					<div class="ct-chart-content pt-2"></div>
				</div>

				<div class="tab-pane fade"	id="nav-stats-cont-table" role="tabpanel"	aria-labelledby="nav-stats-cont-table-tab">
					<table class="table table-borderless table-sm table-striped mt-3">
						<tbody>';
							$html .= "<tr><th>{$L->get('disk-usage-label')}</th><td>$diskUsage</td></tr>";
							foreach ($ContentData['chartData'] as $th => $td) {
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
			  labels: ' . json_encode(array_keys($ContentData['chartData'])) . ',
			  series: ' . json_encode(array_values($ContentData['chartData'])) . '
			}, 
			{'.$chartsOptions.'});

			new Chartist.Bar(".ct-chart-page", {
			  labels: ' . json_encode(array_keys($PageData['pageData'])) . ',
			  series: ' . json_encode(array_values($PageData['pageData'])) . '
			}, 
			{'.$chartsOptions.'});

			'.$chartRefreshFunction.'

		</script>';

		return $html;
	}
}

