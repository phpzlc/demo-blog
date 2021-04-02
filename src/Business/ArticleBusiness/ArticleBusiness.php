<?php
/**
 * 博客文章业务层
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/1
 * Time: 4:17 下午
 */

namespace App\Business\ArticleBusiness;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;

class ArticleBusiness extends AbstractBusiness
{
    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->articleRepository = $this->getDoctrine()->getRepository('App:Article');
    }

    public function validator($class): bool
    {
        if(!parent::validator($class)){
            return false;
        }else{
            if($class instanceof Article){
                $article = $this->articleRepository->findOneBy(['title' => $class->getTitle()]);
                if(!empty($article) && $article->getId() !== $class->getId()){
                    Errors::setErrorMessage('标题已存在'); return false;
                }
            }
        }

        return true;
    }

    public function create(Article $article)
    {
        if(!$this->validator($article)){
            return false;
        }

        $article->setCreateAt(new \DateTime());

        try {
            $this->em->persist($article);
            $this->em->flush();
            $this->em->clear();

            return true;
        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }

    public function update(Article $article)
    {
        if(!$this->validator($article)){
            return false;
        }

        $article->setUpdateAt(new \DateTime());

        try {
            $this->em->persist($article);
            $this->em->flush();
            $this->em->clear();

            return true;
        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }
}