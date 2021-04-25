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

use App\Business\AuthBusiness\AuthTag;
use App\Business\AuthBusiness\UserAuthBusiness;
use App\Business\PlatformBusiness\PlatformClass;
use App\Business\UserBusiness\ConsumerAuth;
use App\Entity\Collection;
use App\Entity\Commentary;
use App\Entity\User;
use App\Repository\ArticleLabelRepository;
use App\Repository\ArticleRepository;
use App\Repository\LabelRepository;
use App\Repository\SortRepository;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BlogController extends BlogBaseController
{

    protected $page_tag;
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
        $this->page_tag = "blog_index";

        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $articles_numbers = $this->articleRepository->findCount();
        $articles = $this->articleRepository->findAll([
            Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
            'userAuth' . Rule::RA_JOIN => array(
                'alias' => 'ua'
            ),
            'is_del' => 0
        ]);
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
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function types(Request $request)
    {
        $this->page_tag = "blog_types";

        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $sorts = $this->sortRepository->findAll([Rule::R_SELECT => 'sql_pre.*, sql_pre.articles_numbers' ,'is_del' => 0]);

        if(empty($id)) {
            $articles = $this->articleRepository->findAll([
                Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
                'userAuth' . Rule::RA_JOIN => array(
                    'alias' => 'ua'
                ),
                'is_del' => 0
            ]);
        }else{
            $articles = $this->articleRepository->findAll([
                Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
                'userAuth' . Rule::RA_JOIN => array(
                    'alias' => 'ua'
                ),
                'is_del' => 0,
                'sort_id' => $id
            ]);
        }

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
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function tags(Request $request)
    {
        $this->page_tag = "blog_tags";

        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $count = $this->labelRepository->findCount(['is_del' => 0]);

        $labels = $this->labelRepository->findAll([ Rule::R_SELECT => 'sql_pre.*, sql_pre.article_numbers',
            'is_del' => 0]);

        if(empty($id)) {
            $articles = $this->articleRepository->findAll([
                Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
                'userAuth' . Rule::RA_JOIN => array(
                    'alias' => 'ua'
                ),
                'is_del' => 0
            ]);
        }else{

            $articleLabels = $this->articleLabelRepository->findAll(['label_id' => $id]);

            if(!empty($articleLabels)) {
                $article_ids = [];
                foreach ($articleLabels as $articleLabel) {
                    $article_ids[] = $articleLabel->getArticle()->getId();
                }

                $new_array = array_map(function ($value) {
                    return '\'' . $value . '\'';
                }, $article_ids);

                $article_string = join(',', $new_array);

                $rules = [
                    Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
                    'userAuth' . Rule::RA_JOIN => array(
                        'alias' => 'ua'
                    ),
                    'is_del' => 0,
                    Rule::R_WHERE => "AND sql_pre.id in ({$article_string})"
                ];
            }else{
                $rules = [
                    Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
                    'userAuth' . Rule::RA_JOIN => array(
                        'alias' => 'ua'
                    ),
                    'is_del' => 0,
                ];
            }

            $articles = $this->articleRepository->findAll($rules);
        }

        return $this->render('blog/tags.html.twig', array(
            'count' => $count,
            'labels' => $labels,
            'articles' => $articles
        ));
    }

    /**
     * 博客详情
     *
     * @param Request $request
     * @return bool|Response
     */
    public function blog(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $article = $this->articleRepository->findAssoc([
            Rule::R_SELECT => 'sql_pre.*, sql_pre.labels, ua.subject_name',
            'id' => $id,
            'userAuth' . Rule::RA_JOIN => array(
                'alias' => 'ua'
            ),
        ]);
        $article->setViews($article->getViews() + 1);
        $commentary = $this->getDoctrine()->getRepository('App:Commentary')->findAll([
            'article_id' => $id,
            'is_del' => 0,
            'user' . Rule::RA_JOIN => array(
                'alias' => 'u'
            ),
            Rule::R_SELECT => 'sql_pre.*, u.subject_name'
        ]);

        $this->getDoctrine()->getManager()->flush();

        return $this->render('blog/blog.html.twig', array(
            'article' => $article,
            'commentaries' => $commentary
        ));
    }

    /**
     * 关于我
     *
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function about()
    {
        $this->page_tag = "blog_about";

        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $user = $this->getDoctrine()->getRepository('App:User')->find($this->curUserAuth->getSubjectId());

        return $this->render('blog/about.html.twig', array(
            'user' => $user
        ));
    }

    /**
     * 登录页面
     *
     * @return bool|Response
     */
    public function loginPage()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        return $this->render('blog/login.html.twig');
    }

    /**
     * 登录
     *
     * @param Request $request
     * @return bool|JsonResponse
     * @throws \Exception
     */
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

    /**
     * 注册页面
     *
     * @return bool|Response
     */
    public function registerPage()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        return $this->render('blog/register.html.twig');
    }

    /**
     * 注册
     *
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function register(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, false);
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

    /**
     * 收藏
     *
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function collection(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $collection = $this->getDoctrine()->getRepository('App:Collection')->findAssoc(['article_id' => $id, 'user_auth_id' => $this->curUserAuth->getId() ]);

        if(!empty($collection)){
            return Responses::error('已被收藏');
        }

        $collection = new Collection();
        $collection->setArticle($this->articleRepository->find($id));
        $collection->setUserAuth($this->curUserAuth);
        $collection->setCreateAt(new \DateTime());

        $this->getDoctrine()->getManager()->persist($collection);
        $this->getDoctrine()->getManager()->flush();

        return Responses::success('收藏成功');
    }

    /**
     * 评论
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function comment(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }
        $id = $request->get('id');
        $content = $request->get('content');

        $commentary = new Commentary();
        $commentary->setArticle($this->articleRepository->find($id));
        $commentary->setUser($this->curUserAuth);
        $commentary->setContent($content);
        $commentary->setCreatAt(new \DateTime());

        $this->getDoctrine()->getManager()->persist($commentary);
        $this->getDoctrine()->getManager()->flush();

        $comments = $this->getDoctrine()->getRepository('App:Commentary')->findAll([
            'article_id' => $id,
            'is_del' => 0,
            'user' . Rule::RA_JOIN => array(
                'alias' => 'u'
            ),
            Rule::R_SELECT => 'sql_pre.*, u.subject_name'
        ]);

        $comments = $this->getDoctrine()->getRepository('App:Commentary')->arraySerialization($comments, ['level' => 1]);

        return Responses::success('评论成功', array(
            'comments' => $comments
        ));
    }

    /**
     * 退出登录
     *
     * @return bool|JsonResponse|RedirectResponse
     * @throws \Exception
     */
    public function loginOut()
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        AuthTag::remove($this->container);

        return $this->redirect($this->generateUrl('blog_index'));
    }

    /**
     * 博客修改密码
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     * @throws \Exception
     */
    public function editPassword(Request $request)
    {
        $r = $this->inlet($request->getMethod() == 'GET'?self::RETURN_SHOW_RESOURCE:self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        if($request->getMethod() == 'GET'){
            return $this->render('blog/editPassword.html.twig');
        }else{
            $old_password = $request->get('oldPassword');
            $new_password = $request->get('newPassword');

            $userAuthBusiness = new UserAuthBusiness($this->container);

            if(!$userAuthBusiness->changePassword($this->curUserAuth, $old_password, $new_password)){
                return Responses::error(Errors::getError());
            }else{
                AuthTag::remove($this->container);
                return Responses::success('密码修改成功');
            }
        }
    }

}