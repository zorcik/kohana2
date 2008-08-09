<?php
/**
 * Database API driver
 *
 * $Id: Database.php 2302 2008-03-13 17:09:55Z Shadowhand $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Database_Driver {

	protected $cache = array();

	/**
	 * Connect to our database.
	 * Returns FALSE on failure or a MySQL resource.
	 *
	 * @return mixed
	 */
	abstract protected function connect();

	/**
	 * Perform a query based on a manually written query.
	 *
	 * @param  string  SQL query to execute
	 * @return Database_Result
	 */
	abstract public function query($sql);

	/**
	 * Builds a DELETE query.
	 *
	 * @param   string  table name
	 * @param   array   where clause
	 * @return  string
	 */
	public function delete($table, $where)
	{
		return 'DELETE FROM '.$this->escape_table($table).' WHERE '.implode(' ', $where);
	}

	/**
	 * Builds an UPDATE query.
	 *
	 * @param   string  table name
	 * @param   array   key => value pairs
	 * @param   array   where clause
	 * @return  string
	 */
	public function update($table, $values, $where)
	{
		foreach($values as $key => $val)
		{
			$valstr[] = $this->escape_column($key).' = '.$val;
		}
		return 'UPDATE '.$this->escape_table($table).' SET '.implode(', ', $valstr).' WHERE '.implode(' ', $where);
	}

	/**
	 * Set the charset using 'SET NAMES <charset>'.
	 *
	 * @param  string  character set to use
	 */
	public function set_charset($charset)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	/**
	 * Wrap the tablename in backticks, has support for: table.field syntax.
	 *
	 * @param   string  table name
	 * @return  string
	 */
	abstract public function escape_table($table);

	/**
	 * Escape a column/field name, has support for special commands.
	 *
	 * @param   string  column name
	 * @return  string
	 */
	abstract public function escape_column($column);

	/**
	 * Builds a WHERE portion of a query.
	 *
	 * @param   mixed    key
	 * @param   string   value
	 * @param   string   type
	 * @param   int      number of where clauses
	 * @param   boolean  escape the value
	 * @return  string
	 */
	public function where($key, $value, $type, $num_wheres, $quote)
	{
		$prefix = ($num_wheres == 0) ? '' : $type;

		if ($quote === -1)
		{
			$value = '';
		}
		elseif ($value === NULL)
		{
			if ( ! $this->has_operator($key))
			{
				$key .= ' IS';
			}

			$value = ' NULL';
		}
		elseif (is_bool($value))
		{
			if ( ! $this->has_operator($key))
			{
				$key .= ' =';
			}

			$value = ($value == TRUE) ? ' 1' : ' 0';
		}
		else
		{
			if ( ! $this->has_operator($key))
			{
				$key = $this->escape_column($key).' =';
			}
			else
			{
				preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL))\s*$/i', $key, $matches);
				if (isset($matches[1]) AND isset($matches[2]))
				{
					$key = $this->escape_column(trim($matches[1])).' '.trim($matches[2]);
				}
			}

			$value = ' '.(($quote == TRUE) ? $this->escape($value) : $value);
		}

		return $prefix.$key.$value;
	}

	/**
	 * Builds a LIKE portion of a query.
	 *
	 * @param   mixed    field name
	 * @param   string   value to match with field
	 * @param   boolean  add wildcards before and after the match
	 * @param   string   clause type (AND or OR)
	 * @param   int      number of likes
	 * @return  string
	 */
	public function like($field, $match = '', $auto = TRUE, $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = $this->escape_str($match);

		if ($auto === TRUE)
		{
			// Add the start and end quotes
			$match = '%'.$match.'%';
		}

		return $prefix.' '.$this->escape_column($field).' LIKE \''.$match . '\'';
	}

	/**
	 * Builds a NOT LIKE portion of a query.
	 *
	 * @param   mixed   field name
	 * @param   string  value to match with field
	 * @param   string  clause type (AND or OR)
	 * @param   int     number of likes
	 * @return  string
	 */
	public function notlike($field, $match = '', $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = $this->escape_str($match);

		if ($auto === TRUE)
		{
			// Add the start and end quotes
			$match = '%'.$match.'%';
		}

		return $prefix.' '.$this->escape_column($field).' NOT LIKE \''.$match.'\'';
	}

	/**
	 * Builds a REGEX portion of a query.
	 *
	 * @param   string   field name
	 * @param   string   value to match with field
	 * @param   string   clause type (AND or OR)
	 * @param   integer  number of regexes
	 * @return  string
	 */
	public function regex($field, $match, $type, $num_regexs)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	/**
	 * Builds a NOT REGEX portion of a query.
	 *
	 * @param   string   field name
	 * @param   string   value to match with field
	 * @param   string   clause type (AND or OR)
	 * @param   integer  number of regexes
	 * @return  string
	 */
	public function notregex($field, $match, $type, $num_regexs)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	/**
	 * Builds an INSERT query.
	 *
	 * @param   string  table name
	 * @param   array   keys
	 * @param   array   values
	 * @return  string
	 */
	public function insert($table, $keys, $values)
	{
		// Escape the column names
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->escape_column($value);
		}
		return 'INSERT INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	/**
	 * Builds a MERGE portion of a query.
	 *
	 * @param   string  table name
	 * @param   array   keys
	 * @param   array   values
	 * @return  string
	 */
	public function merge($table, $keys, $values)
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	/**
	 * Builds a LIMIT portion of a query.
	 *
	 * @param   integer  limit
	 * @param   integer  offset
	 * @return  string
	 */
	// abstract public function limit($limit, $offset = 0);

	/**
	 * Creates a prepared statement.
	 *
	 * @param   string  SQL query
	 * @return  Database_Stmt
	 */
	public function stmt_prepare($sql = '')
	{
		throw new Kohana_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	/**
	 * Escapes any input value.
	 *
	 * @param   mixed   value to escape
	 * @return  string
	 */
	public function escape_value($value)
	{
		if ($this->config['escaping'] === FALSE)
			return $value;

		switch (gettype($value))
		{
			case 'array':
				foreach ($value as $i => $val)
				{
					$value[$i] = $this->escape_value($val);
				}
				$value = implode(', ', $value);
			break;
			case 'string':
				$value = '\''.$this->escape_str($value).'\'';
			break;
			case 'boolean':
				$value = (int) $value;
			break;
			case 'double':
				// Convert to non-locale aware float to prevent possible commas
				$value = sprintf('%F', $value);
			break;
			default:
				$value = ($value === NULL) ? 'NULL' : $value;
			break;
		}

		return (string) $value;
	}

	/**
	 * Escapes a string for a query.
	 *
	 * @param   mixed   value to escape
	 * @return  string
	 */
	abstract public function escape_str($str);

	/**
	 * Lists all tables in the database.
	 *
	 * @return  array
	 */
	abstract public function list_tables();

	/**
	 * Lists all fields in a table.
	 *
	 * @param   string  table name
	 * @return  array
	 */
	// abstract function list_fields($table);

	/**
	 * Returns the last database error.
	 *
	 * @return  string
	 */
	// abstract public function show_error();

	/**
	 * Returns field data about a table.
	 *
	 * @param   string  table name
	 * @return  array
	 */
	// abstract public function field_data($table);

	/**
	 * Fetches SQL type information about a field, in a generic format.
	 *
	 * @param   string  field datatype
	 * @return  array
	 */
	protected function sql_type($str)
	{
		static $sql_types;

		if ($sql_types === NULL)
		{
			// Load SQL data types
			$sql_types = Kohana::config('sql_types');
		}

		$str = strtolower(trim($str));

		if (($open = strpos($str, '(')) !== FALSE)
		{
			// Find closing bracket
			$close = strpos($str, ')', $open) - 1;

			// Find the type without the size
			$type = substr($str, 0, $open);
		}
		else
		{
			// No length
			$type = $str;
		}

		empty($sql_types[$type]) and exit
		(
			'Unknown field type: '.$type.'. '.
			'Please report this: http://trac.kohanaphp.com/newticket'
		);

		// Fetch the field definition
		$field = $sql_types[$type];

		switch($field['type'])
		{
			case 'string':
			case 'float':
				if (isset($close))
				{
					// Add the length to the field info
					$field['length'] = substr($str, $open + 1, $close - $open);
				}
			break;
			case 'int':
				// Add unsigned value
				$field['unsigned'] = (strpos($str, 'unsigned') !== FALSE);
			break;
		}

		return $field;
	}

	public function clear_cache($sql = NULL)
	{
		if ( ! empty($sql))
		{
			// Delete a single query
			unset($this->cache[$this->query_hash($sql)]);
		}
		else
		{
			// Delete the entire cache
			$this->cache = array();
		}

		Kohana::log('debug', 'Database cache cleared: '.get_class($this));

		return $this;
	}

	/**
	 * Creates a hash for an SQL query string. Replaces newlines with spaces,
	 * trims, and hashes.
	 *
	 * @param   string  SQL query
	 * @return  string
	 */
	protected function query_hash($sql)
	{
		return sha1(trim(str_replace("\n", ' ', $sql)));
	}

} // End Database Driver Interface

/**
 * Database_Result
 *
 */
interface Database_Result {

	/**
	 * Builds an array of query results.
	 *
	 * @param   boolean   return rows as objects
	 * @param   mixed     type
	 * @return  array
	 */
	public function result_array();

	/**
	 * Gets the ID of the last insert statement.
	 *
	 * @return  integer
	 */
	public function insert_id();

	/**
	 * Gets the fields of an already run query.
	 *
	 * @return  array
	 */
	public function list_fields();

} // End Database Result Interface