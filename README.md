# cv04 – MVC: controller, view, request, response

*(English version below)*

Teória, príklad krok za krokom a súbory na stiahnutie sú na stránke predmetu: [Týždeň 5](https://beata.stehlikova.website.tuke.sk/it/05.html). Vzorová aplikácia beží na <https://sigma.tuke.sk/beata.stehlikova/it/ukazky/cv04/>.

Čo dnes urobíš: kód z anonymných funkcií v `index.php` presunieš do controllerov v `src/Controller/`, požiadavku bude zastupovať objekt `Request`, odpoveď objekt `Response`, a zoznam hlavnej entity dostane vyhľadávanie.

## Úlohy

**Úloha 5.1: Repository cv04 z cv03 a mvc.zip.** Prijmi zadanie *cv04* ([link](https://classroom50.org/tuke-fberg-stehlikova/informacne-technologie-5k/assignments/cv04/accept?k=xzmai3oh)) a repository naklonuj do `~/web/it/cv04` podľa návodu [04 Odovzdávanie](https://beata.stehlikova.website.tuke.sk/navody/04-odovzdavanie.html), časti 2 a 3 (posledné slovo príkazu je `cv04`). Potom v termináli v Kubeflow:

```bash
cd ~/web/it
cp cv03/config.php cv03/config.example.php cv03/.gitignore cv03/.htaccess cv03/composer.json cv04/
cp -r cv03/sql cv03/src cv03/views cv04/
cd cv04
curl -O https://beata.stehlikova.website.tuke.sk/it/mvc.zip
unzip -o mvc.zip && rm mvc.zip
composer install
```

Čo sa stalo: z `cv03` sú v `cv04` všetky súbory okrem `.git/`, `vendor/` a `README.md`. `unzip -o` prepísal `index.php`, `src/Router.php` a `views/debug.php` novou verziou a vytvoril `src/Http/` a `src/Controller/`. `composer install` vytvoril `vendor/`.

Pravý klik na priečinok `cv04` → **Upload Folder**. Kontrola: `https://sigma.tuke.sk/student/meno.priezvisko/it/cv04/` ukáže úvodnú stránku s menu, `…/cv04/debug` ukáže tabuľku so siedmimi riadkami, `…/cv04/nic` ukáže stránku „Nenájdené“. Commit a push: `cv04: kostra MVC`.

**Úloha 5.2: Controller pre každý číselník.** Pre každý svoj číselník, napríklad `categories`:

1. V Explorer pravý klik na `cv04/src/Controller` → **New File…**, názov `CategoryController.php`. Obsah je na stránke predmetu v časti Pojmy, pojem Controller; zmeň názov triedy, názov modelu v `use` a v `new`.
2. V `index.php` pridaj k riadkom `use` riadok `use App\Controller\CategoryController;` a medzi route riadok `$router->get('/categories', [CategoryController::class, 'index']);`.
3. Vo `views/layout.php` pridaj do menu `<a href="<?= url('/categories') ?>">Kategórie</a>`.
4. Ulož všetky tri súbory. Kontrola: `…/cv04/categories` ukáže tabuľku záznamov, odkaz v menu na ňu vedie.

**Úloha 5.3: Controller hlavnej entity.** Vytvor `src/Controller/TvojaEntitaController.php` s metódami `index()` a `show()` podľa `RecipeController` na stránke predmetu (časť „Ako to funguje“); zatiaľ bez vyhľadávania: v `index()` použi `$model->getAll()` a `$q` vynechaj. V `index.php` pridaj `use` a dve route: `/tvoja-entita` → `index`, `/tvoja-entita/{id}` → `show`. Do menu pridaj odkaz. Kontrola: zoznam sa zobrazí, klik na číslo v stĺpci `id` otvorí detail, `…/tvoja-entita/999` ukáže stránku „Nenájdené“ a v karte Network stavový kód 404.

**Úloha 5.4: Vyhľadávanie.** Do svojho modelu hlavnej entity pridaj metódu `search()` podľa pojmu LIKE na stránke predmetu, so svojím stĺpcom názvu vo `WHERE` a so svojím `JOIN`. V `index()` controllera doplň riadky s `$q` a `search()` podľa `RecipeController`. Na začiatok `views/list.php` pridaj formulár zo stránky predmetu. Kontrola: nad zoznamom je pole a tlačidlo; po zadaní slova, ktoré je v názve niektorého záznamu, ostane v tabuľke len ten; adresa má na konci `?q=slovo`; po kliknutí na *Zrušiť* je zoznam celý.

**Úloha 5.5: Stránka o aplikácii.** Stránku `/o-aplikacii` z cv03 presuň do controllera: v `src/Controller/HomeController.php` pridaj metódu `about()`, ktorá vráti `$this->render('about', ['title' => 'O aplikácii'])`; `views/about.php` máš z cv03. V `index.php` pridaj route `/o-aplikacii` → `[HomeController::class, 'about']`. Kontrola: adresa aj odkaz v menu fungujú.

**Úloha 5.6: Odovzdanie.** Commit a push podľa návodu 04 Odovzdávanie, časť 5. Na GitHub skontroluj, že je tam `src/Http/` s dvoma súbormi, `src/Controller/` s `BaseController.php`, `HomeController.php` a tvojimi controllermi, `views/list.php` s formulárom, tvoj model so `search()`. `config.php` a `vendor/` tam nie sú.

**Úloha 5.7: Kontrola na sigma.tuke.sk.** Pravý klik na `cv04` → **Upload Folder**. Preklikaj menu: úvod, každý číselník, hlavná entita, jeden detail, vyhľadávanie, kontrola, o aplikácii. Potom over, že `…/cv04/nic` ukáže stránku „Nenájdené“, `…/cv04/src/Router.php` vráti 404 a `…/cv04/config.php` vráti 403. Do tohto `README.md` doplň tabuľku všetkých route:

| Relatívna adresa | Controller a metóda | Čo zobrazí |
|---|---|---|
| `/` | `HomeController::index` | úvodná stránka |
| … | … | … |

**Hodnotenie:** 1 bod. Aplikácia vo webovom priestore na sigma.tuke.sk má controller pre každú tabuľku, zoznam hlavnej entity má vyhľadávanie, neexistujúca adresa a neexistujúci záznam vrátia stavový kód 404. Commit a push najneskôr do nasledujúceho cvičenia.

---

# cv04 – MVC: controller, view, request, response

Theory, a step-by-step example and the files to download are on the course page: [Week 5](https://beata.stehlikova.website.tuke.sk/it/05.html). The sample application runs at <https://sigma.tuke.sk/beata.stehlikova/it/ukazky/cv04/>.

What you do today: you move the code from the anonymous functions in `index.php` into controllers in `src/Controller/`, the request is represented by a `Request` object, the response by a `Response` object, and the main entity list gets a search box.

## Tasks

**Task 5.1: Repository cv04 from cv03 and mvc.zip.** Accept the assignment *cv04* ([link](https://classroom50.org/tuke-fberg-stehlikova/informacne-technologie-5k/assignments/cv04/accept?k=xzmai3oh)) and clone the repository into `~/web/it/cv04` following the guide [04 Submitting](https://beata.stehlikova.website.tuke.sk/navody/04-odovzdavanie.html), parts 2 and 3 (the last word of the command is `cv04`). Then in the terminal in Kubeflow:

```bash
cd ~/web/it
cp cv03/config.php cv03/config.example.php cv03/.gitignore cv03/.htaccess cv03/composer.json cv04/
cp -r cv03/sql cv03/src cv03/views cv04/
cd cv04
curl -O https://beata.stehlikova.website.tuke.sk/it/mvc.zip
unzip -o mvc.zip && rm mvc.zip
composer install
```

What happened: all files from `cv03` are in `cv04` except `.git/`, `vendor/` and `README.md`. `unzip -o` replaced `index.php`, `src/Router.php` and `views/debug.php` with the new version and created `src/Http/` and `src/Controller/`. `composer install` created `vendor/`.

Right-click the folder `cv04` → **Upload Folder**. Check: `https://sigma.tuke.sk/student/meno.priezvisko/it/cv04/` shows the home page with the menu, `…/cv04/debug` shows a table with seven rows, `…/cv04/nic` shows the “Not found” page. Commit and push: `cv04: kostra MVC`.

**Task 5.2: A controller for every lookup table.** For every lookup table of yours, for example `categories`:

1. In the Explorer right-click `cv04/src/Controller` → **New File…**, name `CategoryController.php`. The content is on the course page in the part Terms, term Controller; change the class name and the model name in `use` and in `new`.
2. In `index.php` add the line `use App\Controller\CategoryController;` to the `use` lines and the line `$router->get('/categories', [CategoryController::class, 'index']);` among the routes.
3. In `views/layout.php` add `<a href="<?= url('/categories') ?>">Kategórie</a>` to the menu.
4. Save all three files. Check: `…/cv04/categories` shows the table of records, the menu link leads to it.

**Task 5.3: Main entity controller.** Create `src/Controller/YourEntityController.php` with the methods `index()` and `show()` following `RecipeController` on the course page (part “How it works”); without search for now: in `index()` use `$model->getAll()` and leave out `$q`. In `index.php` add `use` and two routes: `/your-entity` → `index`, `/your-entity/{id}` → `show`. Add a menu link. Check: the list is shown, a click on a number in the `id` column opens the detail, `…/your-entity/999` shows the “Not found” page and the Network tab shows status code 404.

**Task 5.4: Search.** Add the method `search()` to your main entity model following the term LIKE on the course page, with your title column in `WHERE` and your `JOIN`. In the controller's `index()` add the lines with `$q` and `search()` following `RecipeController`. Add the form from the course page to the beginning of `views/list.php`. Check: there is a field and a button above the list; after entering a word that is in the title of some record, only that one stays in the table; the address ends with `?q=word`; after clicking *Zrušiť* the list is complete.

**Task 5.5: About page.** Move the page `/o-aplikacii` from cv03 into a controller: in `src/Controller/HomeController.php` add the method `about()` that returns `$this->render('about', ['title' => 'O aplikácii'])`; you have `views/about.php` from cv03. In `index.php` add the route `/o-aplikacii` → `[HomeController::class, 'about']`. Check: the address and the menu link work.

**Task 5.6: Submission.** Commit and push following the guide 04 Submitting, part 5. On GitHub check that there is `src/Http/` with two files, `src/Controller/` with `BaseController.php`, `HomeController.php` and your controllers, `views/list.php` with the form, your model with `search()`. `config.php` and `vendor/` are not there.

**Task 5.7: Check on sigma.tuke.sk.** Right-click `cv04` → **Upload Folder**. Click through the menu: home, every lookup table, main entity, one detail, search, check, about. Then verify that `…/cv04/nic` shows the “Not found” page, `…/cv04/src/Router.php` returns 404 and `…/cv04/config.php` returns 403. Add a table of all routes to this `README.md`:

| Relative address | Controller and method | What it shows |
|---|---|---|
| `/` | `HomeController::index` | home page |
| … | … | … |

**Grading:** 1 point. The application in your web space on sigma.tuke.sk has a controller for every table, the main entity list has search, a non-existent address and a non-existent record return status code 404. Commit and push by the next lab at the latest.
