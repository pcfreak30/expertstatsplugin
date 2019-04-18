<?php


namespace Codeable\ExpertStats;


use Codeable\ExpertStats\Core\Component;

class Stats extends Component {

	/**
	 * @param int    $from_day
	 * @param int    $from_month
	 * @param int    $from_year
	 * @param int    $to_day
	 * @param int    $to_month
	 * @param int    $to_year
	 * @param string $chart_display_method
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	public function get_all_stats( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year, $chart_display_method ) {
		$stats = [];

		$averages        = $this->get_months_average( $from_month, $from_year, $to_month, $to_year );
		$preferred_count = $this->get_preferred_count( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year );
		$amounts_range   = $this->get_amounts_range( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year );

		$chart_amounts_range = [];
		$available_ranges    = [];
		foreach ( $amounts_range as $range => $num_of_tasks ) {
			$chart_amounts_range[] = [ $range => $num_of_tasks ];
			$available_ranges[]    = $range;
		}

		$tasks_type = $this->get_tasks_type( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year );

		$type_categories     = [];
		$type_contractor_fee = [];
		$type_revenue        = [];
		$type_tasks_count    = [];

		foreach ( $tasks_type as $type => $type_data ) {

			$type_categories[ $type ]     = $type;
			$type_contractor_fee[ $type ] = (float) $type_data['fee'];
			$type_revenue[ $type ]        = (float) $type_data['revenue'];
			$type_tasks_count[ $type ]    = (int) $type_data['count'];
		}

		$type_tasks_count_json = wp_json_encode( $type_tasks_count );

		if ( $chart_display_method === 'months' ) {

			$month_totals = $this->get_month_range_totals( $from_month, $from_year, $to_month, $to_year );

			$max_month_totals     = max( $month_totals );
			$max_month_totals_key = array_keys( $month_totals, max( $month_totals ) );

			$all_month_totals               = [];
			$all_month_totals['revenue']    = array_sum( array_column( $month_totals, 'revenue' ) );
			$all_month_totals['total_cost'] = array_sum( array_column( $month_totals, 'total_cost' ) );

			$chart_categories      = [];
			$chart_dates           = [];
			$chart_contractor_fee  = [];
			$chart_revenue         = [];
			$chart_revenue_avg     = [];
			$chart_total_cost      = [];
			$chart_tasks_count     = [];
			$chart_tasks_count_avg = [];

			foreach ( $month_totals as $yearmonth => $amounts ) {
				$chart_categories[ $yearmonth ]     = wordwrap( $yearmonth, 4, '-', true );
				$chart_dates[]                      = wordwrap( $yearmonth, 4, '-', true );
				$chart_contractor_fee[ $yearmonth ] = (float) $amounts['fee_amount'];
				$chart_revenue[ $yearmonth ]        = (float) $amounts['revenue'];
				$chart_total_cost[ $yearmonth ]     = (float) $amounts['total_cost'];
				$chart_tasks_count[ $yearmonth ]    = (int) $amounts['tasks'];

			}

			$chart_tasks_count_json = wp_json_encode( $chart_tasks_count );
			$chart_revenue_json     = wp_json_encode( $chart_revenue );

		} else {

			$days_totals = $this->get_days( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year );


			$max_month_totals        = max( $days_totals );
			$max_month_totals_key    = array_keys( $days_totals, max( $days_totals ) );
			$max_month_totals_key[0] = wordwrap( $max_month_totals_key[0], 6, '-', true );

			$all_month_totals = [];
			foreach ( $days_totals as $mt ) {
				if ( ! isset( $all_month_totals['revenue'] ) ) {
					$all_month_totals['revenue'] = 0;
				}
				if ( ! isset( $all_month_totals['total_cost'] ) ) {
					$all_month_totals['total_cost'] = 0;
				}

				$all_month_totals['revenue']    += $mt['revenue'];
				$all_month_totals['total_cost'] += $mt['total_cost'];
			}

			$chart_categories      = [];
			$chart_dates           = [];
			$chart_contractor_fee  = [];
			$chart_revenue         = [];
			$chart_revenue_avg     = [];
			$chart_total_cost      = [];
			$chart_tasks_count     = [];
			$chart_tasks_count_avg = [];

			foreach ( $days_totals as $yearmonthday => $amounts ) {

				$date_array = date_parse_from_format( 'Ymd', $yearmonthday );

				$chart_categories[ $yearmonthday ]     = $date_array['year'] . '-' . sprintf( '%02d', $date_array['month'] ) . '-' . sprintf( '%02d', $date_array['day'] );
				$chart_dates[]                         = $date_array['year'] . '-' . sprintf( '%02d', $date_array['month'] ) . '-' . sprintf( '%02d', $date_array['day'] );
				$chart_contractor_fee[ $yearmonthday ] = (float) $amounts['fee_amount'];
				$chart_revenue[ $yearmonthday ]        = (float) $amounts['revenue'];
				$chart_total_cost[ $yearmonthday ]     = (float) $amounts['total_cost'];
				$chart_tasks_count[ $yearmonthday ]    = (int) $amounts['tasks'];
			}


			$chart_tasks_count_json = wp_json_encode( $chart_tasks_count );
			$chart_revenue_json     = wp_json_encode( $chart_revenue );

		}

		$chart_dates_json = wp_json_encode( $chart_dates );

		$fromDT = new \DateTime( $from_year . '-' . $from_month . '-' . $from_day );
		$toDT   = new \DateTime( $to_year . '-' . $to_month . '-' . $to_day );

		$datediff = date_diff( $fromDT, $toDT );

		if ( $chart_display_method === 'months' ) {
			$datediffcount = $datediff->format( '%m' ) + ( $datediff->format( '%y' ) * 12 ) + 1;
		}
		if ( $chart_display_method === 'days' ) {
			$datediffcount = $datediff->format( '%a' );
		}

		$chart_revenue_avg     = array_fill( 0, count( $chart_revenue ), round( array_sum( $chart_revenue ) / $datediffcount, 2 ) );
		$chart_tasks_count_avg = array_fill( 0, count( $chart_tasks_count ), round( array_sum( $chart_tasks_count ) / $datediffcount, 2 ) );

		$stats['averages']               = $averages;
		$stats['preferred_count']        = $preferred_count;
		$stats['chart_amounts_range']    = $chart_amounts_range;
		$stats['get_available_ranges']   = $available_ranges;
		$stats['type_categories']        = $type_categories;
		$stats['type_contractor_fee']    = $type_contractor_fee;
		$stats['type_revenue']           = $type_revenue;
		$stats['type_tasks_count']       = $type_tasks_count;
		$stats['type_tasks_count_json']  = $type_tasks_count_json;
		$stats['max_month_totals']       = $max_month_totals;
		$stats['max_month_totals_key']   = $max_month_totals_key;
		$stats['all_month_totals']       = $all_month_totals;
		$stats['chart_categories']       = $chart_categories;
		$stats['chart_dates']            = $chart_dates;
		$stats['chart_dates_json']       = $chart_dates_json;
		$stats['chart_contractor_fee']   = $chart_contractor_fee;
		$stats['chart_revenue']          = $chart_revenue;
		$stats['chart_revenue_avg']      = $chart_revenue_avg;
		$stats['chart_total_cost']       = $chart_total_cost;
		$stats['chart_tasks_count']      = $chart_tasks_count;
		$stats['chart_tasks_count_avg']  = $chart_tasks_count_avg;
		$stats['chart_tasks_count_json'] = $chart_tasks_count_json;
		$stats['chart_revenue_json']     = $chart_revenue_json;

		return $stats;
	}

	/**
	 * @param $from_month
	 * @param $from_year
	 * @param $to_month
	 * @param $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_months_average( $from_month, $from_year, $to_month, $to_year ) {
		$month_range_totals = $this->get_month_range_totals( $from_month, $from_year, $to_month, $to_year );

		$fee_amount = $contractor_fee = $revenue = $total_cost = $tasks = [];

		foreach ( $month_range_totals as $month ) {

			$fee_amount[]     = $month['fee_amount'];
			$contractor_fee[] = $month['contractor_fee'];
			$revenue[]        = $month['revenue'];
			$total_cost[]     = $month['total_cost'];
			$tasks[]          = $month['tasks'];

		}

		$averages = array(
			'fee_amount'     => round( array_sum( $fee_amount ) / count( $fee_amount ), 2 ),
			'contractor_fee' => round( array_sum( $contractor_fee ) / count( $contractor_fee ), 2 ),
			'revenue'        => round( array_sum( $revenue ) / count( $revenue ), 2 ),
			'total_cost'     => round( array_sum( $total_cost ) / count( $total_cost ), 2 ),
		);

		return $averages;
	}

	/**
	 * @param string $from_month
	 * @param string $from_year
	 * @param string $to_month
	 * @param string $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	private function get_month_range_totals( $from_month = null, $from_year = null, $to_month = null, $to_year = null ) {

		$totals = [];

		$first_date = '';
		$last_date  = '';

		// get first and last task if no date is set
		if ( null === $from_month && null === $from_year ) {
			$first_task = $this->get_first_task();
			if ( ! $first_task ) {
				$first_date = date( 'now' );
			} else {
				$first_date = $first_task['dateadded'];
			}
		} else {
			$first_date = $from_year . '-' . $from_month . '-01';
		}
		if ( null === $to_month && null === $to_year ) {
			$last_task = $this->get_last_task();
			if ( ! $first_task ) {
				$last_date = date( 'now' );
			} else {
				$last_date = $last_task['dateadded'];
			}
		} else {
			$last_date = $to_year . '-' . $to_month . '-' . date( 't', strtotime( $to_year . '-' . $to_month . '-01 23:59:59' ) );
		}


		$begin = new \DateTime( $first_date );
		$end   = new \DateTime( $last_date );

		$interval = \DateInterval::createFromDateString( '1 month' );
		$period   = new \DatePeriod( $begin, $interval, $end );

		/** @var \DateTime $dt */
		foreach ( $period as $dt ) {
			$totals[ $dt->format( 'Ym' ) ] = $this->get_month_totals( $dt->format( 'm' ), $dt->format( 'Y' ) );

		}

		return $totals;

	}

	/**
	 * @return bool|mixed
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	public function get_first_task() {
		$transaction_table = $this->plugin->models_manager->transaction->table->full_name;
		$query             = $this->get_single_task();

		$query->order_by( "{$transaction_table}.id" );

		$collection = $query->execute();
		if ( ! $collection->get_count() ) {
			return false;
		}

		return $collection->current();
	}

	/**
	 * @return \ComposePress\Models\Query\Builder
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_single_task() {
		$query             = $this->get_query();
		$transaction_table = $this->get_transaction_table_name();
		$amount_table      = $this->get_amount_table_name();

		$query
			->select()
			->where( "{$transaction_table}.description", '=', 'task_completion' )
			->limit( 0, 1 );

		return $query;
	}

	/**
	 * @param null $from_day
	 * @param null $from_month
	 * @param null $from_year
	 * @param null $to_day
	 * @param null $to_month
	 * @param null $to_year
	 *
	 * @return \ComposePress\Models\Query\Builder
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_query( $from_day = null, $from_month = null, $from_year = null, $to_day = null, $to_month = null, $to_year = null ) {
		$range = true;

		if ( null === $from_day && null === $from_month && null === $from_year && null === $to_day && null === $to_month && null === $to_year ) {
			$range = false;
		}

		if ( $range ) {
			$first_date = date( 'Y-m-d H:i:s', strtotime( $from_year . '-' . $from_month . '-' . $from_day ) );
			$last_date  = date( 'Y-m-d H:i:s', strtotime( $to_year . '-' . $to_month . '-' . $to_day . ' 23:59:59' ) );
		}


		$query             = $this->plugin->models_manager->transaction->query();
		$transaction_table = $this->get_transaction_table_name();
		$amount_table      = $this->get_amount_table_name();

		$join_condition = $query->new_condition();
		$join_condition->add_join_condition( "{$transaction_table}.task_id", '=', "{$amount_table}.task_id" );

		if ( $range ) {
			$dateadded_condition = $query->new_condition();
			$dateadded_condition->add_between_field_condition( 'dateadded', $first_date, $last_date );
		}

		$query
			->join( $amount_table, $join_condition, 'amounttbl', 'LEFT' )
			->where( "{$transaction_table}.description", '=', 'task_completion' );

		if ( $range ) {
			$query->where( $dateadded_condition );
		}

		return $query;
	}

	private function get_transaction_table_name() {
		return $this->plugin->models_manager->transaction->table->full_name;
	}

	private function get_amount_table_name() {
		return $this->plugin->models_manager->amount->table->full_name;
	}

	/**
	 * @return bool|array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	public function get_last_task() {
		$query = $this->get_single_task();

		$query->order_by( "{$this->get_transaction_table_name()}.id", 'DESC' );

		$collection = $query->execute();
		if ( ! $collection->get_count() ) {
			return false;
		}

		return $collection->current();
	}

	/**
	 * @param $month
	 * @param $year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_month_totals( $month, $year ) {

		$to_day = date( 't', strtotime( $year . '-' . $month . '-01 23:59:59' ) );

		return $this->get_dates_totals( '01', $month, $year, $to_day, $month, $year );
	}

	/**
	 * @param int $from_day
	 * @param int $from_month
	 * @param int $from_year
	 * @param int $to_day
	 * @param int $to_month
	 * @param int $to_year
	 *
	 * @return mixed
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_dates_totals( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year ) {
		$query = $this->get_query( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year );
		$query
			->select_raw( 'SUM(fee_amount) as fee_amount' )
			->select_raw( 'SUM(credit_fee_amount) as contractor_fee' )
			->select_raw( 'SUM(credit_revenue_amount) as revenue' )
			->select_raw( 'SUM(debit_user_amount) as total_cost' )
			->select_raw( ' count(1) as tasks' )
			->raw();

		$result = (array) $query->execute();

		return (array) array_shift( $result );
	}

	/**
	 * @param $from_day
	 * @param $from_month
	 * @param $from_year
	 * @param $to_day
	 * @param $to_month
	 * @param $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_preferred_count( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year ) {
		$first_date = date( 'Y-m-d H:i:s', strtotime( $from_year . '-' . $from_month . '-' . $from_day ) );
		$last_date  = date( 'Y-m-d H:i:s', strtotime( $to_year . '-' . $to_month . '-' . $to_day . ' 23:59:59' ) );

		$query             = $this->plugin->models_manager->transaction->query();
		$transaction_table = $this->plugin->models_manager->transaction->table->full_name;
		$amount_table      = $this->plugin->models_manager->amount->table->full_name;

		$join_condition = $query->new_condition();
		$join_condition->add_join_condition( "{$transaction_table}.task_id", '=', "{$amount_table}.task_id" );
		$preferred_condition       = $query->new_condition();
		$dateadded_condition       = $query->new_condition();
		$preferred_condition->type = 'OR';
		$preferred_condition->add_field_condition( 'preferred', '=', 1 );
		$preferred_condition->add_field_condition( 'preferred', '=', 0 );
		$dateadded_condition->add_between_field_condition( 'dateadded', $first_date, $last_date );
		$query
			->select( 'preferred' )
			->select_raw( 'COUNT(id) as count' )
			->select_raw( 'SUM(debit_user_amount) as user_amount' )
			->select_raw( 'SUM(credit_revenue_amount) as revenue' )
			->select_raw( 'SUM(credit_fee_amount) as fee' )
			->join( $this->plugin->models_manager->amount->get_table()->get_full_name(), $join_condition, 'amounttbl', 'LEFT' )
			->where( "{$transaction_table}.description", '=', 'task_completion' )
			->where( $preferred_condition )
			->where( $dateadded_condition )
			->raw();

		return $query->execute();
	}

	/**
	 * @param $from_day
	 * @param $from_month
	 * @param $from_year
	 * @param $to_day
	 * @param $to_month
	 * @param $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_amounts_range( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year ) {
		$first_date = date( 'Y-m-d H:i:s', strtotime( $from_year . '-' . $from_month . '-' . $from_day ) );
		$last_date  = date( 'Y-m-d H:i:s', strtotime( $to_year . '-' . $to_month . '-' . $to_day . ' 23:59:59' ) );

		$query             = $this->plugin->models_manager->transaction->query();
		$transaction_table = $this->plugin->models_manager->transaction->table->full_name;
		$amount_table      = $this->plugin->models_manager->amount->table->full_name;

		$join_condition = $query->new_condition();
		$join_condition->add_join_condition( "{$transaction_table}.task_id", '=', "{$amount_table}.task_id" );
		$dateadded_condition = $query->new_condition();
		$dateadded_condition->add_between_field_condition( 'dateadded', $first_date, $last_date );

		$query
			->select( 'credit_revenue_amount' )
			->join( $this->plugin->models_manager->amount->get_table()->get_full_name(), $join_condition, 'amounttbl', 'LEFT' )
			->where( "{$transaction_table}.description", '=', 'task_completion' )
			->where( $dateadded_condition )
			->raw();

		$results = $query->execute();

		$variance   = [
			'0-100'       => 0,
			'100-300'     => 0,
			'300-500'     => 0,
			'500-1000'    => 0,
			'1000-3000'   => 0,
			'3000-5000'   => 0,
			'5000-10000'  => 0,
			'10000-20000' => 0,
		];
		$milestones = [ 0, 100, 300, 500, 1000, 3000, 5000, 10000, 20000 ];

		foreach ( $results as $amount ) {
			$revenue = $amount['credit_revenue_amount'];
			foreach ( $milestones as $index => $milestone ) {
				if ( $revenue > $milestones[ $index ] && isset( $milestones[ $index + 1 ] ) && $revenue <= $milestones[ $index + 1 ] ) {
					$variance[ $milestones[ $index ] . '-' . $milestones[ $index + 1 ] ] ++;
				}
			}
		}

		return $variance;
	}

	/**
	 * @param $from_day
	 * @param $from_month
	 * @param $from_year
	 * @param $to_day
	 * @param $to_month
	 * @param $to_year
	 *
	 * @return array|false
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_tasks_type( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year ) {
		$first_date = date( 'Y-m-d H:i:s', strtotime( $from_year . '-' . $from_month . '-' . $from_day ) );
		$last_date  = date( 'Y-m-d H:i:s', strtotime( $to_year . '-' . $to_month . '-' . $to_day . ' 23:59:59' ) );

		$query             = $this->plugin->models_manager->transaction->query();
		$transaction_table = $this->plugin->models_manager->transaction->table->full_name;
		$amount_table      = $this->plugin->models_manager->amount->table->full_name;

		$join_condition = $query->new_condition();
		$join_condition->add_join_condition( "{$transaction_table}.task_id", '=', "{$amount_table}.task_id" );
		$dateadded_condition = $query->new_condition();
		$dateadded_condition->add_between_field_condition( 'dateadded', $first_date, $last_date );

		$query
			->select( 'task_type' )
			->select_raw( 'COUNT(id) as count' )
			->select_raw( ' SUM(debit_user_amount) as user_amount' )
			->select_raw( 'SUM(credit_fee_amount) as fee' )
			->join( $this->plugin->models_manager->amount->get_table()->get_full_name(), $join_condition, 'amounttbl', 'LEFT' )
			->where( "{$transaction_table}.description", '=', 'task_completion' )
			->where( $dateadded_condition )
			->group_by( 'task_type' )
			->raw();

		$results = $query->execute();

		$results = array_filter( $results, function ( $item ) {
			return ! empty( $item['revenue'] );
		} );

		return array_combine( array_column( $results, 'task_type' ), $results );
	}

	/**
	 * @param $from_day
	 * @param $from_month
	 * @param $from_year
	 * @param $to_day
	 * @param $to_month
	 * @param $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	private function get_days( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year ) {
		$first_date = date( 'Y-m-d H:i:s', strtotime( $from_year . '-' . $from_month . '-' . $from_day ) );
		$last_date  = date( 'Y-m-d H:i:s', strtotime( $to_year . '-' . $to_month . '-' . $to_day . ' 23:59:59' ) );

		$query             = $this->plugin->models_manager->transaction->query();
		$transaction_table = $this->plugin->models_manager->transaction->table->full_name;
		$amount_table      = $this->plugin->models_manager->amount->table->full_name;

		$join_condition = $query->new_condition();
		$join_condition->add_join_condition( "{$transaction_table}.task_id", '=', "{$amount_table}.task_id" );
		$dateadded_condition = $query->new_condition();
		$dateadded_condition->add_between_field_condition( 'dateadded', $first_date, $last_date );

		$query
			->select( 'fee_amount' )
			->select( 'credit_fee_amount' )
			->select( 'credit_revenue_amount' )
			->select( 'debit_user_amount' )
			->select( 'dateadded' )
			->join( $this->plugin->models_manager->amount->get_table()->get_full_name(), $join_condition, 'amounttbl', 'LEFT' )
			->where( "{$transaction_table}.description", '=', 'task_completion' )
			->where( $dateadded_condition )
			->raw();

		$results = $query->execute();

		$days_totals = [];
		foreach ( $results as $single_payment ) {
			$datekey = date( 'Ymd', strtotime( $single_payment['dateadded'] ) );
			if ( isset( $days_totals[ $datekey ] ) ) {
				$days_totals[ $datekey ]['fee_amount']     += $single_payment['fee_amount'];
				$days_totals[ $datekey ]['contractor_fee'] += $single_payment['credit_fee_amount'];
				$days_totals[ $datekey ]['revenue']        += $single_payment['credit_revenue_amount'];
				$days_totals[ $datekey ]['total_cost']     += $single_payment['debit_user_amount'];
				$days_totals[ $datekey ]['tasks'] ++;
			} else {
				$days_totals[ $datekey ]          = $single_payment;
				$days_totals[ $datekey ]['tasks'] = 1;
			}
		}

		return $days_totals;
	}

	/**
	 * @param $year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	public function get_year_totals( $year ) {
		return $this->get_dates_totals( '01', '01', $year, '31', '12', $year );
	}

	/**
	 * @param null|int $from_month
	 * @param null|int $from_year
	 * @param null|int $to_month
	 * @param null|int $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	public function get_clients( $from_month = null, $from_year = null, $to_month = null, $to_year = null ) {

		$clients = [];

		$first_date = '';
		$last_date  = '';

		// get first and last task if no date is set
		if ( null === $from_month && null === $from_year ) {
			$first_task = $this->get_first_task();
			$first_date = $first_task['dateadded'];
		} else {
			$first_date = $from_year . '-' . $from_month . '-01';
		}
		if ( null === $to_month && null === $to_year ) {
			$last_task = $this->get_last_task();
			$last_date = $last_task['dateadded'];
		} else {
			$last_date = $to_year . '-' . $to_month . '-' . date( 't', strtotime( $to_year . '-' . $to_month . '-01 23:59:59' ) );
		}

		$query          = $this->get_query( 1, $from_month, $from_year, 1, $to_month, $to_year );
		$join_condition = $query->new_condition();
		$join_condition->add_join_condition( "{$this->get_transaction_table_name()}.client_id", '=', "{$this->get_client_table_name()}.client_id" );

		$dateadded_condition = $query->new_condition();
		$dateadded_condition->add_between_field_condition( 'dateadded', $first_date, $last_date );

		$description_condition = $query->new_condition();
		$description_condition->add_field_condition( 'description', '=', 'task_completion' );
		$description_condition->add_field_condition( 'description', '=', 'partial_refund' );
		$description_condition->type = 'OR';

		$query
			->select()
			->join( $this->get_client_table_name(), $join_condition, 'clienttbl' )
			->clear_wheres()
			->where( $dateadded_condition )
			->where( $description_condition )
			->raw();

		$results = $query->execute();

		// loop transactions
		foreach ( $results as $result ) {
			$clients['clients'][ $result['client_id'] ]['client_id']       = $result['client_id'];
			$clients['clients'][ $result['client_id'] ]['revenue']         = ( strpos( $result['description'], 'refund' ) !== false ? $clients['clients'][ $result['client_id'] ]['revenue'] : $clients['clients'][ $result['client_id'] ]['revenue'] + $result['credit_revenue_amount'] );
			$clients['clients'][ $result['client_id'] ]['full_name']       = $result['full_name'];
			$clients['clients'][ $result['client_id'] ]['role']            = $result['role'];
			$clients['clients'][ $result['client_id'] ]['avatar']          = $result['large'];
			$clients['clients'][ $result['client_id'] ]['total_tasks']     = $clients['clients'][ $result['client_id'] ]['total_tasks'] + 1;
			$clients['clients'][ $result['client_id'] ]['tasks']           = ( strpos( $result['task_type'], 'subtask' ) === false ? $clients['clients'][ $result['client_id'] ]['tasks'] + 1 : $clients['clients'][ $result['client_id'] ]['tasks'] );
			$clients['clients'][ $result['client_id'] ]['subtasks']        = ( strpos( $result['task_type'], 'subtask' ) !== false ? $clients['clients'][ $result['client_id'] ]['subtasks'] + 1 : $clients['clients'][ $result['client_id'] ]['subtasks'] );
			$clients['clients'][ $result['client_id'] ]['last_sign_in_at'] = $result['last_sign_in_at'];
			$clients['clients'][ $result['client_id'] ]['timezone_offset'] = $result['timezone_offset'];


			$clients['totals']['refunds']   = ( strpos( $result['description'], 'refund' ) !== false ? $clients['totals']['refunds'] + 1 : $clients['totals']['refunds'] );
			$clients['totals']['completed'] = ( strpos( $result['description'], 'refund' ) === false ? $clients['totals']['completed'] + 1 : $clients['totals']['completed'] );
			$clients['totals']['subtasks']  = ( strpos( $result['task_type'], 'subtask' ) !== false ? $clients['totals']['subtasks'] + 1 : $clients['totals']['subtasks'] );
			$clients['totals']['tasks']     = ( strpos( $result['task_type'], 'subtask' ) === false ? $clients['totals']['tasks'] + 1 : $clients['totals']['tasks'] );

			$clients['clients'][ $result['client_id'] ]['transactions'][] = [
				'id'             => $result['id'],
				'description'    => $result['description'],
				'dateadded'      => $result['dateadded'],
				'fee_percentage' => $result['fee_percentage'],
				'fee_amount'     => $result['fee_amount'],
				'task_type'      => $result['task_type'],
				'task_id'        => $result['task_id'],
				'task_title'     => $result['task_title'],
				'parent_task_id' => $result['parent_task_id'],
				'preferred'      => $result['preferred'],
				'pro'            => $result['pro'],
				'revenue'        => $result['credit_revenue_amount'],
				'is_refund'      => ( strpos( $result['description'], 'refund' ) !== false ? 1 : 0 ),
			];
		}

		return $clients;
	}

	private function get_client_table_name() {
		return $this->plugin->models_manager->client->table->full_name;
	}

	/**
	 * @param $from_day
	 * @param $from_month
	 * @param $from_year
	 * @param $to_day
	 * @param $to_month
	 * @param $to_year
	 *
	 * @return array
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 */
	public function get_dates_average( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year ) {
		$query = $this->get_query( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year );
		$query
			->select_raw( 'AVG(fee_amount) as fee_amount' )
			->select_raw( 'AVG(credit_fee_amount) as contractor_fee' )
			->select_raw( 'AVG(credit_revenue_amount) as revenue' )
			->select_raw( 'AVG(debit_user_amount) as total_cost' )
			->raw();

		$result = $query->execute();

		return array_shift( $result );
	}

}
