<?php
/**
 * 分类管理业务层
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/13
 * Time: 12:05 下午
 */

namespace App\Business\ClassifyBusiness;

use App\Entity\Classify;
use App\Repository\ClassifyRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;

class ClassifyBusiness extends AbstractBusiness
{
    /**
     * @var ClassifyRepository
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
            if($class instanceof Classify){
                $classify_no = $this->sortRepository->findAssoc(['sort_no' => $class->getClassifyNo()]);
                if(!empty($classify_no) && $classify_no->getId() !== $class->getId()){
                    Errors::setErrorMessage('分类编号已存在'); return false;
                }

                $classify_name = $this->sortRepository->findAssoc(['sort_name' => $class->getClassifyName()]);

                if(!empty($classify_name) && $classify_name->getId() !== $class->getId()){
                    Errors::setErrorMessage('分类名称已存在'); return false;
                }
            }
        }

        return true;
    }

    public function create(Classify $classify)
    {
        if(!$this->validator($classify)){
            return false;
        }

        $classify->setCreateAt(new \DateTime());

        $this->em->persist($classify);
        $this->em->flush();
        $this->em->clear();

        return true;
    }


    public function update(Classify $classify)
    {
       if(!$this->validator($classify)){
           return false;
       }

       $classify->setUpdateAt(new \DateTime());

       $this->em->persist($classify);
       $this->em->flush();
       $this->em->clear();

       return true;
    }
}