<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Person;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Inertia;

class PersonController extends Controller
{
    private $repository;
    
    public function __construct(PersonRepositoryInterface $repository) {
        $this->repository = $repository;
        
        $this->middleware('can: list',    ['only' => ['index', 'show']]);
        $this->middleware('can: create',  ['only' => ['create', 'store']] );
        $this->middleware('can: edit',    ['only' => ['edit', 'update']]);
        $this->middleware('can: delete',  ['only' => ['destroy']]);
        $this->middleware('can: restore', ['only' => ['restore']]);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = $this->getUserRoles();
        
        return Inertia::render('Persons/Index', [
            'can' => $roles,
        ]);
    }

    public function getPersons(){}
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Persons/Create', [
            'can' => '',
            'persons' => new Person(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonRequest $request)
    {
        $this->repository->create($request->validated());
        
        return redirect()->back()->with('message', __('persons.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        throw new \BadMethodCallException(__('error.not_implemented') );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        $roles = $this->getUserRoles();
        
        $data = [
            'can' => $roles,
            'person' => $person,
        ];
        
        return Inertia::render('Persons/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonRequest $request, int $id)
    {
        $person = $this->repository->update($request->validated(), $id);
        
        return response()->json($person, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->repository->delete($id);
        
        return redirect()->back()->with('message', __('persons.deleted'));
    }
    
    public function restore(int $id)
    {
        try
        {
            $person = Person::onlyTrashed()->findOrFail($id);
        }
        catch(ModelNotFoundException $e )
        {
            $message = __('persons_not_found');
            return redirect()->back()->with('error', $message);
        }
        catch( \Exception $e )
        {
            //
        }
        
        $book->restore();
        
        return redirect()->back()->with('message', __('books_restored'));
    }
    
    /**
     * Megszerzi a jelenlegi felhasználó könyvműveletekhez kapcsolódó engedélyeit.
     *
     * Ez a metódus ellenőrzi az autentikált felhasználó képességeit egy előre 
     * meghatározott könyvvel kapcsolatos műveletek halmazához, és egy asszociatív 
     * tömböt ad vissza, ahol a kulcsok az akció neveit képviselik, az értékek pedig 
     * bool értékek, amelyek azt jelzik, hogy a felhasználónak engedélye van-e az 
     * adott művelet végrehajtására.
     *
     * Az ellenőrzött műveletek a 'list', 'create', 'edit', 'delete' és 'restore'.
     *
     * @return array Egy asszociatív tömb az engedélyekkel.
     */
    protected function getUserRoles() {
        /**
         * Egy tömb a személykezeléssel kapcsolatos engedély műveletnevekkel.
         *
         * Ez a tömb meghatározza azoknak a műveleteknek a készletét, amelyekre a 
         * felhasználó engedélyeit ellenőrizni fogjuk. 
         * A tömb minden eleme egy konkrét művelethez tartozik, amelyet könyveken 
         * belül lehet végrehajtani a rendszerben. A műveletek közé tartozik a 
         * könyvek listázása, új könyv létrehozása, meglévő könyv szerkesztése, 
         * könyv törlése és egy korábban törölt könyv helyreállítása.
         */
        $permissions = ['list', 'create', 'edit', 'delete', 'restore'];
        
        /**
         * Inicializál egy üres tömböt a felhasználó szerepeinek vagy jogosultságainak tárolására.
         *
         * Ez a tömb kulcs-érték párokkal lesz feltöltve, ahol minden kulcs egy 
         * adott engedélyt képvisel (pl. 'list', 'create', 'edit', 'delete', 'restore'), 
         * és a megfelelő érték egy logikai érték, ami jelzi, hogy az autentikált 
         * felhasználó rendelkezik-e ezzel az engedéllyel a könyvvel kapcsolatos műveletekhez.
         */
        $userRoles = [];

        
        /**
         * Végigmegy az előre meghatározott engedélyek tömbjén.
         *
         * Ez a ciklus végigmegy az `$permissions` tömbben meghatározott minden engedélyen. 
         * Általában a cikluson belül ellenőrizzük, hogy a felhasználónak van-e minden 
         * engedélye, majd az eredményt (true vagy false) hozzárendeljük az `$userRoles` 
         * tömbhöz, vagy más engedélyekkel kapcsolatos logikát hajtunk végre.
         * 
         * A cikluson belül általában olyan műveleteket hajtunk végre, mint:
         * - Ellenőrzés, hogy a jelenlegi felhasználónak van-e a konkrét engedélye.
         * - Az ellenőrzés eredményének hozzárendelése az `$userRoles` tömbhöz.
         * - Logika végrehajtása a felhasználó engedélyei alapján.
         */
        foreach ($permissions as $permission) {
            $userRoles[$permission] = Auth::user()->can('person ' . $permission);
        }

        // A jogosultságok asszociatív tömbjének visszaadása.
        return $userRoles;
    }
}
