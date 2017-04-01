<?php namespace ChrisKonnertz\StringCalc\Symbols\Concrete;

use ChrisKonnertz\StringCalc\Symbols\AbstractConstant;

/**
 * PHP M_PI constant
 */
abstract class PiConstant extends AbstractConstant
{

    /**
     * @inheritdoc
     */
    protected $textualRepresentations = ['pi'];

    /**
     * @inheritdoc
     */
    const VALUE = M_PI;

}