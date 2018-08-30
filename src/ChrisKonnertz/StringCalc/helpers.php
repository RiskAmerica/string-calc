<?php

if (!function_exists('bcexp')) {
    /**
     * @param     $x
     * @param int $digits
     * @return string
     */
    function bcexp($x, $digits = 14)
    {
        $sum   = $prev_sum = '0.0';
        $error = '0.' . str_repeat('0', $digits - 1) . '1'; // 0.1*10^-k
        $n     = '0.0';
        do {
            $prev_sum = $sum;
            $sum      = bcadd($sum, bcpowfact($x, $n));
            $n        = bcadd($n, '1'); // bc idiom for $n++
        } while (bccomp(bcsub($sum, $prev_sum), $error) == 1);
        return $sum;
    }
}

if (!function_exists('bcpowfact')) {
    /**
     * @param $x
     * @param $n
     * @return string
     */
    function bcpowfact($x, $n)
    {
        if (bccomp_zero($n) == 0) {
            return '1';
        }
        if (bccomp($n, '1') == 0) {
            return $x;
        }
        $a = $x; // 1st step: a *= x / 1
        $i = $n;
        while (bccomp($i, '1') == 1) {
            // ith step: a *= x / i
            $a = bcmul($a, bcdiv($x, $i));
            $i = bcsub($i, '1'); // bc idiom for $i--
        }
        return $a;
    }
}
if (!function_exists('bccomp_zero')) {
    /**
     * @param $amount
     * @return int
     */
    function bccomp_zero($amount)
    {
        return bccomp($amount, (@$amount{0} == "-" ? '-' : '') . '0.0');
    }
}

if (!function_exists('bcpow2')) {
    /**
     * @param $left
     * @param $right
     * @return string
     */
    function bcpow2($left, $right)
    {
        return bcexp(bcmul($right, bcln($left)));
    }
}

if (!function_exists('bcln')) {
    /**
     * @param $value
     * @return string
     */
    function bcln($value) // value > 0
    {
        $m    = (string)log($value);
        $x    = bcsub(bcdiv($value, bcexp($m)), "1");
        $res  = "0";
        $xpow = "1";
        $i    = 0;
        do {
            $i++;
            $xpow = bcmul($xpow, $x);
            $sum  = bcdiv($xpow, $i);
            if ($i % 2 == 1) {
                $res = bcadd($res, $sum);
            } else {
                $res = bcsub($res, $sum);
            }
        } while (bccomp($sum, '0'));
        return bcadd($res, $m);
    }
}

if (!function_exists('bcceil')) {
    /**
     * @param $number
     * @return string
     */
    function bcceil($number)
    {
        if (strpos($number, '.') !== false) {
            if (preg_match("~\.[0]+$~", $number)) {
                return bcround($number, 0);
            }
            if ($number[0] != '-') {
                return bcadd($number, 1, 0);
            }
            return bcsub($number, 0, 0);
        }
        return $number;
    }
}
if (!function_exists('bcfloor')) {

    /**
     * @param $number
     * @return string
     */
    function bcfloor($number)
    {
        if (strpos($number, '.') !== false) {
            if (preg_match("~\.[0]+$~", $number)) {
                return bcround($number, 0);
            }
            if ($number[0] != '-') {
                return bcadd($number, 0, 0);
            }
            return bcsub($number, 1, 0);
        }
        return $number;
    }
}

if (!function_exists('bcround')) {
    /**
     * @param     $number
     * @param int $precision
     * @return string
     */
    function bcround($number, $precision = 0)
    {
        if (strpos($number, '.') !== false) {
            if ($number[0] != '-') {
                return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }
            return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }
        return $number;
    }
}
