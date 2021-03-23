<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/17
 */
namespace PHPZlc\Admin\Strategy\Repository\FormControl;

abstract class AbstractControl
{
    const BOOL_CONTROL = 'bool_control';

    const TEXT_CONTROL = 'text_control';

    const DATE_TIME_CONTROL = 'date_time_control';

    const DATE_CONTROL = 'date_control';

    const TIME_CONTROL = 'time_control';

    const IMAGE_UPLOAD_CONTROL = 'image_upload_control';

    const URL_CONTROL = 'url_control';

    const PASSWORD_CONTROL = 'password_control';
    
    const SELECT_CONTROL = 'select_control';
    
    const TEXTAREA_CONTROL = 'textarea_control';
    
    const NUMBER_CONTROL = 'number_control';
    
    const INT_CONTROL = 'int_control';
    
    const SORT_CONTROL = 'sort_control';
    
    const RICHTEXT_CONTROL = 'richtext_control';

    const AMOUNT_CONTROL = 'amount_control';

    public $type;

    public $lableText;

    public $placeholder;

    public $data;
}