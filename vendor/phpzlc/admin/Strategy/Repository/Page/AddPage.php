<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/28
 */

namespace PHPZlc\Admin\Strategy\Repository\Page;


use PHPZlc\Admin\Strategy\Repository\Field;
use PHPZlc\Admin\Strategy\Repository\FieldPageConfig;
use PHPZlc\Admin\Strategy\Repository\FormControl\AbstractControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\BoolControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\DateControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\DateTimeControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\TextControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\TimeControl;
use PHPZlc\Admin\Strategy\Repository\RepositoryStrategy;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\AbstractServiceEntityRepository;

class AddPage extends AbstractPage
{
    public function __construct(RepositoryStrategy $reposiort)
    {
        $page = AbstractPage::PAGE_ADD;
        parent::__construct($reposiort, $page);
    }

    public static function fieldPageConfigConstruce(AbstractServiceEntityRepository $entityRepository, Field $field, $stort)
    {
        $pageConfig = new FieldPageConfig();
        $pageConfig->nullable = $field->column->nullable;
        $pageConfig->sort = $stort;

        if($field->column->propertyName == $entityRepository->getPrimaryKey() || $field->column->isEntity){
            $pageConfig->authority = FieldPageConfig::HIDE;
        }else{
            $pageConfig->authority = FieldPageConfig::EDIT;
        }

//        //控件
//        /**
//         * @var AbstractControl
//         */
//        $control;
//
//        switch ($ruleColumn->type){
//            case 'simple_array':
//            case 'json_array':
//            case 'array':
//                $control = new TextControl();
//                $control->lableText = $ruleColumn->comment;
//                break;
//            case 'boolean':
//                $control = new BoolControl();
//                $control->lableText = $ruleColumn->comment;
//                break;
//            case 'datetime':
//                $control = new DateTimeControl();
//                $control->lableText = $ruleColumn->comment;
//                break;
//            case 'date':
//                $control = new DateControl();
//                $control->lableText = $ruleColumn->comment;
//                break;
//            case 'time':
//                $control = new TimeControl();
//                $control->lableText = $ruleColumn->comment;
//            default:
//                $control = new TextControl();
//                $control->lableText = $ruleColumn->comment;
//        }
//
//        $field->control = $control;

        return $pageConfig;
    }

}