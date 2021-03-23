<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/17
 */

namespace PHPZlc\Admin\Strategy\Repository;

use PHPZlc\Admin\Strategy\Repository\FormControl\AbstractControl;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\RuleColumn;

class Field
{
    /**
     * @var RuleColumn
     */
    public $column;

    /**
     * @var string
     */
    public $text;

    /**
     * @var FieldPageConfig[]
     */
    public $pageConfigs;

    /**
     * @var string
     */
    public $setMethod;

    /**
     * @var string
     */
    public $getMethod;

    /**
     * @var string
     */
    public $getStringMethod;
}