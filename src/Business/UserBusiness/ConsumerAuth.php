<?php
/**
 * 用户业务层
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/15
 * Time: 10:55 上午
 */

namespace App\Business\UserBusiness;

use App\Business\AuthBusiness\SubjectAuthInterface;
use App\Business\AuthBusiness\UserAuthBusiness;
use App\Entity\User;
use App\Entity\UserAuth;
use App\Repository\UserRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use PHPZlc\Validate\Validate;
use Psr\Container\ContainerInterface;

class ConsumerAuth extends AbstractBusiness implements SubjectAuthInterface
{
    /**
     * @var UserRepository
     */
    public $userRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->userRepository = $this->getDoctrine()->getRepository('App:User');
    }

    /**
     * 验证器
     *
     * @param $class
     * @return bool
     */
    public function validator($class): bool
    {
        if(parent::validator($class)){
            if($class instanceof User){
                $user = $this->userRepository->findAssoc(['user_name' => $class->getUserName()]);
                if(!empty($user) && $user->getId() != $class->getId()){
                    Errors::setErrorMessage('用户名已经存在'); return false;
                }
            }

            return true;
        }

        return false;
    }

    public function user($rules)
    {
        return $this->userRepository->findOneBy($rules);
    }

    public function checkStatus($user)
    {
        if($user->getisDisable() == 1){
            Errors::setErrorMessage('帐号已被禁用');
            return false;
        }

        return true;
    }

    /**
     * 创建
     *
     * @param User $user
     * @param $password
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function create(User $user, $password)
    {
        if(!$this->validator($user)){
            return false;
        }

        $user->setCreateAt(new \DateTime());

        $this->conn->beginTransaction();

        try {
            $this->em->persist($user);

            $userAuth = new UserAuth();
            $userAuthBusiness = new UserAuthBusiness($this->container);

            $salt = $userAuthBusiness->generateSalt(4);

            $userAuth->setSubjectType($this->getParameter('subject_user'));
            $userAuth->setSalt($salt);
            $userAuth->setPassword($password);
            $userAuth->setSubjectId($user->getId());

            $user->setUserAuth($userAuth);

            if(!$userAuthBusiness->create($userAuth)){
                throw new \Exception();
            }

            $this->conn->commit();
            return true;
        }catch (\Exception $exception){
            $this->conn->rollBack();
            $this->networkError($exception);
            return false;
        }
    }

    /**
     * 更新
     *
     * @param User $user
     * @param $password
     * @return bool
     */
    public function update(User $user, $password)
    {
        if(!$this->validator($user)){
            return false;
        }

        if(!empty($password)){
            if(UserAuthBusiness::encryptPassword($password, $user->getUserAuth()->getSalt()) !== $user->getUserAuth()->getPassword()){
                $useAuth = $user->getUserAuth();
                if(!Validate::isPassword($password)){
                    Errors::setErrorMessage('密码格式不正确，请输入6-20位无特殊字符密码'); return false;
                }

                $useAuth->setPassword($password);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return true;

    }
}