<?php
/**
 * Sales-tax filing periods, the order-total rollup, and recorded payments.
 *
 * @package bitcommerce
 */

class CommerceTaxReport extends BitBase {

	const MAX_DUE_PERIODS = 8;

	private static $tablesReady = FALSE;

	private $rateUrls = NULL;

	public static function dueFeedback() {
		$report = new CommerceTaxReport();
		$due = $report->getDuePeriods();
		if( empty( $due ) ) {
			return array();
		}
		global $currencies;
		$parts = array();
		foreach( $due as $row ) {
			$amount = ( is_object( $currencies ) ? $currencies->format( $row['tax_collected'], FALSE ) : $row['tax_collected'] );
			$parts[] = htmlspecialchars( $row['tax_title'], ENT_QUOTES, 'UTF-8' ).' '.htmlspecialchars( $row['label'], ENT_QUOTES, 'UTF-8' ).' ('.$amount.')';
		}
		$url = DIR_WS_HTTPS_ADMIN.'tax_report.php';
		return array(
			'error' => tra( 'Sales taxes are due' ).': '.implode( ', ', $parts ).' <a href="'.htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' ).'">'.tra( 'Record payment' ).'</a>',
		);
	}

	public function saveFilings( $pParameters ) {
		$this->prepare();
		$titles = BitBase::getParameter( $pParameters, 'tax_title', array() );
		$frequencies = BitBase::getParameter( $pParameters, 'frequency', array() );
		$months = BitBase::getParameter( $pParameters, 'cycle_start_month', array() );
		$tracks = BitBase::getParameter( $pParameters, 'track_from', array() );
		if( !is_array( $titles ) ) {
			return array( 'error' => tra( 'Filing schedule was not saved.' ) );
		}
		$known = array_flip( $this->knownTitles() );
		$errors = array();
		$this->mDb->StartTrans();
		foreach( $titles as $i => $title ) {
			$title = trim( (string)$title );
			if( $title === '' || !isset( $known[$title] ) ) {
				$errors[] = tra( 'Unknown tax title.' );
				continue;
			}
			$frequency = $this->normalizeFrequency( isset( $frequencies[$i] ) ? $frequencies[$i] : '' );
			$month = $this->normalizeMonth( isset( $months[$i] ) ? $months[$i] : 1, $frequency );
			$track = $this->normalizeDate( isset( $tracks[$i] ) ? $tracks[$i] : '' );
			if( $frequency === NULL || $month === NULL ) {
				$errors[] = tra( 'Filing schedule was not saved.' );
				continue;
			}
			$existing = $this->mDb->getOne( "SELECT `tax_filing_id` FROM ".TABLE_TAX_FILING." WHERE `tax_title` = ?", array( $title ) );
			if( $existing ) {
				$this->mDb->query(
					"UPDATE ".TABLE_TAX_FILING." SET `frequency` = ?, `cycle_start_month` = ?, `track_from` = ?, `last_modified` = ".$this->mDb->mDb->sysTimeStamp." WHERE `tax_title` = ?",
					array( $frequency, $month, $track, $title )
				);
			} else {
				$this->mDb->query(
					"INSERT INTO ".TABLE_TAX_FILING." (`tax_title`, `frequency`, `cycle_start_month`, `track_from`) VALUES (?, ?, ?, ?)",
					array( $title, $frequency, $month, $track )
				);
			}
		}
		$this->mDb->CompleteTrans();
		if( !empty( $errors ) ) {
			return array( 'error' => $errors );
		}
		return array( 'success' => tra( 'Filing schedule saved.' ) );
	}

	public function addFiling( $pParameters ) {
		$this->prepare();
		$title = trim( (string)BitBase::getParameter( $pParameters, 'tax_title', '' ) );
		if( $title === '' || !in_array( $title, $this->knownTitles(), TRUE ) ) {
			return array( 'error' => tra( 'Unknown tax title.' ) );
		}
		$existing = $this->mDb->getOne( "SELECT `tax_filing_id` FROM ".TABLE_TAX_FILING." WHERE `tax_title` = ?", array( $title ) );
		if( $existing ) {
			return array( 'error' => tra( 'That tax title already has a schedule.' ) );
		}
		$frequency = $this->normalizeFrequency( BitBase::getParameter( $pParameters, 'frequency', '' ) );
		$month = $this->normalizeMonth( BitBase::getParameter( $pParameters, 'cycle_start_month', 1 ), $frequency );
		$track = $this->normalizeDate( BitBase::getParameter( $pParameters, 'track_from', '' ) );
		if( $frequency === NULL || $month === NULL ) {
			return array( 'error' => tra( 'Filing schedule was not saved.' ) );
		}
		$this->mDb->query(
			"INSERT INTO ".TABLE_TAX_FILING." (`tax_title`, `frequency`, `cycle_start_month`, `track_from`) VALUES (?, ?, ?, ?)",
			array( $title, $frequency, $month, $track )
		);
		return array( 'success' => tra( 'Filing schedule added.' ) );
	}

	public function deleteFiling( $pParameters ) {
		$this->prepare();
		$title = trim( (string)BitBase::getParameter( $pParameters, 'tax_title', '' ) );
		if( $title === '' ) {
			return array( 'error' => tra( 'Filing schedule was not deleted.' ) );
		}
		$existing = $this->mDb->getOne( "SELECT `tax_filing_id` FROM ".TABLE_TAX_FILING." WHERE `tax_title` = ?", array( $title ) );
		if( !$existing ) {
			return array( 'error' => tra( 'Filing schedule was not deleted.' ) );
		}
		$this->mDb->query( "DELETE FROM ".TABLE_TAX_FILING." WHERE `tax_title` = ?", array( $title ) );
		return array( 'success' => tra( 'Filing schedule deleted.' ) );
	}

	public function recordPayment( $pParameters ) {
		$this->prepare();
		$parsed = $this->paymentPeriod( $pParameters );
		if( isset( $parsed['error'] ) ) {
			return $parsed;
		}
		$amount = BitBase::getParameter( $pParameters, 'amount', '' );
		if( !is_numeric( $amount ) ) {
			return array( 'error' => tra( 'Payment amount is not a number.' ) );
		}
		$paidOn = $this->normalizeDate( BitBase::getParameter( $pParameters, 'paid_on', '' ) );
		if( $paidOn === NULL ) {
			return array( 'error' => tra( 'Payment date is required.' ) );
		}
		$reference = substr( trim( (string)BitBase::getParameter( $pParameters, 'reference', '' ) ), 0, 64 );
		$note = trim( (string)BitBase::getParameter( $pParameters, 'note', '' ) );
		global $gBitUser;
		$userId = ( is_object( $gBitUser ) ? (int)$gBitUser->mUserId : NULL );
		$title = $parsed['tax_title'];
		$start = $parsed['period_start'];
		$end = $parsed['period_end'];
		$this->mDb->StartTrans();
		$existing = $this->mDb->getOne(
			"SELECT `tax_payment_id` FROM ".TABLE_TAX_PAYMENTS." WHERE `tax_title` = ? AND `period_start` = ?",
			array( $title, $start )
		);
		if( $existing ) {
			$this->mDb->query(
				"UPDATE ".TABLE_TAX_PAYMENTS." SET `period_end` = ?, `amount` = ?, `paid_on` = ?, `reference` = ?, `note` = ?, `user_id` = ? WHERE `tax_payment_id` = ?",
				array( $end, $amount, $paidOn, $reference, $note, $userId, (int)$existing )
			);
		} else {
			$this->mDb->query(
				"INSERT INTO ".TABLE_TAX_PAYMENTS." (`tax_title`, `period_start`, `period_end`, `amount`, `paid_on`, `reference`, `note`, `user_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
				array( $title, $start, $end, $amount, $paidOn, $reference, $note, $userId )
			);
		}
		$this->mDb->CompleteTrans();
		return array( 'success' => tra( 'Payment recorded.' ) );
	}

	public function deletePayment( $pParameters ) {
		$this->prepare();
		$title = trim( (string)BitBase::getParameter( $pParameters, 'tax_title', '' ) );
		$start = $this->normalizeDate( BitBase::getParameter( $pParameters, 'period_start', '' ) );
		if( $title === '' || $start === NULL ) {
			return array( 'error' => tra( 'Payment was not deleted.' ) );
		}
		$this->mDb->query(
			"DELETE FROM ".TABLE_TAX_PAYMENTS." WHERE `tax_title` = ? AND `period_start` = ?",
			array( $title, $start )
		);
		return array( 'success' => tra( 'Payment deleted.' ) );
	}

	public function scheduleRows() {
		$this->prepare();
		$rows = $this->filingRows();
		foreach( array_keys( $rows ) as $i ) {
			$rows[$i]['rate_url'] = $this->rateUrl( $rows[$i]['tax_title'] );
		}
		return $rows;
	}

	public function unscheduledTitles() {
		$this->prepare();
		$saved = array();
		foreach( $this->filingRows() as $row ) {
			$saved[$row['tax_title']] = TRUE;
		}
		$titles = array();
		foreach( $this->knownTitles() as $title ) {
			if( empty( $saved[$title] ) ) {
				$titles[$title] = $title;
			}
		}
		return $titles;
	}

	public function getReport( $pYear ) {
		$this->prepare();
		$year = (int)$pYear;
		$schedules = array();
		foreach( $this->filingRows() as $row ) {
			$schedules[$row['tax_title']] = $row;
		}
		$titles = $this->knownTitles();
		$spans = array();
		foreach( $titles as $title ) {
			$frequency = isset( $schedules[$title] ) ? $schedules[$title]['frequency'] : 'quarterly';
			$month = isset( $schedules[$title] ) ? (int)$schedules[$title]['cycle_start_month'] : 1;
			foreach( self::periodsOverlappingYear( $year, $frequency, $month ) as $period ) {
				$spans[] = $period;
			}
		}
		if( empty( $spans ) ) {
			return array();
		}
		$starts = array();
		$ends = array();
		foreach( $spans as $period ) {
			$starts[] = $period['period_start'];
			$ends[] = $period['period_end'];
		}
		$sumsByPeriod = $this->aggregatePeriods( $this->windowsForTitles( $titles, $schedules, $year ) );
		$payments = $this->paymentsBetween( min( $starts ), max( $ends ) );
		$rows = array();
		foreach( $titles as $title ) {
			$frequency = isset( $schedules[$title] ) ? $schedules[$title]['frequency'] : 'quarterly';
			$month = isset( $schedules[$title] ) ? (int)$schedules[$title]['cycle_start_month'] : 1;
			$track = ( isset( $schedules[$title] ) ? $schedules[$title]['track_from'] : '' );
			foreach( self::periodsOverlappingYear( $year, $frequency, $month ) as $period ) {
				$key = $title.'|'.$period['period_start'];
				$sums = isset( $sumsByPeriod[$key] ) ? $sumsByPeriod[$key] : array( 'tax_collected' => '0.00', 'subtotal' => '0.00' );
				if( $this->moneyIsZero( $sums['tax_collected'] ) && $this->moneyIsZero( $sums['subtotal'] ) ) {
					continue;
				}
				$payment = isset( $payments[$key] ) ? $payments[$key] : NULL;
				$status = $this->periodStatus( $period, $track, $sums['tax_collected'], $payment, isset( $schedules[$title] ) );
				$rows[] = array(
					'tax_title' => $title,
					'period_start' => $period['period_start'],
					'period_end' => $period['period_end'],
					'label' => self::periodLabel( $period, $frequency, $month ),
					'subtotal' => $sums['subtotal'],
					'tax_collected' => $sums['tax_collected'],
					'status' => $status,
					'payment' => $payment,
					'can_pay' => ( $status === 'due' || $status === 'paid' ),
					'rate_url' => $this->rateUrl( $title ),
				);
			}
		}
		usort( $rows, array( $this, 'sortReportRows' ) );
		return $rows;
	}

	public function getDuePeriods() {
		$this->prepare();
		$filings = $this->filingRows();
		if( empty( $filings ) ) {
			return array();
		}
		$windows = array();
		$starts = array();
		$ends = array();
		foreach( $filings as $filing ) {
			if( empty( $filing['track_from'] ) ) {
				continue;
			}
			$period = self::periodContaining( date( 'Y-m-d' ), $filing['frequency'], (int)$filing['cycle_start_month'] );
			if( $period['period_end'] >= date( 'Y-m-d' ) ) {
				$period = self::previousPeriod( $period, $filing['frequency'], (int)$filing['cycle_start_month'] );
			}
			for( $i = 0; $i < self::MAX_DUE_PERIODS; $i++ ) {
				if( $period['period_start'] < $filing['track_from'] ) {
					break;
				}
				$windows[] = array(
					'tax_title' => $filing['tax_title'],
					'frequency' => $filing['frequency'],
					'cycle_start_month' => (int)$filing['cycle_start_month'],
					'period_start' => $period['period_start'],
					'period_end' => $period['period_end'],
				);
				$starts[] = $period['period_start'];
				$ends[] = $period['period_end'];
				$period = self::previousPeriod( $period, $filing['frequency'], (int)$filing['cycle_start_month'] );
			}
		}
		if( empty( $windows ) ) {
			return array();
		}
		$sumsByPeriod = $this->aggregatePeriods( $windows );
		$payments = $this->paymentsBetween( min( $starts ), max( $ends ) );
		$due = array();
		foreach( $windows as $window ) {
			$key = $window['tax_title'].'|'.$window['period_start'];
			$sums = isset( $sumsByPeriod[$key] ) ? $sumsByPeriod[$key] : array( 'tax_collected' => '0.00', 'subtotal' => '0.00' );
			if( $this->moneyIsZero( $sums['tax_collected'] ) || isset( $payments[$key] ) ) {
				continue;
			}
			$due[] = array(
				'tax_title' => $window['tax_title'],
				'period_start' => $window['period_start'],
				'period_end' => $window['period_end'],
				'label' => self::periodLabel( $window, $window['frequency'], $window['cycle_start_month'] ),
				'tax_collected' => $sums['tax_collected'],
			);
		}
		usort( $due, array( $this, 'sortReportRows' ) );
		return $due;
	}

	public static function periodContaining( $pDate, $pFrequency, $pStartMonth ) {
		$date = new DateTimeImmutable( substr( (string)$pDate, 0, 10 ) );
		$month = (int)$date->format( 'n' );
		$year = (int)$date->format( 'Y' );
		$startMonth = (int)$pStartMonth;
		if( $startMonth < 1 || $startMonth > 12 ) {
			$startMonth = 1;
		}
		if( $pFrequency === 'monthly' ) {
			$start = $date->modify( 'first day of this month' );
			$end = $start->modify( 'last day of this month' );
		} elseif( $pFrequency === 'annual' ) {
			$startYear = ( $month >= $startMonth ) ? $year : $year - 1;
			$start = new DateTimeImmutable( sprintf( '%04d-%02d-01', $startYear, $startMonth ) );
			$end = $start->modify( '+1 year' )->modify( '-1 day' );
		} else {
			$phase = ( ( $startMonth - 1 ) % 3 ) + 1;
			$monthPhase = ( ( $month - 1 ) % 3 ) + 1;
			$delta = $monthPhase - $phase;
			if( $delta < 0 ) {
				$delta += 3;
			}
			$start = $date->modify( 'first day of this month' )->modify( '-'.$delta.' months' );
			$end = $start->modify( '+3 months' )->modify( '-1 day' );
		}
		return array(
			'period_start' => $start->format( 'Y-m-d' ),
			'period_end' => $end->format( 'Y-m-d' ),
		);
	}

	public static function previousPeriod( $pPeriod, $pFrequency, $pStartMonth ) {
		$day = ( new DateTimeImmutable( $pPeriod['period_start'] ) )->modify( '-1 day' )->format( 'Y-m-d' );
		return self::periodContaining( $day, $pFrequency, $pStartMonth );
	}

	public static function periodsOverlappingYear( $pYear, $pFrequency, $pStartMonth ) {
		$cursor = self::periodContaining( sprintf( '%04d-01-01', (int)$pYear ), $pFrequency, $pStartMonth );
		$previous = self::previousPeriod( $cursor, $pFrequency, $pStartMonth );
		if( $previous['period_end'] >= sprintf( '%04d-01-01', (int)$pYear ) ) {
			$cursor = $previous;
		}
		$yearEnd = sprintf( '%04d-12-31', (int)$pYear );
		$periods = array();
		for( $i = 0; $i < 16 && $cursor['period_start'] <= $yearEnd; $i++ ) {
			$periods[] = $cursor;
			$nextDay = ( new DateTimeImmutable( $cursor['period_end'] ) )->modify( '+1 day' )->format( 'Y-m-d' );
			$cursor = self::periodContaining( $nextDay, $pFrequency, $pStartMonth );
		}
		return $periods;
	}

	public static function periodLabel( $pPeriod, $pFrequency, $pStartMonth ) {
		$startMonth = (int)substr( $pPeriod['period_start'], 5, 2 );
		if( $pFrequency === 'quarterly' && ( ( (int)$pStartMonth - 1 ) % 3 ) === 0 ) {
			$quarter = (int)ceil( $startMonth / 3 );
			return substr( $pPeriod['period_start'], 0, 4 ).'-Q'.$quarter;
		}
		return $pPeriod['period_start'].' – '.$pPeriod['period_end'];
	}

	public static function frequencyChoices() {
		return array(
			'quarterly' => tra( 'Quarterly' ),
			'monthly' => tra( 'Monthly' ),
			'annual' => tra( 'Annual' ),
		);
	}

	public static function monthChoices() {
		$names = array(
			1 => tra( 'January' ),
			2 => tra( 'February' ),
			3 => tra( 'March' ),
			4 => tra( 'April' ),
			5 => tra( 'May' ),
			6 => tra( 'June' ),
			7 => tra( 'July' ),
			8 => tra( 'August' ),
			9 => tra( 'September' ),
			10 => tra( 'October' ),
			11 => tra( 'November' ),
			12 => tra( 'December' ),
		);
		$names[1] .= ' ('.tra( 'Jan, Apr, Jul, Oct' ).')';
		$names[2] .= ' ('.tra( 'Feb, May, Aug, Nov' ).')';
		$names[3] .= ' ('.tra( 'Mar, Jun, Sep, Dec' ).')';
		return $names;
	}

	private function prepare() {
		self::ensureTables();
		$this->seedRecentFilings();
	}

	private static function ensureTables() {
		if( self::$tablesReady ) {
			return;
		}
		global $gBitDb;
		$tables = array();
		if( !$gBitDb->tableExists( 'com_tax_filing' ) ) {
			$tables['com_tax_filing'] = "
  tax_filing_id I4 PRIMARY AUTO,
  tax_title C(255) NOTNULL,
  frequency C(16) NOTNULL,
  cycle_start_month I1 NOTNULL,
  track_from D,
  last_modified T DEFTIMESTAMP,
  date_added T DEFTIMESTAMP
";
		}
		if( !$gBitDb->tableExists( 'com_tax_payments' ) ) {
			$tables['com_tax_payments'] = "
  tax_payment_id I4 PRIMARY AUTO,
  tax_title C(255) NOTNULL,
  period_start D NOTNULL,
  period_end D NOTNULL,
  amount N(15,4),
  paid_on D,
  reference C(64),
  note X,
  user_id I4,
  date_added T DEFTIMESTAMP
";
		}
		if( !empty( $tables ) ) {
			$gBitDb->createTables( $tables );
		}
		self::ensureIndex( 'com_tax_filing', 'tax_filing_title_idx', 'tax_title', TRUE );
		self::ensureIndex( 'com_tax_payments', 'tax_payment_period_idx', 'tax_title, period_start', TRUE );
		self::$tablesReady = TRUE;
	}

	private static function ensureIndex( $pTable, $pName, $pColumns, $pUnique ) {
		global $gBitDb;
		$existing = $gBitDb->mDb->MetaIndexes( $pTable );
		if( is_array( $existing ) ) {
			foreach( array_keys( $existing ) as $name ) {
				if( strcasecmp( (string)$name, $pName ) === 0 ) {
					return;
				}
			}
		}
		$dict = NewDataDictionary( $gBitDb->mDb );
		$sql = $dict->CreateIndexSQL( $pName, $pTable, $pColumns, ( $pUnique ? array( 'UNIQUE' ) : array() ) );
		if( $sql ) {
			$dict->ExecuteSQLArray( $sql );
		}
	}

	private function seedRecentFilings() {
		$existing = (int)$this->mDb->getOne( "SELECT COUNT(*) FROM ".TABLE_TAX_FILING );
		if( $existing > 0 ) {
			return;
		}
		$current = self::periodContaining( date( 'Y-m-d' ), 'quarterly', 1 );
		$previous = self::previousPeriod( $current, 'quarterly', 1 );
		$titles = $this->mDb->query(
			"SELECT DISTINCT cot.`title` AS tax_title
			 FROM ".TABLE_ORDERS_TOTAL." cot
			 INNER JOIN ".TABLE_ORDERS." co ON (cot.`orders_id` = co.`orders_id`)
			 WHERE cot.`class` = 'ot_tax'
			   AND co.`orders_status_id` > 3
			   AND co.`date_purchased` >= ?
			   AND co.`date_purchased` < ?
			   AND cot.`title` <> ''
			   AND cot.`title` NOT IN (SELECT `tax_title` FROM ".TABLE_TAX_FILING.")",
			array( $previous['period_start'], self::dayAfter( $current['period_end'] ) )
		);
		while( $titles && !$titles->EOF ) {
			$title = $titles->fields['tax_title'];
			$this->mDb->query(
				"INSERT INTO ".TABLE_TAX_FILING." (`tax_title`, `frequency`, `cycle_start_month`, `track_from`) VALUES (?, 'quarterly', 1, ?)",
				array( $title, $previous['period_start'] )
			);
			$titles->MoveNext();
		}
	}

	private function knownTitles() {
		$titles = array();
		$rs = $this->mDb->query(
			"SELECT `tax_description` AS tax_title FROM ".TABLE_TAX_RATES." WHERE `tax_description` IS NOT NULL AND `tax_description` <> ''
			 UNION
			 SELECT `tax_title` AS tax_title FROM ".TABLE_TAX_FILING."
			 UNION
			 SELECT DISTINCT `title` AS tax_title FROM ".TABLE_ORDERS_TOTAL." WHERE `class` = 'ot_tax' AND `title` IS NOT NULL AND `title` <> ''
			 ORDER BY tax_title"
		);
		while( $rs && !$rs->EOF ) {
			$titles[] = $rs->fields['tax_title'];
			$rs->MoveNext();
		}
		return $titles;
	}

	private function rateUrl( $pTitle ) {
		if( $this->rateUrls === NULL ) {
			$this->rateUrls = array();
			$rs = $this->mDb->query(
				"SELECT `tax_rates_id`, `tax_description` FROM ".TABLE_TAX_RATES." WHERE `tax_description` IS NOT NULL AND `tax_description` <> '' ORDER BY `tax_rates_id`"
			);
			while( $rs && !$rs->EOF ) {
				$title = $rs->fields['tax_description'];
				if( !isset( $this->rateUrls[$title] ) ) {
					$this->rateUrls[$title] = zen_href_link_admin( FILENAME_TAX_RATES, 'page=1&tID='.(int)$rs->fields['tax_rates_id'] );
				}
				$rs->MoveNext();
			}
		}
		return isset( $this->rateUrls[$pTitle] ) ? $this->rateUrls[$pTitle] : '';
	}

	private function filingRows() {
		$rows = array();
		$rs = $this->mDb->query( "SELECT `tax_title`, `frequency`, `cycle_start_month`, `track_from` FROM ".TABLE_TAX_FILING." ORDER BY `tax_title`" );
		while( $rs && !$rs->EOF ) {
			$row = $rs->fields;
			$row['frequency'] = $this->normalizeFrequency( $row['frequency'] );
			if( $row['frequency'] === NULL ) {
				$row['frequency'] = 'quarterly';
			}
			$row['cycle_start_month'] = (int)$row['cycle_start_month'];
			$row['track_from'] = ( empty( $row['track_from'] ) ? '' : substr( $row['track_from'], 0, 10 ) );
			$rows[] = $row;
			$rs->MoveNext();
		}
		return $rows;
	}

	private function windowsForTitles( $pTitles, $pSchedules, $pYear ) {
		$windows = array();
		foreach( $pTitles as $title ) {
			$frequency = isset( $pSchedules[$title] ) ? $pSchedules[$title]['frequency'] : 'quarterly';
			$month = isset( $pSchedules[$title] ) ? (int)$pSchedules[$title]['cycle_start_month'] : 1;
			foreach( self::periodsOverlappingYear( $pYear, $frequency, $month ) as $period ) {
				$windows[] = array(
					'tax_title' => $title,
					'period_start' => $period['period_start'],
					'period_end' => $period['period_end'],
				);
			}
		}
		return $windows;
	}

	private function aggregatePeriods( $pWindows ) {
		$map = array();
		if( empty( $pWindows ) ) {
			return $map;
		}
		$cases = array();
		$binds = array();
		$minStart = NULL;
		$maxEnd = NULL;
		foreach( $pWindows as $window ) {
			$endExclusive = self::dayAfter( $window['period_end'] );
			$cases[] = "WHEN cot.`title` = ? AND co.`date_purchased` >= ? AND co.`date_purchased` < ? THEN ?";
			$binds[] = $window['tax_title'];
			$binds[] = $window['period_start'];
			$binds[] = $endExclusive;
			$binds[] = $window['tax_title'].'|'.$window['period_start'];
			if( $minStart === NULL || $window['period_start'] < $minStart ) {
				$minStart = $window['period_start'];
			}
			if( $maxEnd === NULL || $endExclusive > $maxEnd ) {
				$maxEnd = $endExclusive;
			}
		}
		$binds[] = $minStart;
		$binds[] = $maxEnd;
		$rs = $this->mDb->query(
			"SELECT CASE ".implode( ' ', $cases )." END AS period_key,
			        round( sum( cot.`orders_value` ), 2 ) AS tax_collected,
			        round( sum( cot2.`orders_value` ), 2 ) AS subtotal
			 FROM ".TABLE_ORDERS_TOTAL." cot
			 INNER JOIN ".TABLE_ORDERS." co ON (cot.`orders_id` = co.`orders_id`)
			 INNER JOIN ".TABLE_ORDERS_TOTAL." cot2 ON (cot.`orders_id` = cot2.`orders_id` AND cot2.`class` = 'ot_subtotal')
			 WHERE cot.`class` = 'ot_tax'
			   AND co.`orders_status_id` > 3
			   AND co.`date_purchased` >= ?
			   AND co.`date_purchased` < ?
			 GROUP BY period_key",
			$binds
		);
		while( $rs && !$rs->EOF ) {
			$key = $rs->fields['period_key'];
			if( $key !== NULL && $key !== '' ) {
				$map[$key] = array(
					'tax_collected' => $rs->fields['tax_collected'],
					'subtotal' => $rs->fields['subtotal'],
				);
			}
			$rs->MoveNext();
		}
		return $map;
	}

	private function paymentsBetween( $pStart, $pEnd ) {
		$map = array();
		$rs = $this->mDb->query(
			"SELECT `tax_payment_id`, `tax_title`, `period_start`, `period_end`, `amount`, `paid_on`, `reference`, `note`
			 FROM ".TABLE_TAX_PAYMENTS."
			 WHERE `period_start` >= ? AND `period_start` <= ?",
			array( $pStart, $pEnd )
		);
		while( $rs && !$rs->EOF ) {
			$row = $rs->fields;
			$row['period_start'] = substr( (string)$row['period_start'], 0, 10 );
			$row['period_end'] = substr( (string)$row['period_end'], 0, 10 );
			$row['paid_on'] = ( empty( $row['paid_on'] ) ? '' : substr( (string)$row['paid_on'], 0, 10 ) );
			$map[$row['tax_title'].'|'.$row['period_start']] = $row;
			$rs->MoveNext();
		}
		return $map;
	}

	private function paymentPeriod( $pParameters ) {
		$title = trim( (string)BitBase::getParameter( $pParameters, 'tax_title', '' ) );
		$start = $this->normalizeDate( BitBase::getParameter( $pParameters, 'period_start', '' ) );
		if( $title === '' || $start === NULL ) {
			return array( 'error' => tra( 'Payment was not recorded.' ) );
		}
		$filing = NULL;
		foreach( $this->filingRows() as $row ) {
			if( $row['tax_title'] === $title ) {
				$filing = $row;
				break;
			}
		}
		if( $filing === NULL ) {
			return array( 'error' => tra( 'Set a filing schedule before recording a payment.' ) );
		}
		$period = self::periodContaining( $start, $filing['frequency'], (int)$filing['cycle_start_month'] );
		if( $period['period_start'] !== $start ) {
			return array( 'error' => tra( 'That date is not the start of a filing period.' ) );
		}
		if( $period['period_end'] >= date( 'Y-m-d' ) ) {
			return array( 'error' => tra( 'The open period cannot be marked paid.' ) );
		}
		return array(
			'tax_title' => $title,
			'period_start' => $period['period_start'],
			'period_end' => $period['period_end'],
		);
	}

	private function periodStatus( $pPeriod, $pTrackFrom, $pTax, $pPayment, $pScheduled ) {
		if( !empty( $pPayment ) ) {
			return 'paid';
		}
		if( $pPeriod['period_end'] >= date( 'Y-m-d' ) ) {
			return 'open';
		}
		if( !$pScheduled || $pTrackFrom === '' || $pPeriod['period_start'] < $pTrackFrom || $this->moneyIsZero( $pTax ) ) {
			return 'closed';
		}
		return 'due';
	}

	private function normalizeFrequency( $pFrequency ) {
		$frequency = (string)$pFrequency;
		if( $frequency === 'quarterly' || $frequency === 'monthly' || $frequency === 'annual' ) {
			return $frequency;
		}
		return NULL;
	}

	private function normalizeMonth( $pMonth, $pFrequency ) {
		$month = (int)$pMonth;
		if( $month < 1 || $month > 12 ) {
			return NULL;
		}
		if( $pFrequency === 'monthly' ) {
			return 1;
		}
		if( $pFrequency === 'quarterly' ) {
			return ( ( $month - 1 ) % 3 ) + 1;
		}
		return $month;
	}

	private function normalizeDate( $pDate ) {
		$date = trim( (string)$pDate );
		if( $date === '' ) {
			return NULL;
		}
		if( !preg_match( '/^(\d{4})-(\d{2})-(\d{2})/', $date, $match ) ) {
			return NULL;
		}
		if( !checkdate( (int)$match[2], (int)$match[3], (int)$match[1] ) ) {
			return NULL;
		}
		return $match[1].'-'.$match[2].'-'.$match[3];
	}

	private function moneyIsZero( $pAmount ) {
		return abs( (float)$pAmount ) < 0.005;
	}

	private function sortReportRows( $pLeft, $pRight ) {
		if( $pLeft['period_start'] === $pRight['period_start'] ) {
			return strcmp( $pLeft['tax_title'], $pRight['tax_title'] );
		}
		return strcmp( $pRight['period_start'], $pLeft['period_start'] );
	}

	private static function dayAfter( $pDate ) {
		return ( new DateTimeImmutable( $pDate ) )->modify( '+1 day' )->format( 'Y-m-d' );
	}

}
