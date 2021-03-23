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
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractPage extends AbstractController
{
    const PAGE_INDEX = 'index';

    const PAGE_ADD = 'add';

    const PAGE_EDIT = 'edit';

    const PAGE_INFO = 'info';

    protected $reposiort;

    protected $page;

    public function __construct(RepositoryStrategy $reposiort, $page)
    {
        $this->reposiort = $reposiort;
        $this->page = $page;
        $this->container = $reposiort->container;
    }

    /**
     * @param AbstractServiceEntityRepository $entityRepository
     * @param Field $field
     * @param $stort
     * @return FieldPageConfig
     */
    abstract public static function fieldPageConfigConstruce(AbstractServiceEntityRepository $entityRepository, Field $field, $stort);
    
    public function pageFileds()
    {
        return $this->reposiort->pageFileds($this->page);
    }


    protected function fieldToString(Field $field, $value)
    {
        if($value instanceof \DateTime){
            $value = $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}