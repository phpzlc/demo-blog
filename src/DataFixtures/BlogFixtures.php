<?php
/**
 * 博客内置数据
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/5/4
 * Time: 3:30 下午
 */

namespace App\DataFixtures;

use App\Business\ArticleBusiness\ArticleBusiness;
use App\Business\ClassifyBusiness\ClassifyBusiness;
use App\Business\LabelBusiness\LabelBusiness;
use App\Entity\Article;
use App\Entity\Classify;
use App\Entity\Label;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\DataCollector\DataCollectorExtension;


class BlogFixtures extends Fixture
{
    private $container;

    public function __construct(ContainerInterface $container = null )
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $label = new Label();

        $label->setName('标签1')
            ->setCreateAt(new \DateTime())
            ->setIllustrate('标签种类');

        (new LabelBusiness($this->container))->create($label);

        $classify = new Classify();

        $classify->setClassifyNo('001')
            ->setClassifyName('分类1')
            ->setCreateAt(new \DateTime());

        (new ClassifyBusiness($this->container))->create($classify);

        $article = new Article();
        $article->setTitle('文章1')
            ->setArticleSummary('这是测试使用文章，你可以去后台新建文章')
            ->setContent('<p>这是测试使用文章，你可以去后台新建文章</p>')
            ->setCreateAt(new \DateTime())
            ->setClassify($classify);

        $labels[] = $label->getId();

        (new ArticleBusiness($this->container))->create($article, $labels);
    }
}