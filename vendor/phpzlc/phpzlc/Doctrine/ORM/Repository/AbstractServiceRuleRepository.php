<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/27
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Bundle\Service\DateTime\DateTime;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\ClassRuleMetaData;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\ClassRuleMetaDataFactroy;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\RuleColumn;
use PHPZlc\PHPZlc\Doctrine\ORM\SQLParser\SQLParser;
use PHPZlc\PHPZlc\Doctrine\ORM\SQLParser\SQLSelectColumn;
use PHPZlc\PHPZlc\Doctrine\ORM\Untils\SQLHandle;
use PHPZlc\PHPZlc\Doctrine\ORM\Untils\Str;
use PHPZlc\Validate\Validate;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PhpMyAdmin\SqlParser\Parser;

abstract class AbstractServiceRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);

        $this->telSqlArray['from'] = $this->getTableName();
        $this->telSqlArray['primaryKey'] = $this->getPrimaryKey();
        $this->telSqlArray['finalOrderBy'] = "sql_pre.{$this->getPrimaryKey()} DESC";

        if($this->getClassRuleMetadata()->hasRuleColumnOfColumnName('is_del')){
            $this->telSqlArray['falseDeleteField'] = 'is_del';
        }elseif($this->getClassRuleMetadata()->hasRuleColumnOfColumnName('isDel')){
            $this->telSqlArray['falseDeleteField'] = 'is_del';
        }

        $this->registerRules();
    }

    public $sqlArray = array(
        'alias' => '',
        'from' => '',
        'join' => '',
        'select' => '',
        'where' => '',
        'orderBy' => '',
        'finalOrderBy' => '',
        'primaryKey' => '',
        'aliasIncrease' => '',
        'falseDeleteField' => ''
    );

    public $telSqlArray = array(
        'alias' => 't',
        'from' => '',
        'join' => '',
        'select' => '',
        'where' => '',
        'orderBy' => '',
        'finalOrderBy' => '',
        'primaryKey' => '',
        'aliasIncrease' => 0,
        'falseDeleteField' => ''
    );

    /**
     * @var string
     */
    public $sql;

    /**
     * @var Rules
     */
    public $runRules;

    /**
     * @var ResultSetMappingBuilder
     */
    public $runResultSetMappingBuilder;

    /**
     * @var Rules
     */
    public $necessaryRules = null;

    /**
     * @var array
     */
    public $registerRules = [];

    /**
     * @var array
     */
    public $rewriteSqls = [];

##############################  ????????? start ##################################

    public function getTableName()
    {
        return $this->getClassMetadata()->getTableName();
    }

    public function getPrimaryKey()
    {
        return $this->getClassRuleMetadata()->getRuleColumnOfPropertyName($this->getClassMetadata()->getIdentifier()[0])->name;
    }

    public function setTableName()
    {
        return $this;
    }

#################################   ?????? start ##################################

    /**
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain sql_pre:a=>c,b=>a;at:a=>c,b=>a;
     */
    public function rules($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        if(empty($resultSetMappingBuilder)){
            $this->runResultSetMappingBuilder = new ResultSetMappingBuilder($this->getEntityManager());
        }else{
            $this->runResultSetMappingBuilder = clone $resultSetMappingBuilder;
        }

        if(empty($rules)){
            $this->runRules = new Rules();
        }else{
            if(!is_array($rules)) {
                $this->runRules = clone $rules;
            }else{
                $this->runRules = new Rules($rules);
            }
        }

        $this->sql = '';
        $this->sqlArray = $this->telSqlArray;
        $this->runResultSetMappingBuilder->addEntityResult($this->getClassName(), $this->sqlArray['alias']);

        //????????????
        if($this->runRules->issetRule(Rule::R_SELECT)){
            $this->sqlArray['select'] = $this->runRules->getRule(Rule::R_SELECT)->getValue();
        }else{
            $this->sqlArray['select'] = $this->getClassRuleMetadata()->getSelectSql([RuleColumn::PT_TABLE_IN, RuleColumn::PT_TYPE_TARGET]);
        }

        if($this->runRules->issetRule(Rule::R_JOIN)){
            $this->sqlArray['join'] =  $this->runRules->getRule(Rule::R_JOIN)->getValue();
        }

        if($this->runRules->issetRule(Rule::R_WHERE)){
            $this->sqlArray['where'] =  $this->runRules->getRule(Rule::R_WHERE)->getValue();
        }

        if($this->runRules->issetRule(Rule::R_ORDER_BY)){
            $this->sqlArray['orderBy'] =  $this->runRules->getRule(Rule::R_ORDER_BY)->getValue();
        }

        if(!empty($this->sqlArray['falseDeleteField'])){
            if(!$this->runRules->issetRule(Rule::R_FREED_FALSE_DEL)){
                $this->sqlArray['where'] .= " AND sql_pre.{$this->sqlArray['falseDeleteField']} = 0";
            }
        }

        //??????
        $this->process($this->runRules, $this->runResultSetMappingBuilder, $aliasChain);
    }

    /**
     * ????????????
     *
     * @return mixed
     */
    abstract public function registerRules();

    /**
     * ??????????????????
     *
     * @param $rule_suffix_name
     * @param $rule_description
     */
    final protected function registerCoverRule($rule_suffix_name, $rule_description = null)
    {
        $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($rule_suffix_name);

        if(empty($ruleColumn)) {
            $suffix_name = '';
            $ai_rule_name = '';
            foreach (Rule::getAllAIRule() as $aiRule) {
                if (strpos($rule_suffix_name, $aiRule) !== false) {
                    $suffix_name = rtrim($rule_suffix_name, $aiRule);
                    $ai_rule_name = $aiRule;
                    break;
                }
            }
            if(!empty($suffix_name)){
                $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($suffix_name);
                if(empty($ruleColumn)){
                    $this->registerRules[$rule_suffix_name] = $rule_description;
                }else {
                    $this->registerRules[$ruleColumn->propertyName . $ai_rule_name] = $rule_description;
                    $this->registerRules[$ruleColumn->name . $ai_rule_name] = $rule_description;
                }
            }else{
                $this->registerRules[$rule_suffix_name] = $rule_description;
            }
        }else{
            $this->registerRules[$ruleColumn->propertyName] = $rule_description;
            $this->registerRules[$ruleColumn->name] = $rule_description;
        }
    }

    /**
     * ??????????????????
     *
     * @param Rule $rule
     */
    final protected function registerNecessaryRule(Rule $rule)
    {
        if(empty($this->necessaryRules)){
            $this->necessaryRules = new Rules();
        }

        $this->necessaryRules->addRule($rule);
    }

    final protected function registerRewriteSql($field_name, $sql)
    {
        $this->rewriteSqls[$field_name] = $sql;
    }

    /**
     * ????????????
     *
     * @param Rule $currentRule
     * @param Rules $rules
     * @param ResultSetMappingBuilder $resultSetMappingBuilder
     * @return mixed
     */
    abstract public function ruleRewrite(Rule $currentRule, Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder);

    /**
     * ?????? ?????????????????????sql?????????????????????SQL?????????????????????
     */
    private function process(Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder, $aliasChain)
    {
        //>> ??????????????????????????????????????????  ??????????????????????????????????????????  ???????????? ???????????? > ?????????????????? > JOIN????????????
        $sqlArray = $this->sqlArray;
        $cloneResultSetMappingBuilder = clone $resultSetMappingBuilder;
        //> ??????????????????????????????SQL ????????????????????????????????????????????????????????????Rules?????????
        $this->rulesProcess($rules, $cloneResultSetMappingBuilder);
        //> SQL??????????????????????????????
        $sqlParser = new SQLParser($this->generateSql());
        //> ????????????????????????????????????????????????
        foreach ($sqlParser->getUseFieldsOFPreGrouping() as $pre => $fields){
            $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
            if(!empty($classRuleMetadata)){
                foreach ($fields as $field => $fieldParam){
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($fieldParam['column']);
                    if(!empty($ruleColumn)){
                        $rules->addRules($ruleColumn->rules);
                    }
                }
            }
        }

        //> ???????????????????????????
        foreach ($cloneResultSetMappingBuilder->aliasMap as $pre => $entity){
            $serviceRuleRepository = $this->getServiceRuleRepository($pre, $entity);
            if(!empty($serviceRuleRepository->necessaryRules)) {
                foreach ($serviceRuleRepository->necessaryRules->getRules() as $rule) {
                    if($serviceRuleRepository->sqlArray['alias'] != $this->sqlArray['alias']) {
                        $rule->editPre($serviceRuleRepository->sqlArray['alias']);
                    }
                    $rules->addRule($rule);
                }
            }
        }

        //>>????????????????????????????????????????????????
        $this->sqlArray = $sqlArray;
        $rules->isNotAddRule = true;
        unset($cloneResultSetMappingBuilder);
        unset($sqlArray);

        $this->rulesProcess($rules, $resultSetMappingBuilder);

        //>> ??????SQL ???????????????????????????????????????????????????
        //> ?????? * ??????  ???*???????????????????????? ??????????????????????????????SQL
        $isSqlParsers = false;
//        dump(($sqlParser->selectColumnsOfColumn));
//        dump($this->sqlArray);exit;
        foreach ($sqlParser->selectColumnsOfColumn as $column => $SQLSelectColumn){
            if($SQLSelectColumn->isField) {
                if (empty($SQLSelectColumn->fieldPre)) {
                    $this->sqlArray['select'] = str_replace($SQLSelectColumn->cloumn, 'sql_pre.' . $SQLSelectColumn->name, $this->sqlArray['select']);
                    $SQLSelectColumn->fieldPre = 'sql_pre';
                    $SQLSelectColumn->cloumn = 'sql_pre.' . $SQLSelectColumn->name;
                    $isSqlParsers = true;
                }

                if ($SQLSelectColumn->name == '*') {
                    $classRuleMetadata = $this->classRuleMetadataOfPre($SQLSelectColumn->fieldPre, $resultSetMappingBuilder);
                    if (!empty($classRuleMetadata)) {
                        if ($SQLSelectColumn->name == '*') {
                            //TODO ????????????
                            $this->sqlArray['select'] = str_replace($SQLSelectColumn->cloumn, $classRuleMetadata->getSelectSql([RuleColumn::PT_TYPE_TARGET, RuleColumn::PT_TABLE_IN], $SQLSelectColumn->fieldPre), $this->sqlArray['select']);
                            $isSqlParsers = true;
                        }
                    }
                }
            }else{
                //TODO ???????????????????????????????????????
            }
        }


        //??????????????????
        if($rules->issetRule(Rule::R_HIDE_SELECT)){
            $hide_select = explode(',' , $rules->getRule(Rule::R_HIDE_SELECT)->getValue());
            if(empty($hide_select)){
                throw new PHPZlcException('R_HIDE_SELECT ??????????????????');
            }else{
                foreach ($hide_select as $hide_value){
                    $hide_value = trim($hide_value);
                    if(!empty($hide_value)){
                        $hide_value_arr = explode('.' , $hide_value);
                        if(count($hide_value_arr) == 1){
                            $pre = 'sql_pre';
                            $hide = $hide_value_arr[0];
                        }else{
                            $pre = $hide_value_arr[0];
                            $hide = $hide_value_arr[1];
                        }
                        $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
                        if(!empty($classRuleMetadata)){
                            $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($hide);
                            if(empty($ruleColumn)){
                                $this->sqlArray['select'] = str_replace($pre . '.' . $hide, '', $this->sqlArray['select']);
                            }else{
                                $this->sqlArray['select'] = str_replace($pre . '.' . $ruleColumn->name, '', $this->sqlArray['select']);
                                $this->sqlArray['select'] = str_replace($pre . '.' . $ruleColumn->propertyName, '', $this->sqlArray['select']);
                            }
                        }else{
                            $this->sqlArray['select'] = str_replace($pre . '.' . $hide, '', $this->sqlArray['select']);
                        }
                        //??????????????????,??????????????????
                        $this->sqlArray['select'] = preg_replace("/,[\S\s],/",",", $this->sqlArray['select']);
                    }
                }

                $this->sqlArray['select'] = rtrim(trim($this->sqlArray['select']), ',');

                if(empty($this->sqlArray['select'])){
                    throw new PHPZlcException('R_HIDE_SELECT ????????? select ????????????');
                }
            }

            if(!$isSqlParsers){
                $isSqlParsers = true;
            }
        }

        if($isSqlParsers){
            $sqlParser = new SQLParser($this->generateSql());
            unset($isSqlParsers);
        }

        //>> ??????SQL ???????????????????????????????????????????????????
        if(
            !isset($sqlParser->selectColumns[$this->getPrimaryKey()])
            &&
            !isset($sqlParser->selectColumns[$this->getClassMetadata()->getFieldName($this->getPrimaryKey())])
        ){
            $this->sqlArray['select'] = 'sql_pre.' . $this->getPrimaryKey() . ', ' . $this->sqlArray['select'];
            $resultSetMappingBuilder->addFieldResult($this->sqlArray['alias'], $this->getPrimaryKey(), $this->getClassMetadata()->getFieldName($this->getPrimaryKey()));
        }

        //>> ???????????????????????? ?????????????????????????????????????????????
        if(empty($this->sqlArray['orderBy'])){
            $this->sqlArray['orderBy'] = $this->sqlArray['finalOrderBy'];
        }else{
            $this->sqlArray['orderBy'] .= ',' . $this->sqlArray['finalOrderBy'];
        }

        //>> ??????????????????
        /**
         * @var SQLSelectColumn[] $SQLSelectColumns
         */
        foreach ($sqlParser->getSelectColumnFieldsOFPreGrouping() as $pre => $SQLSelectColumns){
            $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
            if(!empty($classRuleMetadata)){
                foreach ($SQLSelectColumns as $SQLSelectColumn){
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($SQLSelectColumn->fieldName);
                    if(!empty($ruleColumn)) {
                        if($ruleColumn->propertyType != RuleColumn::PT_TYPE_TARGET) {
                            if($SQLSelectColumn->isAs){
                                $resultSetMappingBuilder->addFieldResult($SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre, $SQLSelectColumn->name, $ruleColumn->propertyName);
                            }else{
                                $resultSetMappingBuilder->addFieldResult($SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre, $ruleColumn->name, $ruleColumn->propertyName);
                                if($ruleColumn->propertyType == RuleColumn::PT_TABLE_OUT){
                                    $this->sqlArray['select'] = str_replace($SQLSelectColumn->cloumn, $SQLSelectColumn->cloumn .' as ' . $SQLSelectColumn->name, $this->sqlArray['select']);
                                }
                            }
                        }else{
                            if($ruleColumn->name != $this->getPrimaryKey()) {
                                $tar_pre = array_search($ruleColumn->targetEntity, $resultSetMappingBuilder->aliasMap);
                                if(isset($sqlParser->alias[$tar_pre])) {
                                    if (empty($tar_pre)) {
                                        $joinClassRuleMetadata = $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity));
                                        if ($joinClassRuleMetadata) {
                                            $tar_pre = $this->getAliasIncrease();
                                            $resultSetMappingBuilder->addJoinedEntityResult($ruleColumn->targetEntity, $tar_pre, $SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre, $ruleColumn->propertyName);
                                        }
                                    }
                                    if (!empty($tar_pre)) {
                                        $resultSetMappingBuilder->addFieldResult(
                                            $SQLSelectColumn->fieldPre == 'sql_pre' ? $tar_pre : $SQLSelectColumn->fieldPre,
                                            $SQLSelectColumn->name,
                                            $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity))->getRuleColumnOfColumnName($ruleColumn->targetName)->propertyName
                                        );
                                    }
                                }else{
                                    $resultSetMappingBuilder->addMetaResult(
                                        $SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre,
                                        $SQLSelectColumn->name,
                                        $SQLSelectColumn->fieldName,
                                        false,
                                        $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity))->getRuleColumnOfColumnName($ruleColumn->targetName)->type
                                    );
                                }
                            }else{
                                $resultSetMappingBuilder->addMetaResult(
                                    $SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre,
                                    $SQLSelectColumn->name,
                                    $SQLSelectColumn->fieldName,
                                    true,
                                    $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity))->getRuleColumnOfColumnName($ruleColumn->targetName)->type
                                );
                            }
                        }
                    }
                }
            }
        }

        //????????????
        $aliasChainParser = $this->aliasChainParser($aliasChain);
        foreach ($sqlParser->getUseFieldsOFPreGrouping() as $pre => $fields){
            $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
            if(!empty($classRuleMetadata)){
                foreach ($fields as $field => $fieldParam){
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($fieldParam['column']);
                    if(!empty($ruleColumn)){
                        foreach ($this->sqlArray as $key => $value){
                            //?????????????????????select????????????????????????select???????????????;??????????????????????????????????????????????????????????????????????????????
//                            if($key == 'orderBy' && isset($sqlParser->selectColumnsOfColumn[$field])){
//                                $this->sqlArray[$key] = str_replace($field, $sqlParser->selectColumnsOfColumn[$field]->name, $value);
//                            }else{
                            if(isset($aliasChainParser[$pre])){
                                $alias = array_merge($aliasChainParser[$pre], ['sql_pre' => $pre]);
                            }else{
                                $alias = ['sql_pre' => $pre];
                            }

                            if(array_key_exists($ruleColumn->name, $this->rewriteSqls)){
                                $this->sqlArray[$key] = str_replace($field, SQLHandle::sqlProcess($this->rewriteSqls[$ruleColumn->name], $alias), $value);
                            }elseif(array_key_exists($ruleColumn->propertyName, $this->rewriteSqls)) {
                                $this->sqlArray[$key] = str_replace($field, SQLHandle::sqlProcess($this->rewriteSqls[$ruleColumn->propertyName], $alias), $value);
                            }else{
                                $this->sqlArray[$key] = str_replace($field, $ruleColumn->getSql($alias), $value);
//                                }
                            }
                        }
                    }
                }
            }
        }

        if(!empty($this->sqlArray)){
            $this->sqlArray['orderBy'] = ' ORDER BY ' . $this->sqlArray['orderBy'];
        }
    }

    private function rulesProcess(Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        foreach ($rules->getJoinRules() as $rule) {
            $this->ruleProcess($rule, $rules, $resultSetMappingBuilder);
        }

        foreach ($rules->getNotJoinRules() as $rule){
            $this->ruleProcess($rule, $rules, $resultSetMappingBuilder);
        }
    }

    private function ruleProcess(Rule $rule, Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        if (in_array($rule->getName(), Rule::$defRule)) {
            return;
        }

        $classRuleMetadata = $this->classRuleMetadataOfPre($rule->getPre(), $resultSetMappingBuilder);

        if(empty($classRuleMetadata)){
            return;
        }

        $ServiceRuleRepository = $this->getServiceRuleRepository($rule->getPre() == 'sql_pre' ? $this->sqlArray['alias'] : $rule->getPre(), $classRuleMetadata->getClassMetadata()->getName());

        if (array_key_exists($rule->getSuffixName(), $ServiceRuleRepository->registerRules)) {
            $ServiceRuleRepository->ruleRewrite($rule, $rules, $resultSetMappingBuilder);
        } else {
            $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName());

            if (!empty($ruleColumn)) {
                //where??????
                if(Validate::isRealEmpty($rule->getValue())){
                    $ServiceRuleRepository->sqlArray['where'] .= " AND ({$ruleColumn->getSqlComment($rule->getPre())} = '' OR {$ruleColumn->getSqlComment($rule->getPre())} is NULL)";
                }else {
                    $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} = '{$rule->getValue()}' ";
                }
            } elseif (!Validate::isRealEmpty($rule->getValue())) {
                if ($this->matchStringEnd($rule->getName(), Rule::RA_CONTRAST)) {
                    //where??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_CONTRAST);
                    if (!empty($ruleColumn)) {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()[0]} '{$rule->getValue()[1]}' ";
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_CONTRAST_2)){
                    //where??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_CONTRAST_2);
                    if (!empty($ruleColumn)) {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()[0]} '{$rule->getValue()[1]}' ";
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_NOT_IN)) {
                    //where??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_NOT_IN);
                    if (!empty($ruleColumn)) {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} not in ({$rule->getValue()}) ";
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_IN)) {
                    //where??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_IN);
                    if (!empty($ruleColumn)) {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} in ({$rule->getValue()}) ";
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_IS)) {
                    //where??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_IS);
                    if (!empty($ruleColumn)) {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} is {$rule->getValue()} ";
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_LIKE)) {
                    if($rule->getValue() != '%%') {
                        //where??????
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_LIKE);
                        if (!empty($ruleColumn)) {
                            $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} LIKE '{$rule->getValue()}' ";
                        }
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_ORDER_BY)) {
                    //orderBy??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_ORDER_BY);
                    if (!empty($ruleColumn)) {
                        if (empty($ServiceRuleRepository->sqlArray['orderBy'])) {
                            $ServiceRuleRepository->sqlArray['orderBy'] = " {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()}";
                        } else {
                            $ServiceRuleRepository->sqlArray['orderBy'] .= ',' . " {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()}";
                        }
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_JOIN)) {
                    //JOIN??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_JOIN);
                    if (!empty($ruleColumn)) {
                        if($ruleColumn->isEntity){
                            $joinclassRuleMetadata = $this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity);
                            $type = isset($rule->getValue()['type']) ? $rule->getValue()['type']: ' LEFT JOIN ';
                            $tableName = isset($rule->getValue()['tableName']) ? $rule->getValue()['tableName'] : $joinclassRuleMetadata->getTableName();
                            $alias = isset($rule->getValue()['alias']) ? $rule->getValue()['alias'] : die($rule->getName() . '??????alias');
                            $on = isset($rule->getValue()['on']) ? $rule->getValue()['on'] : $ruleColumn->getSqlComment($rule->getPre()) . ' = ' . $rule->getValue()['alias'] . '.' . $ruleColumn->targetName;
                            $ServiceRuleRepository->sqlArray['join'] .= " {$type} {$tableName} AS {$alias} ON {$on} ";
                            if(!array_key_exists($alias, $resultSetMappingBuilder->aliasMap)){
                                $resultSetMappingBuilder->addJoinedEntityResult($ruleColumn->targetEntity, $alias, $rule->getPre() == 'sql_pre' ? $this->sqlArray['alias'] : $rule->getPre(), $ruleColumn->propertyName);
                            }
                        }
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_SQL)) {
                    //????????????SQL??????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_SQL);
                    if(!empty($ruleColumn)) {
                        $this->registerRewriteSql($ruleColumn->name, $rule->getValue());
                    }
                } elseif ($this->matchStringEnd($rule->getName(), Rule::RA_NOT_REAL_EMPTY)) {
                    //WHERE?????? ????????????????????????????????????
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_NOT_REAL_EMPTY);
                    if(!empty($ruleColumn)) {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} = '{$rule->getValue()}' ";
                    }
                }
            }
        }

        if($rule->getPre() !== 'sql_pre') {
            //?????????????????????????????????????????????
            if(!empty($ServiceRuleRepository->sqlArray['select'])) {
                $this->sqlArray['select'] .= $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['select']);
            }
            if(!empty($ServiceRuleRepository->sqlArray['join'])) {
                $this->sqlArray['join'] .= $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['join']);
            }
            if(!empty($ServiceRuleRepository->sqlArray['where'])) {
                $this->sqlArray['where'] .= $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['where']);
            }

            if(!empty($ServiceRuleRepository->sqlArray['orderBy'])){
                if(empty($this->sqlArray['orderBy'])){
                    $this->sqlArray['orderBy'] = $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['orderBy']);
                }else{
                    $this->sqlArray['orderBy'] .= ',' . $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['orderBy']);
                }
            }
        }
    }

    /**
     * @param string $pre
     * @param ResultSetMappingBuilder $resultSetMappingBuilder
     * @return null|ClassRuleMetaData
     */
    private function classRuleMetadataOfPre($pre = 'sql_pre', ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        if($pre == 'sql_pre'){
            return $this->getClassRuleMetadata();
        }else{
            if(array_key_exists($pre, $resultSetMappingBuilder->aliasMap)){
                return $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($resultSetMappingBuilder->aliasMap[$pre]));
            }
        }

        return null;
    }

    public function getClassRuleMetadata(ClassMetadata $classMetadata = null)
    {
        if(empty($classMetadata)){
            $classMetadata = $this->getClassMetadata();
        }

        return ClassRuleMetaDataFactroy::getClassRuleMetadata($classMetadata);
    }

    /**
     * @param string $pre
     * @param null $entityName
     * @return AbstractServiceRuleRepository
     */
    private function getServiceRuleRepository($pre = 'sql_pre', $entityName = null)
    {
        if ($pre != $this->sqlArray['alias']) {
            $ServiceRuleRepository = $this->getEntityManager()->getRepository($entityName);
            $ServiceRuleRepository->sqlArray = $ServiceRuleRepository->telSqlArray;
            $ServiceRuleRepository->sqlArray['alias'] = $pre;
        } else {
            $ServiceRuleRepository = $this;
        }

        return $ServiceRuleRepository;
    }

#################################   ?????? start ##################################

    private function generateSql()
    {
        return "SELECT {$this->sqlArray['select']} FROM {$this->sqlArray['from']} {$this->sqlArray['alias']} {$this->sqlArray['join']} WHERE 1 {$this->sqlArray['where']} {$this->sqlArray['orderBy']}";
    }

    private function getAliasIncrease()
    {
        $aliasIncrease = $this->sqlArray['alias'] . $this->sqlArray['aliasIncrease'];
        $this->sqlArray['aliasIncrease'] ++;
        return $aliasIncrease;
    }

    public function getSql($sql = null)
    {
        if(empty($sql)){
            $sql = $this->generateSql();
        }

        $this->sql = str_replace('sql_pre', $this->sqlArray['alias'], $sql);

        return $this->sql;
    }

    private function aliasChainParser($aliasChain)
    {
        $aliasChainParser = [];

        try {
            if(!empty($aliasChain)) {
                $a1 = explode(';', $aliasChain);
                foreach ($a1 as $v1) {
                    $a2 = explode(':', $v1);
                    $a3 = explode(',', $a2[1]);
                    foreach ($a3 as $v3) {
                        $a4 = explode('=>', $v3);
                        $aliasChainParser[$a2[0]][$a4[0]] = $a4[1];
                    }
                }
            }
        }catch (\Exception $exception){
            throw new PHPZlcException('aliasChain????????????'. $exception->getMessage() .'???????????????sql_pre:a=>c,b=>a;at:a=>c,b=>a');
        }

        return $aliasChainParser;
    }


#################################   ?????? Result Serialization ##################################

    final public function arraySerialization($result, $decoratorMethodParams = ['level' => 0], $decoratorMethodName = 'toArray') : array
    {
        if(empty($result)){
            return [];
        }

        if(is_object($result)){
            return $this->$decoratorMethodName($result, $decoratorMethodParams);
        }else{
            $res = [];

            foreach ($result as $key => $value){
                $res[$key] = $this->$decoratorMethodName($value, $decoratorMethodParams);
            }

            return $res;
        }
    }

    final public function toArray($entity, $params = ['level' => 0]): array
    {
        if(empty($entity)) {
            return [];
        }

        if(array_key_exists('level', $params)){
            $params['level'] --;
        }else{
            $params['level'] = 0;
        }

        foreach ($this->getClassRuleMetadata()->getAllRuleColumn() as $ruleColumn){
            $methodName = 'get'.Str::asCamelCase($ruleColumn->propertyName);
            $methodReturn = $entity->$methodName();
            if(is_object($methodReturn) && !($methodReturn instanceof \DateTime)){
                try {
                    if(empty($methodReturn)){
                        $data[$ruleColumn->propertyName] = null;
                    }elseif($params['level'] < 0) {
                        $joinRepository = $this->getEntityManager()->getRepository(get_class($methodReturn));
                        $joinMethodName = 'get'.Str::asCamelCase($joinRepository->getPrimaryKey());
                        $data[$ruleColumn->name] = $methodReturn->$joinMethodName();
                    }else{
                        $data[$ruleColumn->propertyName] = $this->getEntityManager()->getRepository(get_class($methodReturn))->toArray($methodReturn, $params);
                    }
                }catch (\Exception $exception){
                    throw $exception;
                    $data[$ruleColumn->propertyName] = $methodReturn->toArray();
                }
            }else{
                $returnValue = $entity->$methodName();

                switch ($ruleColumn->type){
                    case 'simple_array':
                    case 'json_array':
                    case 'array':
                        if(empty($returnValue)){
                            $returnValue = [];
                        }
                        break;
                    case 'boolean':
                        $returnValue = $returnValue ? 1 : 0;
                        break;
                    case 'datetime':
                        if(empty($returnValue)){
                            $returnValue = '';
                        }else {
                            $returnValue = $returnValue->format('Y-m-d H:i:s');
                        }
                        break;
                    case 'date':
                        if(empty($returnValue)){
                            $returnValue = '';
                        }else{
                            $returnValue = $returnValue->format('Y-m-d');
                        }
                        break;
                    case 'time':
                        if(empty($returnValue)){
                            $returnValue = '';
                        }else{
                            $returnValue = $returnValue->format('H:i:s');
                        }
                        break;
                    default:
                        if(Validate::isRealEmpty($returnValue)){
                            $returnValue = '';
                        }
                }

                $data[$ruleColumn->name] = $returnValue;
            }
        }

        return $data;
    }

    /**
     * ????????????
     *
     * @param Rule $currentRule
     * @param $rule_suffix_name
     * @return bool
     */
    final protected function ruleMatch(Rule $currentRule, $rule_suffix_name) : bool
    {
        if($currentRule->getSuffixName() == $rule_suffix_name){
            return true;
        }

        $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($currentRule->getSuffixName());

        if(!empty($ruleColumn)){
            if($ruleColumn->name == $rule_suffix_name ||  $ruleColumn->propertyName == $rule_suffix_name){
                return true;
            }
        }

        $suffix_name = '';
        $ai_rule_name = '';
        foreach (Rule::getAllAIRule() as $aiRule) {
            if (strpos($currentRule->getSuffixName(), $aiRule) !== false) {
                $suffix_name = rtrim($currentRule->getSuffixName(), $aiRule);
                $ai_rule_name = $aiRule;
                break;
            }
        }
        if(!empty($suffix_name)){
            $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($suffix_name);
            if(!empty($ruleColumn)) {
                if ($ruleColumn->name . $ai_rule_name == $rule_suffix_name || $ruleColumn->propertyName . $ai_rule_name == $rule_suffix_name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ?????????????????????????????????????????????
     *
     * @param string $s1
     * @param string $s2 ?????????
     * @return bool
     */
    private function matchStringEnd($s1, $s2)
    {
        if(strlen($s1) >= strlen($s2)) {
            return substr($s1, -strlen($s2)) === $s2 ? true : false;
        }else{
            return false;
        }
    }
}