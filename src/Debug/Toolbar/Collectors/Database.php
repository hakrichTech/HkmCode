<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug\Toolbar\Collectors;

use Hkm_code\Database\Query;

/**
 * Collector for the Database tab of the Debug Toolbar.
 */
class Database extends BaseCollector
{
	/**
	 * Whether this collector has timeline data.
	 *
	 * @var boolean
	 */
	protected static $hasTimeline = true;

	/**
	 * Whether this collector should display its own tab.
	 *
	 * @var boolean
	 */
	protected static $hasTabContent = true;

	/**
	 * Whether this collector has data for the Vars tab.
	 *
	 * @var boolean
	 */
	protected static $hasVarData = false;

	/**
	 * The name used to reference this collector in the toolbar.
	 *
	 * @var string
	 */
	protected static $title = 'Database';

	/**
	 * Array of database connections.
	 *
	 * @var array
	 */
	protected static $connections;

	/**
	 * The query instances that have been collected
	 * through the DBQuery Event.
	 *
	 * @var Query[]
	 */
	protected static $queries = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$connections = hkm_config('Database')::getConnections();
	}

	//--------------------------------------------------------------------

	/**
	 * The static method used during Events to collect
	 * data.
	 *
	 * @param Query $query
	 *
	 * @internal param $ array \Hkm_code\Database\Query
	 */
	public static function COLLECT(Query $query)
	{
		$config = hkm_config('Toolbar');

		// Provide default in case it's not set
		$max = $config::$maxQueries ?: 100;

		if (count(static::$queries) < $max)
		{
			static::$queries[] = $query;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns timeline data formatted for the toolbar.
	 *
	 * @return array The formatted data or an empty array.
	 */
	protected static function FORMAT_TIMELINE_DATA(): array
	{
		$data = [];

		foreach (self::$connections as $alias => $connection)
		{
			// Connection Time
			$data[] = [
				'name'      => 'Connecting to Database: "' . $alias . '"',
				'component' => 'Database',
				'start'     => $connection->getConnectStart(),
				'duration'  => $connection->getConnectDuration(),
			];
		}

		foreach (static::$queries as $query)
		{
			$data[] = [
				'name'      => 'Query',
				'component' => 'Database',
				'start'     => $query->getStartTime(true),
				'duration'  => $query->getDuration(),
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 */
	public static function DISPLAY(): array
	{
		$data['queries'] = array_map(function (Query $query) {
			return [
				'duration' => ((float) $query->getDuration(5) * 1000) . ' ms',
				'sql'      => $query->debugToolbarDisplay(),
			];
		}, static::$queries);

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the "badge" value for the button.
	 *
	 * @return integer
	 */
	public static function GET_BADGE_VALUE(): int
	{
		return count(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Information to be displayed next to the title.
	 *
	 * @return string The number of queries (in parentheses) or an empty string.
	 */
	public static function GET_TITLE_DETAILS(): string
	{
		return '(' . count(static::$queries) . ' Queries across ' . ($countConnection = count(self::$connections)) . ' Connection' .
				($countConnection > 1 ? 's' : '') . ')';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have any data collected?
	 *
	 * @return boolean
	 */
	public static function IS_EMPTY(): bool
	{
		return empty(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public static function ICON(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADMSURBVEhLY6A3YExLSwsA4nIycQDIDIhRWEBqamo/UNF/SjDQjF6ocZgAKPkRiFeEhoYyQ4WIBiA9QAuWAPEHqBAmgLqgHcolGQD1V4DMgHIxwbCxYD+QBqcKINseKo6eWrBioPrtQBq/BcgY5ht0cUIYbBg2AJKkRxCNWkDQgtFUNJwtABr+F6igE8olGQD114HMgHIxAVDyAhA/AlpSA8RYUwoeXAPVex5qHCbIyMgwBCkAuQJIY00huDBUz/mUlBQDqHGjgBjAwAAACexpph6oHSQAAAAASUVORK5CYII=';
	}
}
