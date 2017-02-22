<?php
// data-model-tree-approach-include.php
class TreeApproach
{
	public static function displayResults($query, $result)
	{
		$output = '';
		$output .= $query . PHP_EOL;
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		$output .= '                  ID |                Title | Year |               Parent' . PHP_EOL;
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		foreach ($result as $item) {
			$output .= vsprintf('%20s | %20s | %4d | %20s' . PHP_EOL, $item);
		}
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		$output .= PHP_EOL;
		return $output;
	}

	public static function displayShortResult($query, $result)
	{
		$output = '';
		$output .= $query . PHP_EOL;
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		$output .= var_export($result, TRUE);
		$output .= PHP_EOL;
		return $output;
	}

	public static function displayChildResult($query, $result)
	{
		$output = '';
		$output .= $query . PHP_EOL;
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		$output .= '                  ID |   First Name |    Last Name |             Children' . PHP_EOL;
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		foreach ($result as $item) {
			$output .= sprintf('%20s | %12s | %12s | %20s',
								$item['_id'],
								$item['firstName'],
								$item['lastName'],
								$item['children'][0]);
			$output .= PHP_EOL;
			for ($x = 1; $x < count($item['children']); $x++) {
				$output .= sprintf('%20s | %12s | %12s | %20s', ' ', ' ', ' ', $item['children'][$x]);
				$output .= PHP_EOL;
			}
		}
		$output .= '-------------------------------------------------------------------------' . PHP_EOL;
		$output .= PHP_EOL;
		return $output;
	}
}
