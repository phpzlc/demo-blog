<?php
/**
 * 博客
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/3/26
 * Time: 6:00 下午
 */

namespace App\Controller\Blog;

use App\Repository\ArticleLabelRepository;
use App\Repository\ArticleRepository;
use App\Repository\LabelRepository;
use App\Repository\SortRepository;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends SystemBaseController
{
    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var LabelRepository
     */
    protected $labelRepository;

    /**
     * @var ArticleLabelRepository
     */
    protected $articleLabelRepository;

    /**
     * @var SortRepository
     */
    protected $sortRepository;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->articleRepository = $this->getDoctrine()->getRepository('App:Article');
        $this->labelRepository = $this->getDoctrine()->getRepository('App:Label');
        $this->articleLabelRepository = $this->getDoctrine()->getRepository('App:ArticleLabel');
        $this->sortRepository = $this->getDoctrine()->getRepository('App:Sort');

        return true;
    }

    /**
     * 首页
     *
     * @return bool|Response
     */
    public function index()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $articles_numbers = $this->articleRepository->findCount();
        $articles = $this->articleRepository->findAll();
        $labels = $this->labelRepository->findAll([
            Rule::R_SELECT => 'sql_pre.*, sql_pre.article_numbers',
            'is_del' => 0
        ]);

        return $this->render('blog/index.html.twig', array(
            'articles_numbers' => $articles_numbers,
            'labels' => $labels,
            'articles' => $articles,
            'sorts' => $this->sortRepository->findAll(['is_del' => 0]),
            'new_articles' => $this->articleRepository->findLimitAll(10, 1, ['is_del' => 0])
        ));
    }

    /**
     * 标签页
     *
     * @return Response
     */
    public function types()
    {
        return $this->render('blog/types.html.twig');
    }

    public function tags()
    {
        return $this->render('blog/tags.html.twig');
    }

    public function archives()
    {
        return $this->render('blog/archives.html.twig');
    }

    public function about()
    {
        return $this->render('blog/about.html.twig');
    }

    public function blog()
    {
        return $this->render('blog/blog.html.twig');
    }
}