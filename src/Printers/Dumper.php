<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use function array_keys;
use function array_pop;
use function count;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function max;
use function preg_replace;
use function range;
use function sprintf;
use function str_repeat;
use function strlen;
use function strpos;
use function var_export;
use const PHP_EOL;

final class Dumper
{

	public const
		OptIncludeApostrophe = 'include_apostrophe',
		OptMaxDepth = 'max_depth',
		OptWrapLength = 'wrap_length',
		OptIndentChar = 'indent_char';

	private const IndentLength = 4;

	/**
	 * @param mixed $value
	 * @param array<mixed> $options
	 */
	public static function dumpValue($value, array $options = []): string
	{
		return self::dumpValueInternal($value, [], 0, 0, $options);
	}

	/**
	 * @param mixed $value
	 * @param array<mixed> $parents
	 * @param array<mixed> $options
	 */
	private static function dumpValueInternal(
		&$value,
		array $parents = [],
		int $level = 0,
		int $column = 0,
		array $options = []
	): string
	{
		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}

		if ($value === null) {
			return 'null';
		}

		if (is_int($value) || is_float($value)) {
			return var_export($value, true);
		}

		if (is_string($value)) {
			return self::dumpString($value, $options);
		}

		if (is_array($value)) {
			return self::dumpArray($value, $parents, $level, $column, $options);
		}

		throw InvalidArgument::create()
			->withMessage('Unexpected value');
	}

	/**
	 * @param array<mixed> $options
	 */
	private static function dumpString(string $var, array $options): string
	{
		$var = (string) preg_replace('#\'|\\\\(?=[\'\\\\]|$)#D', '\\\\$0', $var);
		$includeApostrophe = (bool) ($options[self::OptIncludeApostrophe] ?? true);

		return $includeApostrophe ? sprintf("'%s'", $var) : $var;
	}

	/**
	 * @param array<mixed> $var
	 * @param array<mixed> $parents
	 * @param array<mixed> $options
	 */
	private static function dumpArray(array &$var, array $parents, int $level, int $column, array $options): string
	{
		if ($var === []) {
			return '[]';
		}

		if ($level > ($options[self::OptMaxDepth] ?? 50) || in_array($var, $parents, true)) {
			throw InvalidArgument::create()
				->withMessage('Nesting level too deep or recursive dependency.');
		}

		$indentChar = $options[self::OptIndentChar] ?? "\t";
		$space = str_repeat($indentChar, $level);
		$outInline = '';
		$outWrapped = sprintf("\n%s", $space);
		$parents[] = $var;
		$counter = 0;
		$hideKeys = is_int(($tmp = array_keys($var))[0]) && $tmp === range($tmp[0], $tmp[0] + count($var) - 1);

		foreach ($var as $k => &$v) {
			$keyPart = $hideKeys && $k === $counter ? '' : self::dumpValue($k) . ': ';
			$counter = is_int($k) ? max($k + 1, $counter) : $counter;
			$outInline .= ($outInline === '' ? '' : ', ') . $keyPart;
			$outInline .= self::dumpValueInternal($v, $parents, 0, $column + strlen($outInline), $options);
			$outWrapped .= $indentChar
				. $keyPart
				. self::dumpValueInternal($v, $parents, $level + 1, strlen($keyPart), $options)
				. sprintf(",\n%s", $space);
		}

		unset($v);

		array_pop($parents);
		$wrap = strpos($outInline, PHP_EOL) !== false || $level * self::IndentLength + $column + strlen(
			$outInline,
		) + 3 > ($options[self::OptWrapLength] ?? 120); // 3 = [],

		return '[' . ($wrap ? $outWrapped : $outInline) . ']';
	}

}
