<?php
/**
 * 统计台
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/7
 * Time: 4:30 下午
 */

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\CollectionRepository;
use App\Repository\CommentaryRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class StatisticalStationController extends AdminController
{
    protected $page_tag = 'admin_statistical_station_index';

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var CommentaryRepository
     */
    protected $commentaryRepository;

    /**
     * @var CollectionRepository
     */
    protected $collectionRepository;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->addNavigation(new Navigation('统计台'));

        $this->userRepository = $this->getDoctrine()->getRepository('App:User');
        $this->articleRepository = $this->getDoctrine()->getRepository('App:Article');
        $this->commentaryRepository = $this->getDoctrine()->getRepository('App:Commentary');
        $this->collectionRepository = $this->getDoctrine()->getRepository('App:Collection');

        return true;
    }

    /**
     * 统计台-首页
     *
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function index()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $form_rule = " (SELECT curdate() as createdate union all SELECT date_sub(curdate(), interval 1 day) as createdate union all SELECT date_sub(curdate(), interval 2 day) as createdate
         union all SELECT date_sub(curdate(), interval 3 day) as createdate union all SELECT date_sub(curdate(), interval 4 day) as createdate
         union all SELECT date_sub(curdate(), interval 5 day) as createdate union all SELECT date_sub(curdate(), interval 6 day) as createdate
         union all SELECT date_sub(curdate(), interval 7 day) as createdate) as total";

        $this->articleRepository->sqlArray['select'] = " DATE_FORMAT(createdate,'%m-%d') as date, ifnull(COUNT(a.create_at), 0) as articles_number ";
        $this->articleRepository->sqlArray['from'] = $form_rule;

        $this->articleRepository->sqlArray['join'] = "LEFT JOIN article a ON DATE_FORMAT(a.create_at, '%Y-%m-%d')=total.createdate";
        $this->articleRepository->sqlArray['orderBy'] = "GROUP BY createdate";

        /**
         * @var Connection $conn
         */
        $conn = $this->getDoctrine()->getConnection();

        $articles = $conn->fetchAll($this->articleRepository->getSql());

        $this->userRepository->sqlArray['select'] = " DATE_FORMAT(createdate,'%m-%d') as date, ifnull(COUNT(u.create_at), 0) as users_number ";
        $this->userRepository->sqlArray['from'] = $form_rule;
        $this->userRepository->sqlArray['join'] = "LEFT JOIN user u  ON DATE_FORMAT(u.create_at,'%Y-%m-%d')=total.createdate";
        $this->userRepository->sqlArray['orderBy'] = "GROUP BY createdate";

        $users = $conn->fetchAll($this->userRepository->getSql());

        return $this->render('admin/statistical-station/index.html.twig', array(
            'users' => $this->userRepository->findCount(['is_del' => 0]),
            'articles' => $this->articleRepository->findCount(['is_del' => 0]),
            'collections' => $this->collectionRepository->findCount(),
            'commentaries' => $this->commentaryRepository->findCount(['is_del' => 0]),
            'articles_chart' => $articles,
            'users_chart' => $users
        ));
    }
}