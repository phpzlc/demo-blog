<?php
/**
 * 分类管理业务层
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/13
 * Time: 12:05 下午
 */

namespace App\Business\SortBusiness;

use App\Entity\Sort;
use App\Repository\SortRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;

class SortBusiness extends AbstractBusiness
{
    /**
     * @var SortRepository
     */
    protected $sortRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->sortRepository = $this->getDoctrine()->getRepository('App:Sort');
    }

    public function validator($class): bool
    {
        if(!parent::validator($class)){
            return false;
        }else{
            if($class instanceof Sort){
                $sort_no = $this->sortRepository->findAssoc(['sort_no' => $class->getSortNo()]);
                if(!empty($sort_no) && $sort_no->getId() !== $class->getId()){
                    Errors::setErrorMessage('分类编号已存在'); return false;
                }

                $sort_name = $this->sortRepository->findAssoc(['sort_name' => $class->getSortName()]);

                if(!empty($sort_name) && $sort_name->getId() !== $class->getId()){
                    Errors::setErrorMessage('分类名称已存在'); return false;
                }
            }
        }

        return true;
    }

    public function create(Sort $sort)
    {
        if(!$this->validator($sort)){
            return false;
        }

        $sort->setCreateAt(new \DateTime());

        $this->em->persist($sort);
        $this->em->flush();
        $this->em->clear();

        return true;
    }


    public function update(Sort $sort)
    {
       if(!$this->validator($sort)){
           return false;
       }

       $sort->setUpdateAt(new \DateTime());

       $this->em->persist($sort);
       $this->em->flush();
       $this->em->clear();

       return true;
    }
}