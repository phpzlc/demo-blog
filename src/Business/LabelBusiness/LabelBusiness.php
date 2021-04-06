<?php
/**
 * 标签业务层
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/6
 * Time: 12:17 下午
 */

namespace App\Business\LabelBusiness;

use App\Entity\Label;
use App\Repository\LabelRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;

class LabelBusiness extends AbstractBusiness
{
    /**
     * @var LabelRepository
     */
    protected $labelRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->labelRepository = $this->getDoctrine()->getRepository('App:Label');
    }

    public function validator($class): bool
    {
        if(!parent::validator($class)){
            return false;
        }else{
            if($class instanceof Label){
                $label = $this->labelRepository->findAssoc(['name' => $class->getName(), 'is_del' => 0]);
                if(!empty($label) && $label->getId() != $class->getId()){
                    Errors::setErrorMessage($class->getName().'标签已存在'); return false;
                }
            }
        }

        return true;
    }

    public function create(Label $label)
    {
        if(!$this->validator($label)){
            return false;
        }

        $label->setCreateAt(new \DateTime());
        $this->em->persist($label);

        try {

            $this->em->flush();
            $this->em->clear();

            return true;
        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }

    public function update(Label $label)
    {
        if(!$this->validator($label)){
            return false;
        }

        $label->setUpdateAt(new \DateTime());
        $this->em->persist($label);

        try {
            $this->em->flush();
            $this->em->clear();

            return true;
        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }
}