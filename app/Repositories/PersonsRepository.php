<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\PersonsRepository;
use App\Criteria\PersonCriteria;

use App\Validators\PersonsValidator;

/**
 * Class PersonsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class PersonsRepository extends BaseRepository implements PersonsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return \App\Models\Person::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria( PersonCriteria::class );
    }
    
}
