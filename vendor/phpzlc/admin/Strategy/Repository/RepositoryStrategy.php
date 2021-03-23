<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/17
 */

namespace PHPZlc\Admin\Strategy\Repository;

use PHPZlc\Admin\Strategy\Repository\Page\AbstractPage;
use PHPZlc\Admin\Strategy\Repository\FormControl\AbstractControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\BoolControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\DateControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\DateTimeControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\TextControl;
use PHPZlc\Admin\Strategy\Repository\FormControl\TimeControl;
use PHPZlc\Admin\Strategy\Repository\Page\AddPage;
use PHPZlc\Admin\Strategy\Repository\Page\EditPage;
use PHPZlc\Admin\Strategy\Repository\Page\IndexPage;
use PHPZlc\Admin\Strategy\Repository\Page\InfoPage;
use PHPZlc\Admin\Strategy\Repository\PageConfig\AbstractPageConfig;
use PHPZlc\PHPZlc\Bundle\Service\DateTime\DateTime;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\AbstractServiceEntityRepository;
use PHPZlc\Validate\Validate;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RepositoryStrategy extends AbstractController
{
    /**
     * @var AbstractServiceEntityRepository
     */
    public $entityRepository;

    /**
     * @var Field[]
     */
    public $fields;

    public function __construct(ContainerInterface $container ,AbstractServiceEntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
        $this->container = $container;

        $stort = 1;

        foreach ($this->entityRepository->getClassRuleMetadata()->getAllRuleColumn() as $fieldName => $ruleColumn){
            $field = new Field();
            $field->column = $ruleColumn;
            $field->text = $ruleColumn->comment;
            $field->setMethod = 'set' . ucfirst($fieldName) . '()';
            $field->getMethod = 'get' . ucfirst($fieldName) . '()';
            $field->getStringMethod = 'get' . ucfirst($fieldName) . '()';
            $field->getDataMethod = 'get' . ucfirst($fieldName) . 'Data()';

            $field->pageConfigs[AbstractPage::PAGE_INDEX] = IndexPage::fieldPageConfigConstruce($this->entityRepository, $field, $stort);
            $field->pageConfigs[AbstractPage::PAGE_INFO] = InfoPage::fieldPageConfigConstruce($this->entityRepository, $field, $stort);
            $field->pageConfigs[AbstractPage::PAGE_ADD] = AddPage::fieldPageConfigConstruce($this->entityRepository, $field, $stort);
            $field->pageConfigs[AbstractPage::PAGE_EDIT] = EditPage::fieldPageConfigConstruce($this->entityRepository, $field, $stort);

            //控件
            /**
             * @var AbstractControl
             */
            $control;

            switch ($ruleColumn->type){
                case 'simple_array':
                case 'json_array':
                case 'array':
                    $control = new TextControl();
                    $control->lableText = $ruleColumn->comment;
                    break;
                case 'boolean':
                    $control = new BoolControl();
                    $control->lableText = $ruleColumn->comment;
                    break;
                case 'datetime':
                    $control = new DateTimeControl();
                    $control->lableText = $ruleColumn->comment;
                    break;
                case 'date':
                    $control = new DateControl();
                    $control->lableText = $ruleColumn->comment;
                    break;
                case 'time':
                    $control = new TimeControl();
                    $control->lableText = $ruleColumn->comment;
                default:
                    $control = new TextControl();
                    $control->lableText = $ruleColumn->comment;
            }

            $field->control = $control;

            $this->fields[$fieldName] = $field;

            $stort = $stort + 5;
        }
    }

    public function addField(Field $field)
    {
        $this->fields[$field->column->propertyName] = $field;
    }

    public function field($field)
    {
        return $this->fields[$field];
    }

    public function fieldPageConfig($field, $page)
    {
        return $this->fields[$field]->pageConfigs[$page];
    }

    public function fieldColumn($field)
    {
        return $this->fields[$field]->column;
    }

    public function fieldFromControl($field)
    {
        return $this->fields[$field]->inputField;
    }

    /**
     * @param $page
     * @return Field[]
     */
    public function pageFileds($page)
    {
        $fieldPageStorts = [];

        foreach ($this->fields as $key => $field){
            $fieldPageStorts[$key] = $field->pageConfigs[$page]->sort;
        }

        asort($fieldPageStorts);

        $fields = [];

        foreach ($fieldPageStorts as $key => $fieldPageStort){
            $fields[$key] = $this->fields[$key];
        }

        return $fields;
    }
}