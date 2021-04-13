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

    /**
     * @param Sort[]
     * @return array
     */
    public function sequenceSorts($sorts)
    {
        if(empty($sorts)){
            return [];
        }

        $idToPIds = array();
        $prepareSequence = array();
        $sequence = array();
        $useRootIds = array();

        /**
         * @var Sort $sort
         */
        foreach ($sorts as $key => $sort){
            $idToPIds[$sort->getId()] = $key;
            if(empty($sort->getParentSort())){
                $prepareSequence[''][] = $sort;
            }else{
                $prepareSequence[$sort->getParentSort()->getId()][] = $sort;
            }
        }

        foreach ($sorts as $key => $sort){
            $sort = $this->arrayRootSortToSort($sort, $sorts, $idToPIds);
            if(!in_array($sort->getId(), $useRootIds, true)){
                $sequence[] = array(
                    'sort' => $sort,
                    'children' => $this->sequenceGetChildren($sort, $prepareSequence)
                );
                $useRootIds[] = $sort->getId();
            }
        }

        return $sequence;
    }

    private function arrayRootSortToSort(Sort $sort, $sorts, array $idToPIds)
    {
        if(empty($sort->getParentSort()) || !array_key_exists($sort->getParentSort()->getId(), $idToPIds)){
            return $sort;
        }

        return $this->arrayRootSortToSort($sorts[$idToPIds[$sort->getParentSort()->getId()]], $sorts, $idToPIds);
    }

    private function sequenceGetChildren(Sort $parentSort, $prepareSequence)
    {
        $array = [];

        if(array_key_exists($parentSort->getId(), $prepareSequence)){
            foreach ($prepareSequence[$parentSort->getId()] as $sort){
                $array[] = array(
                    'sort' => $sort,
                    'children' => $this->sequenceGetChildren($sort, $prepareSequence)
                );
            }
        }

        return $array;
    }
}
