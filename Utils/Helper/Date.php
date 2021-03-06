<?php declare(strict_types = 1);

namespace Vairogs\Component\Utils\Helper;

use DateTime;
use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use ReflectionException;
use Vairogs\Component\Utils\Annotation;
use function array_merge;
use function floor;
use function gmdate;
use function round;
use function str_replace;
use function strlen;
use function substr;
use function trim;

class Date
{
    /**
     * @var string
     */
    public const FORMAT = 'd-m-Y H:i:s';
    /**
     * @var string
     */
    public const FORMAT_TS = 'D M d Y H:i:s T';
    /**
     * @var string[]
     */
    public const EXTRA_FORMATS = [
        self::FORMAT,
        self::FORMAT_TS,
    ];
    /**
     * @var int
     */
    public const SEC = 1000;
    /**
     * @var int
     */
    public const MIN = 60 * self::SEC;
    /**
     * @var int
     */
    public const HOUR = 60 * self::MIN;
    /**
     * @var array<string, int>
     */
    public const TIME = [
        'hour' => self::HOUR,
        'minute' => self::MIN,
        'second' => self::SEC,
    ];

    /**
     * @param string $personCode
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function validatePersonCode(string $personCode): bool
    {
        $personCode = Text::keepNumeric($personCode);
        if (11 !== strlen($personCode)) {
            return false;
        }

        if (32 === (int)substr($personCode, 0, 2)) {
            if (!self::validateNewPersonCode($personCode)) {
                return false;
            }
        } else {
            if (!self::validateDate($personCode)) {
                return false;
            }
            if (!self::validateOldPersonCode($personCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $personCode
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function validateNewPersonCode(string $personCode): bool
    {
        $personCode = str_replace('-', '', $personCode);
        // @formatter:off
        $calculations = [1, 6, 3, 7, 9, 10, 5, 8, 4, 2,];
        // @formatter:on
        $sum = 0;
        foreach ($calculations as $key => $calculation) {
            $sum += ($personCode[$key] * $calculation);
        }
        $remainder = $sum % 11;
        if (-1 > 1 - $remainder) {
            return (1 - $remainder + 11) === (int)$personCode[10];
        }

        return (1 - $remainder) === (int)$personCode[10];
    }

    /**
     * @param string $date
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function validateDate(string $date): bool
    {
        $date = Text::keepNumeric($date);
        $day = (int)substr($date, 0, 2);
        $month = (int)substr($date, 2, 2);
        if ($month < 0 || $month > 12) {
            return false;
        }
        // @formatter:off
        $months = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31,];
        // @formatter:on
        if (0 === (int)substr($date, 4, 2) % 4) {
            $months[1] = 29;
        }

        return 0 < $day && $months[$month - 1] >= $day;
    }

    /**
     * @param string $personCode
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function validateOldPersonCode(string $personCode): bool
    {
        $personCode = str_replace('-', '', $personCode);
        $check = '01060307091005080402';
        $checksum = 1;
        for ($i = 0; $i < 10; $i++) {
            $checksum -= (int)$personCode[$i] * (int)substr($check, $i * 2, 2);
        }

        return (int)($checksum - floor($checksum / 11) * 11) === (int)$personCode[10];
    }

    /**
     * @param int $timestamp
     * @param string $format
     *
     * @return string|int
     * @Annotation\TwigFilter()
     */
    public static function excelDate(int $timestamp, string $format = self::FORMAT): int|string
    {
        $base = 25569;
        if ($timestamp >= $base) {
            /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
            $unix = ($timestamp - $base) * 86400;
            $date = gmdate($format, $unix);
            if (self::validateDateBasic($date, $format)) {
                return $date;
            }
        }

        return $timestamp;
    }

    /**
     * @param mixed $date
     * @param string $format
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function validateDateBasic(mixed $date, string $format = self::FORMAT): bool
    {
        $object = DateTime::createFromFormat($format, $date);

        return $object && $date === $object->format($format);
    }

    /**
     * @param float|int $timestamp
     *
     * @return string
     * @Annotation\TwigFilter()
     */
    #[Pure] public static function format(int|float $timestamp): string
    {
        $str = '';
        $timestamp = round($timestamp * 1000);
        foreach (self::TIME as $unit => $value) {
            if ($timestamp >= $value) {
                $time = floor($timestamp / $value * 100.0 / 100.0);

                if ($time > 0) {
                    $str .= $time . ' ' . $unit . (1.0 === $time ? '' : 's') . ' ';
                }
                $timestamp -= ($time * $value);
            }
        }

        if ($timestamp > 0) {
            $str .= $timestamp . ' ms';
        }

        return trim($str);
    }

    /**
     * @param float|int $timestamp
     * @return array
     * @Annotation\TwigFilter()
     */
    #[Pure] public static function formatToArray(int|float $timestamp): array
    {
        $timestamp = round($timestamp * 1000);
        $result = [];

        foreach (self::TIME as $unit => $value) {
            if ($timestamp >= $value) {
                $time = floor($timestamp / $value * 100.0 / 100.0);
                if ($time > 0) {
                    $result[$unit] = $time;
                }
                $timestamp -= ($time * $value);
            }
        }

        if ($timestamp > 0) {
            $result['micro'] = $timestamp;
        }

        return $result;
    }

    /**
     * @param string $string
     * @param string $format
     *
     * @return string|null
     * @Annotation\TwigFilter()
     */
    public static function formatDate(string $string, string $format = self::FORMAT): ?string
    {
        $datetime = DateTime::createFromFormat($format, $string);

        return $datetime ? $datetime->format(self::FORMAT) : null;
    }

    /**
     * @param string|null $format
     * @param string|null $dateString
     *
     * @return DateTime|null
     * @Annotation\TwigFilter()
     */
    public static function getDateNullable(?string $format = null, ?string $dateString = null): ?DateTime
    {
        if (!$date = DateTime::createFromFormat($format, $dateString)) {
            return null;
        }

        return $date;
    }

    /**
     * @param string|null $format
     * @param string|null $dateString
     *
     * @return DateTime
     * @throws InvalidArgumentException
     * @Annotation\TwigFilter()
     */
    public static function getDate(?string $format = null, ?string $dateString = null): DateTime
    {
        if (!$date = DateTime::createFromFormat($format, $dateString)) {
            throw new InvalidArgumentException('Invalid date string');
        }

        return $date;
    }

    /**
     * @param int $timestamp
     * @param string|null $format
     *
     * @return string
     * @throws Exception
     * @Annotation\TwigFilter()
     */
    public static function createFromUnixTimestamp(int $timestamp = 0, string $format = null): string
    {
        return (new DateTime())->setTimestamp($timestamp)
            ->format($format ?? self::FORMAT);
    }

    /**
     * @param string $date
     *
     * @return DateTime|string
     * @throws ReflectionException
     * @Annotation\TwigFilter()
     */
    public static function guessDateFormat(string $date): DateTime|string
    {
        $formats = array_merge(Php::getClassConstantsValues(DateTime::class), self::EXTRA_FORMATS);
        foreach ($formats as $format) {
            $datetime = DateTime::createFromFormat($format, $date);
            if ($datetime instanceof DateTime) {
                return $datetime;
            }
        }

        return $date;
    }
}
