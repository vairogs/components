<?php declare(strict_types = 1);

namespace Vairogs\Component\Utils\Helper;

use function array_slice;
use function count;
use function is_array;

class Sort
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * @param mixed $foo
     * @param mixed $bar
     */
    public static function swap(&$foo, &$bar): void
    {
        if ($foo === $bar) {
            return;
        }

        $tmp = $foo;
        $foo = $bar;
        $bar = $tmp;
    }

    /**
     * @param array $array
     */
    public static function bubbleSort(array &$array): void
    {
        $count = count($array);
        for ($foo = 0; $foo < $count; $foo++) {
            for ($bar = 0; $bar < $count - 1; $bar++) {
                if ($bar < $count && $array[$bar] > $array[$bar + 1]) {
                    self::swapArray($array, $bar, $bar + 1);
                }
            }
        }
    }

    /**
     * @param array $array
     * @param mixed $foo
     * @param mixed $bar
     */
    public static function swapArray(array &$array, $foo, $bar): void
    {
        if ($array[$foo] === $array[$bar]) {
            return;
        }

        $tmp = $array[$foo];
        $array[$foo] = $array[$bar];
        $array[$bar] = $tmp;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public static function mergeSort(array $array): array
    {
        if (1 === count($array)) {
            return $array;
        }

        $middle = (int)round(count($array) / 2);
        $left = array_slice($array, 0, $middle);
        $right = array_slice($array, $middle);

        $left = self::mergeSort($left);
        $right = self::mergeSort($right);

        return self::merge($left, $right);
    }

    /**
     * @param array $left
     * @param array $right
     * @return array
     */
    private static function merge(array $left, array $right): array
    {
        $result = [];
        $i = $j = 0;

        $leftCount = count($left);
        $rightCount = count($right);

        while ($i < $leftCount && $j < $rightCount) {
            if ($left[$i] > $right[$j]) {
                $result[] = $right[$j];
                $j++;
            } else {
                $result[] = $left[$i];
                $i++;
            }
        }

        while ($i < $leftCount) {
            $result[] = $left[$i];
            $i++;
        }

        while ($j < $rightCount) {
            $result[] = $right[$j];
            $j++;
        }

        return $result;
    }

    /**
     * @param mixed $item
     * @param mixed $field
     *
     * @return bool
     */
    public static function isSortable($item, $field): bool
    {
        if (is_array($item)) {
            return array_key_exists($field, $item);
        }

        if (is_object($item)) {
            return isset($item->$field) || property_exists($item, $field);
        }

        return false;
    }

    /**
     * @param mixed $parameter
     * @param string $order
     *
     * @return callable
     */
    public static function usort($parameter, string $order): callable
    {
        return static function ($a, $b) use ($parameter, $order) {
            $flip = ($order === self::DESC) ? -1 : 1;

            if (($a_sort = Php::getParameter($a, $parameter)) === ($b_sort = Php::getParameter($b, $parameter))) {
                return 0;
            }

            if ($a_sort > $b_sort) {
                return $flip;
            }

            return (-1 * $flip);
        };
    }
}
