<?php

namespace App\Http\Admin\Controllers;

use Domain\Routes\Models\Route;
use Domain\Routes\Requests\RouteRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use function GuzzleHttp\json_decode;

/**
 * Class RouteCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RouteCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Domain\Routes\Models\Route::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/route');
        CRUD::setEntityNameStrings('route', 'routes');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addButtonFromModelFunction('line', 'activate', 'activate', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'sort_buses', 'sortBus', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'sort_buses_back', 'sortBusBack', 'beginning');
        CRUD::column('name');
        CRUD::column('number');

        $this->crud->addButtonFromModelFunction('line', 'front_button', 'front', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'back_button', 'back', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(RouteRequest::class);

        $this->crud->addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'number',
                'label' => 'Number',
                'type' => 'number',
            ],
            [   // SelectMultiple = n-n relationship (with pivot table)
                'label'     => "Starting stops",
                'type'      => 'select_multiple',
                'name'      => 'stops', // the method that defines the relationship in your Model

                // optional
                'entity'    => 'stops', // the method that defines the relationship in your Model
                'model'     => "Domain\Stops\Models\Stop", // foreign key model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?

                // also optional
                'options'   => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            ],
            [   // SelectMultiple = n-n relationship (with pivot table)
                'label'     => "Back stops",
                'type'      => 'select_multiple',
                'name'      => 'endstops', // the method that defines the relationship in your Model

                // optional
                'entity'    => 'endstops', // the method that defines the relationship in your Model
                'model'     => "Domain\Stops\Models\Stop", // foreign key model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?

                // also optional
                'options'   => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            ],
            [   // Table
                'name'            => 'start_coords',
                'label'           => 'Starting coordinations',
                'type'            => 'table',
                'entity_singular' => 'option', // used on the "Add X" button
                'columns'         => [
                    'lat'  => 'lat',
                    'long'  => 'long',
//                    'index' => 'index',
                ],
                'max' => 800, // maximum rows allowed in the table
                'min' => 0, // minimum rows allowed in the table
            ],
            [   // Table
                'name'            => 'end_coords',
                'label'           => 'Back coordinations',
                'type'            => 'table',
                'entity_singular' => 'option', // used on the "Add X" button
                'columns'         => [
                    'lat'  => 'lat',
                    'long'  => 'long',
//                    'index' => 'index',
                ],
                'max' => 800, // maximum rows allowed in the table
                'min' => 0, // minimum rows allowed in the table
            ],
        ]);

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    //public function store()
    //{
   //    $req_body = $this->crud->getRequest()->all();

//
//        $objects = json_decode($req_body['start_coords']);
//        $objects = collect($objects);
//        $objects_back = json_decode($req_body['end_coords']);
//        $objects_back = collect($objects_back);
//
//        $points_front =
//            $objects->map(function($data) {
//                return new Point((float) $data->lat, (float)$data->long);
//            });
//
//        $objects_back =
//            $objects_back->map(function($data) {
//                return new Point((float) $data->lat, (float)$data->long);
//            });
//
//        $test = [
//            new Point(12.455363273620605, 41.90746728266806),
//            new Point(12.450309991836548, 41.906636872349075),
//            new Point(12.445632219314575, 41.90197359839437),
//            new Point(12.447413206100464, 41.90027269624499),
//            new Point(12.457906007766724, 41.90000118654431),
//            new Point(12.458517551422117, 41.90281205461268),
//            new Point(12.457584142684937, 41.903107507989986),
//            new Point(12.457734346389769, 41.905918239316286),
//            new Point(12.45572805404663, 41.90637337450963),
//            new Point(12.455363273620605, 41.90746728266806),
//        ];
//        $this->crud->getRequest()->request->add(['front_line'=> new LineString($test) ]);
//        $this->crud->getRequest()->request->add(['back_line'=> new LineString($objects_back)]);
//
//        $response = $this->traitStore();
//        return $response;

    //}


}
