<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/17
 */

namespace PHPZlc\Admin\Strategy\Repository;

use PHPZlc\Admin\Strategy\Repository\FormControl\AbstractControl;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\RuleColumn;

class FieldPageConfig
{
    const SHOW ='show';

    const EDIT = 'edit';

    const HIDE = 'hide';

    public $authority = self::SHOW;

    public $id;

    public $class;

    public $style;

    public $other;

    public $nullable;

    /**
     * @var AbstractControl
     */
    public $control;

    /**
     * @var void
     */
    public $controlData;

    /**
     * @var
     */
    public $controlGetDataMethod;

    /**
     * @var integer 越小排序越高
     */
    public $sort;
}