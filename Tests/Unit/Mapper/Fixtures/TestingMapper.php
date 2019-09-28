<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Testing mapper.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingMapper extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_oelib_test';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = TestingModel::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'friend' => TestingMapper::class,
        'owner' => \Tx_Oelib_Mapper_FrontEndUser::class,
        'children' => TestingMapper::class,
        'related_records' => TestingMapper::class,
        'composition' => TestingChildMapper::class,
        'composition2' => TestingChildMapper::class,
        'bidirectional' => TestingMapper::class,
    ];

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['title'];

    /**
     * @var string[] the column names of an additional compound key
     */
    protected $compoundKeyParts = ['title', 'header'];

    /**
     * @var \Tx_Oelib_Model[]
     */
    protected $cachedModels = [];

    /**
     * Gets the cached models.
     *
     * This function is intended for testing whether models have been cached.
     *
     * @return \Tx_Oelib_Model[]
     */
    public function getCachedModels()
    {
        return $this->cachedModels;
    }

    /**
     * Sets the map for this mapper.
     *
     * @param \Tx_Oelib_IdentityMap $map
     *
     * @return void
     */
    public function setMap(\Tx_Oelib_IdentityMap $map)
    {
        $this->map = $map;
    }

    /**
     * Retrieves a model based on the WHERE clause given in the parameter
     * $whereClauseParts. Hidden records will be retrieved as well.
     *
     * @throws \Tx_Oelib_Exception_NotFound if there is no record in the DB
     *                                     which matches the WHERE clause
     *
     * @param string[] $whereClauseParts
     *        WHERE clause parts for the record to retrieve, each element must
     *        consist of a column name as key and a value to search for as value
     *        (will automatically get quoted), must not be empty
     *
     * @return \Tx_Oelib_Model the model
     */
    public function findSingleByWhereClause(array $whereClauseParts)
    {
        return parent::findSingleByWhereClause($whereClauseParts);
    }

    /**
     * Sets the model class name.
     *
     * @param string $className
     *        model class name, must not be empty
     *
     * @return void
     */
    public function setModelClassName($className)
    {
        $this->modelClassName = $className;
    }

    /**
     * Processes a model's data and creates any relations that are hidden within
     * it using foreign key mapping.
     *
     * @param array &$data
     *        the model data to process, might be modified
     * @param \Tx_Oelib_Model $model
     *        the model to create the relations for
     *
     * @return void
     */
    public function createRelations(array &$data, \Tx_Oelib_Model $model)
    {
        parent::createRelations($data, $model);
    }

    /**
     * Retrieves all non-deleted, non-hidden models from the DB which match the
     * given where clause.
     *
     * @param string $whereClause
     *        WHERE clause for the record to retrieve, each element must  consist of a column name as key and a value
     *     to search for as value
     *        (will automatically get quoted), may be empty
     * @param string $sorting
     *        the sorting for the found records, must be a valid DB field
     *        optionally followed by "ASC" or "DESC", may be empty
     * @param string $limit the LIMIT value ([begin,]max), may be empty
     *
     * @return \Tx_Oelib_List<<\Tx_Oelib_Model>> all models found in DB for the given where clause,
     *                       will be an empty list if no models were found
     *
     * @deprecated will be removed in oelib 4.0.0
     */
    public function findByWhereClause($whereClause = '', $sorting = '', $limit = '')
    {
        return parent::findByWhereClause($whereClause, $sorting, $limit);
    }

    /**
     * Looks up a model in the cache by key.
     *
     * When this function reports "no match", the model could still exist in the
     * database, though.
     *
     * @throws \Tx_Oelib_Exception_NotFound if there is no match in the cache yet
     *
     * @param string $key an existing key, must not be empty
     * @param string $value
     *        the value for the key of the model to find, must not be empty
     *
     * @return \Tx_Oelib_Model the cached model
     */
    public function findOneByKeyFromCache($key, $value)
    {
        return parent::findOneByKeyFromCache($key, $value);
    }

    /**
     * Caches a model by an additional compound key.
     *
     * This method needs to be overwritten in subclasses to work. However, it is recommended to use
     * cacheModelByCompoundKey instead. So this method primarily is here for backwards compatibility.
     *
     * @param \Tx_Oelib_Model $model the model to cache
     * @param string[] $data the data of the model as it is in the DB, may be empty
     *
     * @return void
     *
     * @see cacheModelByCompoundKey
     */
    protected function cacheModelByCombinedKeys(\Tx_Oelib_Model $model, array $data)
    {
        $this->cachedModels[] = $model;
    }

    /**
     * Looks up a model in the cache by compound key.
     *
     * When this function reports "no match", the model could still exist in the
     * database, though.
     *
     *
     * @param string $title
     * @param string $header
     *
     * @return \Tx_Oelib_Model the cached model
     *
     * @throws \Tx_Oelib_Exception_NotFound if there is no match in the cache yet
     */
    public function findOneByTitleAndHeader($title, $header)
    {
        $value = [];
        $value['title'] = $title;
        $value['header'] = $header;

        return $this->findOneByCompoundKeyFromCache($value);
    }
}
