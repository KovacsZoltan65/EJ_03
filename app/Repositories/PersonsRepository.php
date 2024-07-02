<?php

namespace App\Repositories;

use App\Criteria\PersonsCriteria;
use App\Interfaces\PersonsRepositoryInterface;
use App\Models\Person;

//use App\Validators\PersonsValidator;

/**
 * Class PersonsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class PersonsRepository extends BaseRepository implements PersonsRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Person::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria( PersonsCriteria::class );
    }
    
}
