<?php

include_once './db.php';
use Controller as Controller;

class InventoryController extends Controller
{
    public function create()
    {
        if ($this->checkToken()) {
            $result = $this->insertInto('inventories', [
                'name' => $_POST['name'],
                'cost' => $_POST['cost'],
                'price' => $_POST['price'],
                'stock' => $_POST['stock'],
                'sold' => $_POST['sold'],
            ]);

            if ($result) {
                $this->response(201, ['message' => 'created inventory successfully']);
            } else {
                $this->response(422, ['message' => 'unable to create inventory']);
            }
        } else {
            $this->response(404);
        }
    }

    public function read()
    {
        if ($this->checkToken()) {
            $result = $this->view('inventories', [
                [
                    'col' => 'name',
                    'operator' => '=',
                    'value' => 'Test One',
                ],
                [
                    'col' => 'price',
                    'operator' => '<',
                    'value' => 20,
                ],
            ]);

            if ($result) {
                $this->response(200, ['message' => 'retrieved inventories', 'data' => $result]);
            } else {
                $this->response(422, ['message' => 'unable to retrieve inventory']);
            }
        }
    }

    public function index() {}

    public function createMultiple()
    {
        if ($this->checkToken()) {
            $inventories = $_POST['inventories'];
            $keys = [];
            $data = [];
            foreach ($inventories as $inventory) {
                $keys = $keys + array_keys($inventory);
                array_push($data, array_values($inventory));
            }
            $keys = array_unique($keys);

            $result = $this->insertMultiple('inventories', $keys, $data);

            if ($result) {
                $this->response(201, ['message' => 'created inventories successfully']);
            } else {
                $this->response(422, ['message' => 'unable to create inventories']);
            }
        } else {
            $this->response(404);
        }
    }

    public function update()
    {
        if ($this->checkToken()) {
            parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $params);
            $result = $this->updateInto('inventories', [
                'name' => $params['name'],
                'cost' => $params['cost'],
                'price' => $params['price'],
                'stock' => $params['stock'],
                'sold' => $params['sold'],
            ], [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $params['id'],
                ],
            ]);

            if ($result) {
                $this->response(200, ['message' => 'updated inventory successfully']);
            } else {
                $this->response(422, ['message' => 'unable to update inventory']);
            }

        } else {
            $this->response(404);
        }
    }

    public function delete()
    {
        if ($this->checkToken()) {
            $parsed_url = parse_url($_SERVER['REQUEST_URI']);
            if (array_key_exists('query', $parsed_url)) {
                parse_str($parsed_url['query'], $params);
                $result = $this->deleteFrom('inventories', [
                    [
                        'col' => 'id',
                        'operator' => '=',
                        'value' => $params['id'],
                    ],
                ]);

                if ($result) {
                    $this->response(200, ['message' => 'inventory deleted successfully']);
                } else {
                    $this->response(422, ['message' => 'unable to delete inventory']);
                }
            } else {
                $result = $this->deleteAll('inventories');

                if ($result) {
                    $this->response(200, ['message' => 'inventories deleted successfully']);
                } else {
                    $this->response(422, ['message' => 'unable to delete inventories']);
                }
            }

        } else {
            $this->response(404);
        }
    }
}
