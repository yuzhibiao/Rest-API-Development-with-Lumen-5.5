<?php //app/Repositories/Contracts/BaseRepository.php
 
namespace App\Repositories\Contracts;
 
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
 
interface BaseRepository
{
    /**
     * find a resource by id
     *
     * @param $id
     * @return Model|null
     */
    public function findOne($id);
 
    /**
     * find a resource by criteria
     *
     * @param array $criteria
     * @return Model|null
     */
    public function findOneBy(array $criteria);
 
    /**
     * Search All resources
     *
     * @param array $searchCriteria
     * @return Collection
     */
    public function findBy(array $searchCriteria = []);
 
    /**
     * Search All resources by any values of a key
     *
     * @param string $key
     * @param array $values
     * @return Collection
     */
    public function findIn($key, array $values);
 
    /**
     * save a resource
     *
     * @param array $data
     * @return Model
     */
    public function save(array $data);
 
    /**
     * update a resource
     *
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data);
 
    /**
     * delete a resource
     *
     * @param Model $model
     * @return mixed
     */
    public function delete(Model $model);
 
    /**
     * updated records by specific key and values
     *
     * @param string $key
     * @param array $values
     * @param array $data
     * @return Collection
     */
    public function updateIn($key, array $values, array $data);
}
