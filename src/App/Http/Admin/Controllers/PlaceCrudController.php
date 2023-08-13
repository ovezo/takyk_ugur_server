<?php

namespace App\Http\Admin\Controllers;

use Domain\Places\Requests\PlaceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Domain\Places\Models\Place;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * Class PlaceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PlaceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Domain\Places\Models\Place::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/place');
        CRUD::setEntityNameStrings('place', 'places');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {


        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
	$this->crud->addColumns([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name'=>'to_date',
                'label'=>'Ýagdaýy',
                'wrapper' => [
                    'element' => 'span',
                    'class' => function ($crud, $column, $entry, $related_key) {
                        if ($entry->to_date > \Carbon\Carbon::now()) {
                            return 'badge badge-success';
                        }
//                        else if ($entry->status == 'pending'){
//                            return 'badge badge-warning';
//                        }
                        return 'badge badge-danger';
                    },
                    'style'=>"font-size: 0.9rem",
                ],
            ],
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PlaceRequest::class);

        $this->crud->addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
                'tab' => 'Info'
            ],
            [
                'name' => 'address',
                'type' => 'text',
                'label' => 'Address',
                'tab' => 'Info'
            ],
            [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'Phone',
                'tab' => 'Info'
            ],
            [
                'name' => 'email',
                'type' => 'text',
                'label' => 'Email',
                'tab' => 'Info'
            ],
            [
                'name' => 'website',
                'type' => 'text',
                'label' => 'Website',
                'tab' => 'Info'
            ],
            [
                'name' => 'long',
                'type' => 'text',
                'label' => 'Long => 58...',
                'tab' => 'Info'
            ],
            [
                'name' => 'lat',
                'type' => 'text',
                'label' => 'Lat => 37...',
                'tab' => 'Info'
            ],
//            [
//                'name'  => 'location', // do not change this
//                'type'  => 'point', // do not change this
//                'label' => "Coordinates",
//                'tab'   => 'Map'
//            ],
            [  // Select
                'label' => "Category Place",
                'type' => 'select',
                'name' => 'place_category_id', // the db column for the foreign key
                'entity' => 'place_category',
                'attribute' => 'name', // foreign key attribute that is shown to user
                'tab' => 'Info',
                // optional - force the related options to be a custom query, instead of all();
                'options' => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), //  you can use this to filter the results show in the select
            ],
            [   // Upload
                'name' => 'logo',
                'label' => 'Image',
                'type' => 'upload',
                'upload' => true,
                'disk' => 'public', // if you store files in the /public folder, please omit this; if you store them in /storage or S3, please specify it;
                'tab' => 'Image'
            ],
            [   // Upload
                'name' => 'images',
                'label' => 'Photos',
                'type' => 'upload_multiple',
                'upload' => true,
                'disk' => 'public', // if you store files in the /public folder, please omit this; if you store them in /storage or S3, please specify it;
                'tab' => 'Image'
            ],
            [   // SelectMultiple = n-n relationship (with pivot table)
                'label' => "Place routes",
                'type' => 'select_multiple',
                'name' => 'placeroutes', // the method that defines the relationship in your Model

                // optional
                'entity' => 'placeroutes', // the method that defines the relationship in your Model
                'model' => "Domain\Routes\Models\Route", // foreign key model
                'attribute' => 'number', // foreign key attribute that is shown to user
                'pivot' => true, // on create&update, do you need to add/delete pivot table entries?

                // also optional
                'options' => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
                'tab' => 'Routes'
            ],
            [   // Checkbox
                'name' => 'mo',
                'label' => 'Monday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'tu',
                'label' => 'Tuesday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'we',
                'label' => 'Wednesday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'th',
                'label' => 'Thursday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'fr',
                'label' => 'Friday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'sa',
                'label' => 'Saturday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'su',
                'label' => 'Sunday',
                'type' => 'checkbox',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'time',
                'label' => 'Work time',
                'type' => 'text',
                'tab' => 'Work days'
            ],
            [   // Checkbox
                'name' => 'from_date',
                'label' => 'From date',
                'type' => 'date',
                'tab' => 'Expired date'
            ],
            [   // Checkbox
                'name' => 'to_date',
                'label' => 'To date',
                'type' => 'date',
                'tab' => 'Expired date'
            ],

        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
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

    public function store()
    {
        $req_body = $this->crud->getRequest()->all();

        $lat = $req_body['lat'];
        $lng = $req_body['long'];
        $this->crud->getRequest()->request->remove('location');

        $response = $this->traitStore();

        $entryID = $this->data['entry']->id;

        $place = Place::find($entryID);
        $place->location = new Point($lat, $lng, 4326);
        $place->save();

        return $response;
    }

    public function update()
    {
        $req_body = $this->crud->getRequest()->all();

        if ($req_body['lat'] != null || $req_body['long'] != null){

            if ($req_body['lat'] != null) {
                $lat = $req_body['lat'];
            }if ($req_body['long'] != null) {
                $lng = $req_body['long'];
            }

            $response = $this->traitUpdate();

            $entryID = $this->data['entry']->id;

            $place = Place::find($entryID);
            $place->location = new Point($lat, $lng, 4326);
            $place->save();

        }
        $response = $this->traitUpdate();

        return $response;
    }
}
