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
use App\Entity\ArticleLabel;
use App\Repository\ArticleLabelRepository;
use App\Repository\ArticleRepository;
use App\Repository\LabelRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;

class ArticleBusiness extends AbstractBusiness
{
    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var ArticleLabelRepository
     */
    protected $articleLabelRepository;

    /**
     * @var LabelRepository
     */
    protected $labelRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->articleRepository = $this->getDoctrine()->getRepository('App:Article');
        $this->articleLabelRepository = $this->getDoctrine()->getRepository('App:ArticleLabel');
        $this->labelRepository = $this->getDoctrine()->getRepository('App:Label');
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

    public function create(Article $article, $labels = null)
    {
        if(!$this->validator($article)){
            return false;
        }

        $this->conn->beginTransaction();

        $article->setCreateAt(new \DateTime());
        $this->em->persist($article);

        try {

            if(!empty($labels)){
                $articleLabels = $this->articleLabelRepository->findAll(['article_id' => $article->getId()]);
                if(!empty($articleLabels)){
                    foreach ($articleLabels as $articleLabel){
                        if(!$this->deleteArticleLabel($articleLabel)){
                            throw new \Exception();
                        }
                    }
                }

                foreach ($labels as $k => $v){
                    $label = $this->labelRepository->find($labels[$k]);
                    if(!empty($label)){
                        $articleLabel = new ArticleLabel();
                        $articleLabel->setLabel($label);
                        $articleLabel->setArticle($article);
                        if(!$this->articleLabel($articleLabel, false)){
                            throw new \Exception();
                        }
                    }
                }
            }

            $this->em->flush();
            $this->em->clear();

            $this->conn->commit();
            return true;
        }catch (\Exception $exception){
            $this->conn->rollBack();
            $this->networkError($exception);
            return false;
        }
    }

    public function update(Article $article, $labels = null)
    {
        if(!$this->validator($article)){
            return false;
        }

        $this->conn->beginTransaction();

        $article->setUpdateAt(new \DateTime());

        try {

            if(!empty($labels)){
                $articleLabels = $this->articleLabelRepository->findAll(['article_id' => $article->getId()]);

                if(!empty($articleLabels)){
                    foreach ($articleLabels as $articleLabel){
                        if(!$this->deleteArticleLabel($articleLabel)){
                            throw new \Exception();
                        }
                    }
                }

                foreach ($labels as $k => $v){
                    $label = $this->labelRepository->find($labels[$k]);
                    if(!empty($label)){
                        $articleLabel = new ArticleLabel();
                        $articleLabel->setLabel($label);
                        $articleLabel->setArticle($article);
                        if(!$this->articleLabel($articleLabel, false)){
                            throw new \Exception();
                        }
                    }
                }
            }

            $this->em->persist($article);

            $this->em->flush();
            $this->em->clear();

            $this->conn->commit();

            return true;
        }catch (\Exception $exception){
            $this->conn->rollBack();
            $this->networkError($exception);
            return false;
        }
    }

    public function articleLabel(ArticleLabel $articleLabel, $is_flush = true)
    {
        try {

            $this->em->persist($articleLabel);

            if($is_flush) {
                $this->em->flush();
                $this->em->clear();
            }

            return true;

        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }

    public function deleteArticleLabel(ArticleLabel $articleLabel)
    {
        try {
            $this->em->flush();
            $this->em->remove($articleLabel);

            return true;
        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }
}