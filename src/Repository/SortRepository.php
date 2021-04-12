<?php

namespace App\Repository;

use App\Entity\Sort;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\AbstractServiceEntityRepository;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;
use PHPZlc\Validate\Validate;

/**
 * @method Sort|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sort|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sort|null    findAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
 * @method Sort|null   findLastAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
 * @method Sort|null    findAssocById($id, $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
 * @method Sort[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Sort[]    findAll($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
 * @method Sort[]    findLimitAll($rows, $page = 1, $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
 */
class SortRepository extends AbstractServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sort::class);
    }

    public function registerRules()
    {
        // TODO: Implement registerRules() method.
    }

    public function ruleRewrite(Rule $currentRule, Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        // TODO: Implement ruleRewrite() method.
    }
}
