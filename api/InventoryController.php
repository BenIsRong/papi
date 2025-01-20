<?php
include_once('./db.php');
use Controller as Controller;

$method = $_SERVER['REQUEST_METHOD'];

class InventoryController extends Controller{

    public function create(){
        if($this->checkToken()){
            $result = $this->insert("inventories", [
                "name" => $_POST['name'],
                "cost" => $_POST['cost'],
                "price" => $_POST['price'],
                "stock" => $_POST['stock'],
                "sold" => $_POST['sold'],
            ]);

            if($result){
                $this->response(200, ["message" => "created inventory successfully"]);
            }else{
                $this->response(422, ["message" => "unable to create inventory"]);
            }
        }else{
            $this->response(404);
        }
    }
}