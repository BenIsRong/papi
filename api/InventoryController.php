<?php
include_once('./db.php');
use Controller as Controller;

class InventoryController extends Controller{

    public function create(){
        if($this->checkToken()){
            $result = $this->insertInto("inventories", [
                "name" => $_POST['name'],
                "cost" => $_POST['cost'],
                "price" => $_POST['price'],
                "stock" => $_POST['stock'],
                "sold" => $_POST['sold'],
            ]);

            if($result){
                $this->response(201, ["message" => "created inventory successfully"]);
            }else{
                $this->response(422, ["message" => "unable to create inventory"]);
            }
        }else{
            $this->response(404);
        }
    }

    public function update(){
        parse_str(parse_url($_SERVER["REQUEST_URI"])["query"], $params);

        if($this->checkToken()){
            $result = $this->updateInto("inventories", [
                "name" => $params['name'],
                "cost" => $params['cost'],
                "price" => $params['price'],
                "stock" => $params['stock'],
                "sold" => $params['sold'],
            ], [
                "id" => $params['id'],
            ]);

            if($result){
                $this->response(200, ["message" => "updated inventory successfully"]);
            }else{
                $this->response(422, ["message" => "unable to update inventory"]);
            }

        }else{
            $this->response(404);
        }
    }
}