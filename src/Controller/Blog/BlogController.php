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

use App\Business\AuthBusiness\UserAuthBusiness;
use App\Business\PlatformBusiness\PlatformClass;
use App\Business\UserBusiness\ConsumerAuth;
use App\Entity\User;
use App\Repository\ArticleLabelRepository;
use App\Repository\ArticleRepository;
use App\Repository\LabelRepository;
use App\Repository\SortRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
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
        PlatformClass::setPlatform($this->getParameter('platform_blog'));

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
            'sorts' => $this->sortRepository->findAll([Rule::R_SELECT => 'sql_pre.*, sql_pre.articles_numbers' ,'is_del' => 0]),
            'new_articles' => $this->articleRepository->findLimitAll(10, 1, ['is_del' => 0])
        ));
    }

    /**
     * 分类页
     *
     * @return bool|Response
     */
    public function types()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $sorts = $this->sortRepository->findAll([Rule::R_SELECT => 'sql_pre.*, sql_pre.articles_numbers' ,'is_del' => 0]);

        $articles = $this->articleRepository->findAll();

        $count = $this->sortRepository->findCount(['is_del' => 0]);

        return $this->render('blog/types.html.twig', array(
            'sorts' => $sorts,
            'articles' => $articles,
            'count' => $count
        ));
    }

    /**
     * 标签页
     *
     * @return bool|Response
     */
    public function tags()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $count = $this->labelRepository->findCount(['is_del' => 0]);
        $labels = $this->labelRepository->findAll([ Rule::R_SELECT => 'sql_pre.*, sql_pre.article_numbers',
            'is_del' => 0]);
        $articles = $this->articleRepository->findAll([Rule::R_SELECT => 'sql_pre.*, sql_pre.labels']);

        return $this->render('blog/tags.html.twig', array(
            'count' => $count,
            'labels' => $labels,
            'articles' => $articles
        ));
    }

    public function archives()
    {
        return $this->render('blog/archives.html.twig');
    }

    public function about()
    {
        return $this->render('blog/about.html.twig');
    }

    public function blog(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $article = $this->articleRepository->findAssoc([Rule::R_SELECT => 'sql_pre.*, sql_pre.labels','id' => $id]);

        return $this->render('blog/blog.html.twig', array(
            'article' => $article
        ));
    }

    public function loginPage()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        return $this->render('blog/login.html.twig');
    }

    public function login(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $data['account'] = $request->get('account');
        $data['password'] = $request->get('password');

        $userAuth = new UserAuthBusiness($this->container);

        if($userAuth->accountLogin($data['account'], $data['password'], $this->getParameter('subject_user'), 'user_name') === false){
            return Responses::error(Errors::getError());
        }

        return Responses::success('登录成功',['go_url' => $this->generateUrl('blog_index')]);

    }

    public function registerPage()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        return $this->render('blog/register.html.twig');
    }

    public function register(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $account = $request->get('account');
        $password = $request->get('password');

        $user = new User();
        $user->setUserName($account);

        if(!(new ConsumerAuth($this->container))->create($user, $password)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('注册成功');
    }
}