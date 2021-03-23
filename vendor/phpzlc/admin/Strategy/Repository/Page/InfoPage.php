<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/28
 */

namespace PHPZlc\Admin\Strategy\Repository\Page;


use PHPZlc\Admin\Strategy\Repository\Field;
use PHPZlc\Admin\Strategy\Repository\FieldPageConfig;
use PHPZlc\Admin\Strategy\Repository\RepositoryStrategy;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\AbstractServiceEntityRepository;

class InfoPage extends AbstractPage
{
    public function __construct(RepositoryStrategy $reposiort)
    {
        $page = AbstractPage::PAGE_INFO;
        parent::__construct($reposiort, $page);
    }

    public static function fieldPageConfigConstruce(AbstractServiceEntityRepository $entityRepository, Field $field, $stort)
    {
        $pageConfig = new FieldPageConfig();
        $pageConfig->nullable = $field->column->nullable;
        $pageConfig->sort = $stort;

        if($field->column->propertyName == $entityRepository->getPrimaryKey() || $field->column->isEntity){
            $pageConfig->authority = FieldPageConfig::HIDE;
        }

        return $pageConfig;
    }

}