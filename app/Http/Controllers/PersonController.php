<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use App\Interfaces\PersonsRepositoryInterface;
use App\Models\Person;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PersonController extends Controller
{
    private $repository;
    
    /**
     * PersonController konstruktor.
     *
     * @param PersonRepositoryInterface $repository Az aldomain modell tárháza.
     */
    public function __construct(PersonsRepositoryInterface $repository) {
        // A tárolót beinjektálják, és egy osztály tulajdonságában tárolják későbbi használatra.
        $this->repository = $repository;
        
        // A PersonController ezt a tárolót fogja használni a CRUD műveletek 
        // végrehajtására az altartományi modellen.
        
        // A middleware beállítása, hogy csak akkor engedélyezze a "person list"
        // jogosultságú felhasználók számára a "index" és "show" metódusok
        // elérését, ha a felhasználó rendelkezik ezzel a jogosultsággal.
        // 
        // A "only" kulcsszó használatával csak ezeket a metódusokat szűrjük ki ebből
        // a middleware-ből, és a "can" middleware-t csak ezekre azokat alkalmazza.
        //$this->middleware('can:person list', [
        //    'only' => ['index', 'show'],
        //]);
        
        // A middleware beállítása, hogy csak akkor engedélyezze a "person create"
        // jogosultságú felhasználók számára a "create" és "store" metódusok
        // elérését, ha a felhasználó rendelkezik ezzel a jogosultsággal.
        // 
        // A "only" kulcsszó használatával csak ezeket a metódusokat szűrjük ki ebből
        // a middleware-ből, és a "can" middleware-t csak ezekre azokat alkalmazza.
        //$this->middleware('can:person create', [
        //    'only' => ['create', 'store'],
        //]);
        
        // A middleware beállítása, hogy csak akkor engedélyezze a "person edit"
        // jogosultságú felhasználók számára a "edit" és "update" metódusok
        // elérését, ha a felhasználó rendelkezik ezzel a jogosultsággal.
        // 
        // A "only" kulcsszó használatával csak ezeket a metódusokat szűrjük ki ebből
        // a middleware-ből, és a "can" middleware-t csak ezekre azokat alkalmazza.
        //$this->middleware('can:person edit', [
        //    'only' => ['edit', 'update'],
        //]);
        
        // A middleware beállítása, hogy csak akkor engedélyezze a "person delete"
        // jogosultságú felhasználók számára a "destroy" metódus elérését, ha a 
        // felhasználó rendelkezik ezzel a jogosultsággal.
        // 
        // A "only" kulcsszó használatával csak ezeket a metódusokat szűrjük ki ebből
        // a middleware-ből, és a "can" middleware-t csak ezekre azokat alkalmazza.
        //$this->middleware('can:person delete', [
        //    'only' => ['destroy'],
        //]);
        
        // A middleware beállítása, hogy csak akkor engedélyezze a "person restore"
        // jogosultságú felhasználók számára a "restore" metódus elérését, ha a 
        // felhasználó rendelkezik ezzel a jogosultsággal.
        // 
        // A "only" kulcsszó használatával csak ezeket a metódusokat szűrjük ki ebből
        // a middleware-ből, és a "can" middleware-t csak ezekre azokat alkalmazza.
        //$this->middleware('can:person restore', [
        //    'only' => ['restore'],
        //]);
    }
    
    /**
     * Jelenítse meg az erőforrás listáját.
     *
     * Ez a módszer a „Személyek/index” nézetet a „can” változóval jeleníti meg
     * a felhasználó szerepköreit tartalmazza.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // Szerezze meg a felhasználói szerepköröket
        $roles = $this->getUserRoles();
        
        // Jelenítse meg a „Személyek/Index” nézetet a „can” változóval
        return Inertia::render('Persons/Index', [
            'can' => $roles,
        ]);
    }

    public function getPersons(Request $request){
        // Beállítások
        $config = $request->get('config', []);
        // Szűrők és keresések
        $filters = $request->get('filters', []);
        
        // Szűrés kezelése
        if (count($filters) > 0) {
            // Ha van keresési paraméter, akkor...
            if (isset($filters['search'])) {
                // A keresési paramétert átteszem egy változóba
                $value = $filters['search'];

                // Keresési paraméter érvégyesítése az 'author' és 'title' mezőkre
                $this->repository->where('author', 'LIKE', "%$value%");
                $this->repository->orWhere('title', 'LIKE', "%$value%");
            }

            // ----------------
            // RENDEZÉS
            // ----------------
            // Rendezés a 'name' oszlop szerint
            $column = 'id';
            // Ha van más beállítás, akkor...
            if (isset($filters['column'])) {
                // azt állítom be
                $column = $filters['column'];
            }

            // Alap rendezési irány
            $direction = 'asc';
            // Ha van más beállítás, akkor...
            if (isset($filters['direction'])) {
                // azt állítom be
                $direction = $filters['direction'];
            }
            // Rendezés érvényesítése
            $this->repository->orderBy($column, $direction);
        }

        // Oldaltörés értékének kezelése
        $per_page = count($config) != 0 && isset($config['per_page']) ? $config['per_page'] : config('app.per_page');

        // Adatok lekérése
        $persons = $this->repository->paginate($per_page);
        
        // Adatcsomag összeállítása
        $data = [
            'persons' => $persons,
            'config' => $config,
            'filters' => $filters,
        ];
        
        // Adatcsomag visszaküldése
        return response()->json($data, Response::HTTP_OK);
    }
    
    /**
     * Mutassa meg az űrlapot az új erőforrás létrehozásához.
     *
     * Ez a módszer a „Persons/Create” nézetet jeleníti meg a „can” változóval
     * amely a felhasználó szerepköreit tartalmazza, a 'persons' változó pedig egy újat tartalmaz
     * Személy példány.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        // Szerezze meg a felhasználói szerepköröket
        $roles = $this->getUserRoles();
        $person = new Person();
        
        // Jelenítse meg a 'Persons/Create' nézetet a 'can' változóval és egy új Személy-példánnyal
        return Inertia::render('Persons/Create', [
            'can' => $roles, // Tartalmazza a felhasználó szerepköreit
            'person' => $person, // Új személy példányt tartalmaz
        ]);
    }

    /**
     * Tároljon egy újonnan létrehozott erőforrást a tárhelyen.
     *
     * Ez a metódus új személypéldányt hoz létre a következőből származó ellenőrzött adatok felhasználásával
     * kérjen le és tárolja a tárolóban. Ezt követően átirányítja a felhasználót
     * vissza az előző oldalra egy sikerüzenettel.
     *
     * @param StorePersonRequest $request Az érvényesített adatokat tartalmazó kérelem objektum
     * @return RedirectResponse
     */
    public function store(StorePersonRequest $request)
    {
        // Hozzon létre egy új Személy-példányt a kérelemben szereplő ellenőrzött adatok felhasználásával
        $this->repository->create($request->validated());
        
        // Visszairányítja a felhasználót az előző oldalra egy sikerüzenettel
        return redirect()->back()->with('message', __('persons.created'));
    }

    /**
     * Jelenítse meg a megadott erőforrást.
     *
     * Ez a metódus jelenleg nincs implementálva, és BadMethodCallException kivételt dob
     * nem implementált hibaüzenettel.
     *
     * @param string $id A megjelenítendő személy azonosítója
     * @return void
     * @throws \BadMethodCallException
     */
    public function show(string $id)
    {
        // Adjon meg egy BadMethodCallException-t egy "nem implementált" hibaüzenettel
        throw new \BadMethodCallException(__('error.not_implemented') );
    }

    /**
     * Jelenítse meg az űrlapot a megadott erőforrás szerkesztéséhez.
     *
     * @param Person $person A szerkesztendő személy
     * @return \Inertia\Response A „Persons/Edit” nézet a „can” és „person” adatokkal
     */
    public function edit(Person $person)
    {
        // Szerezze meg a felhasználói szerepköröket
        $roles = $this->getUserRoles();
        
        // Készítse elő a nézetbe továbbítandó adatokat
        $data = [
            'can' => $roles, // Tartalmazza a felhasználó szerepköreit
            'person' => $person, // A szerkesztendő személyt tartalmazza
        ];
        
        // Jelenítse meg a „Persons/Edit” nézetet az adatokkal
        return Inertia::render('Persons/Edit', $data);
    }

    /**
     * Frissítse a megadott személyt a tárhelyen.
     *
     * Ez a módszer az érvényesített adatok felhasználásával frissíti a megadott azonosítóval rendelkező személyt
     * a kérésből. Ezután a frissített JSON-választ adja vissza
     * 200-as állapotkóddal rendelkező személy.
     *
     * @param UpdatePersonRequest $request Az érvényesített adatokat tartalmazó kérelem objektum
     * @param int $id A frissítendő személy azonosítója
     * @return JsonResponse A frissített személy 200-as állapotkóddal
     */
    public function update(UpdatePersonRequest $request, int $id)
    {
        // Frissítse a megadott azonosítóval rendelkező személyt a kérelemben szereplő érvényes adatok felhasználásával
        $person = $this->repository->update($request->validated(), $id);
        
        // 200-as állapotkóddal küldjön vissza egy JSON-választ, amely tartalmazza a frissített személyt
        return response()->json($person, Response::HTTP_OK);
    }

    /**
     * Törölje a megadott személyt a tárhelyről.
     *
     * Ez a módszer törli az adott azonosítóval rendelkező személyt a tárból.
     * Ezt követően sikeres üzenettel visszairányítja a felhasználót az előző oldalra.
     *
     * @param int $id A törölni kívánt személy azonosítója
     * @return RedirectResponse
     */
    public function destroy(int $id)
    {
        // A megadott azonosítóval rendelkező személy törlése az adattárból
        $this->repository->delete($id);
        
        // Visszairányítja a felhasználót az előző oldalra egy sikerüzenettel
        return redirect()->back()->with('message', __('persons.deleted'));
    }
    
    /**
     * Helyezze vissza a megadott személyt a tárhelyből.
     *
     * Ez a módszer megpróbálja megtalálni a soft-törölt személyt a megadott azonosítóval és
     * visszaállítani. Ha a személy nem található, az átirányítás hibával tér vissza
     * üzenet. Ha bármilyen más kivétel történik a folyamat során, az csendben történik
     * figyelmen kívül hagyva. Végül egy átirányítást ad vissza egy sikerüzenettel.
     *
     * @param int $id A visszaállítandó személy azonosítója
     * @return RedirectResponse
     */
    public function restore(int $id)
    {
        try
        {
            // Keresse meg a programozottan törölt személyt a megadott azonosítóval
            $person = Person::onlyTrashed()->findOrFail($id);
            
            // Állítsa vissza a személyt
            $person->restore();

            // Átirányítás visszaküldése sikerüzenettel
            return redirect()->back()->with('message', __('books_restored'));
        }
        catch(ModelNotFoundException $e )
        {
            // Ha a személy nem található, küldjön vissza egy átirányítást hibaüzenettel
            $message = __('persons_not_found');

            return redirect()->back()->with('error', $message);
        }
        catch( \Exception $e )
        {
            // Az egyéb kivételeket figyelmen kívül hagyja
        }
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
