<?php
require_once 'database.class.php';
class searchpdo extends database
{
	private $tableArr, $fieldArr;
	function __construct($tableArr, $fieldArr = NULL, $q = NULL)
	{
		parent::__construct();
		$this->tableArr = $tableArr;
		$this->fieldArr = $fieldArr;
		$this->formatAndReturn($q);
	}

	function formatAndReturn($q)
	{
		$sArr = explode(' ', $q);	
		$boolean = $this->checkForBool($sArr);
		$s = NULL;
		$sArr = array_diff($sArr, array($boolean));		
		if($boolean == "OR")
		{
			$this->createORstring($sArr);
		}
		elseif($boolean == "AND")
		{
			$this->createANDstring($sArr);
		}
		else
		{
			$this->createNormalstring($q);
		}
	}

	function checkForBool($sArr)
	{
		$boolean = NULL;
		foreach ($sArr as $searchwords)
		{
			if(($searchwords == "OR") || ($searchwords == "AND"))
			{
				$boolean = $searchwords;
			}			
		}
		return $boolean;
	}

	function createNormalstring($q)
	{
		$colArr = array();
		foreach ($this->tableArr as $table)
		{
			$ss = NULL;
			$ss = "SELECT * FROM `".$table."` WHERE ";
			$colArr = $this->getFieldsFromTable($table);
			foreach ($colArr as $col)
			{
				// This is the area of concentration
				$ss .= "`".$col."` LIKE '%$q%' OR ";
			}
			$ss = rtrim($ss," OR ");						
			$rows = $this->query($ss);
			echo '<br />Searching in table "'.$table.'" for "'.$q.'"';
			$this->buildTable($rows, $colArr);
		}
	}

	function createORstring($sArr)
	{
		$colArr = array();
		foreach ($this->tableArr as $table)
		{
			$ss = NULL;
			$ss = "SELECT * FROM `".$table."` WHERE ";
			$colArr = $this->getFieldsFromTable($table);			
			foreach ($colArr as $col)
			{
				foreach ($sArr as $q)
				{
					$ss .= "`".$col."` LIKE '%$q%' OR ";
				}				
			}
			$ss = rtrim($ss," OR ");			
			$rows = $this->query($ss);
			echo '<br />Searching in table "'.$table.'" for "'.$q.'"';
			$this->buildTable($rows, $colArr);
		}
	}

	function createANDstring($sArr)
	{
		$colArr = array();
		foreach ($this->tableArr as $table)
		{
			$ss = NULL;
			$ss = "SELECT * FROM `".$table."` WHERE ";
			$colArr = $this->getFieldsFromTable($table);			
			foreach ($colArr as $col)
			{
				foreach ($sArr as $q)
				{
					$ss .= "`".$col."` LIKE '%$q%' AND ";
				}
				$ss = rtrim($ss," AND ");
				$ss .= " OR ";
			}
			$ss = rtrim($ss," OR ");
			$rows = $this->query($ss);
			echo '<br />Searching in table "'.$table.'" for "'.$q.'"';
			$this->buildTable($rows, $colArr);
		}
	}

	function getFieldsFromTable($tn)
	{
		$outArr = array();
		$s = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='{$this->config->db_name}' AND `TABLE_NAME`='{$tn}'";
		$rows = $this->query($s);
		foreach ($rows as $row)
		{
			if(strtolower($row->COLUMN_NAME) !== 'id')
			{
				array_push($outArr, $row->COLUMN_NAME);
			}
		}
		return $outArr;
	}

	function buildHead($colArr)
	{
		foreach ($colArr as $col)
		{
			if(!is_null($this->fieldArr))
			{
				if($this->isAssoc($this->fieldArr) == TRUE)
				{
					$key = array_search($col, $this->fieldArr);
					if(!empty($key))
					{
						echo '<th>'.strtoupper($key).'</th>';	
					}
				}
				else
				{
					if(in_array($col, $this->fieldArr))
					{
						echo '<th>'.strtoupper($col).'</th>';	
					}
				}
			}
			else
			{
				echo '<th>'.strtoupper($col).'</th>';	
			}					
		}
	}

	function buildBody($rows, $colArr)
	{
		foreach ($rows as $row)
		{
			echo '<tr>';
			foreach ($colArr as $col)
			{
				if(!is_null($this->fieldArr))
				{
					if(in_array($col, $this->fieldArr))
					{
						echo '<td>'.$row->{$col}.'</td>';
					}
				}
				else
				{
					echo '<td>'.$row->{$col}.'</td>';	
				}						
			}
			echo '</tr>'.PHP_EOL;		
		}
	}

	function buildTable($rows, $colArr)
	{
		if(count($rows) > 0)
		{
			echo '<div class="table-responsive">';
			echo '<table class="table table-condensed">'.PHP_EOL;
			echo '<thead><tr>';
			$this->buildHead($colArr);
			echo '</tr></thead><tbody>'.PHP_EOL;
			$this->buildBody($rows, $colArr);
			echo '</tbody></table>'.PHP_EOL;
			echo '</div>'.PHP_EOL;
		}
	}

	function isAssoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
?>