<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Zuri\Expense;

class ExpenseController extends Controller
{
    private $model;
    private $request;

    public function __construct(Expense $model, Request $request)
    {
        $this->model = $model;
        $this->request = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if( $this->model->validateShow($request->all() ) ) {
            $params["plugin_id"] = $request->plugin_id;
            $params["organization_id"] = $request->organization_id;
            $query = [
                "room_id" => $request->room_id,
            ];

            $expense =  $this->model->all($params, $query);
            return response()->json(['status' => 'expenses retrieved successfully', 'data' => $expense], 200);
        }else{
            $errors = $this->model->errors();
            return response()->json(['status' => 'error', 'message' => $errors], 422); 
       }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       if($this->model->validate($request->all()) ){
            $total = 0;
            if($request->items){
                foreach ($request->items as $item ) {
                   $total+=$item["unit_price"] * $item["quantity"];
                }  
            }
            
            $data["plugin_id"] = $request->plugin_id;
            $data["organization_id"] = $request->organization_id;
            $data["collection_name"] = "expenses_list_collection";
            $data["bulk_write"]=false;
            $data["object_id"]="";
            $data["filter"] =json_decode("{}");
            $data["payload"] = [
                    "title" => $request->title,
                    "description" => $request->description,
                    "total" =>$total,
                    "items" => $request->items,
                    "status" => "pending",
                    "room_id" => $request->room_id,
                    "author_id" => $request->author_id,
                    "admin_comment" =>"",
                    "created_at" => time()
            ];
            try {
                $expense = $this->model->create($data);
                return response()->json(['status' => 'created successfully', 'data' => $expense], 201); 
            } catch (Exception $e) {
                return $e;
            }
        }else{
            $errors = $this->model->errors();
            return response()->json(['status' => 'error', 'message' => $errors], 422); 
       }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
       
        $params["plugin_id"] = $request->plugin_id;
        $params["organization_id"] = $request->organization_id;
        $query = [
            "room_id" => $request->room_id,
            "_id" =>$id
        ];

        $expense = $this->model->find($params, $query);
        return response()->json(['status' => 'expense retrieved successfully', 'data' => $expense], 200); 
        

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
       
        $query = [
            "room_id" => $request->room_id,
        ];


        $params["plugin_id"] = $request->plugin_id;
        $params["organization_id"] = $request->organization_id;

        if( array_key_exists('title', $request->filter) ){
            $query['title'] =   str_replace(' ', '%20', $request->filter['title']); 
        }
        if( array_key_exists('description', $request->filter) ){
            $query['description'] =   str_replace(' ', '%20', $request->filter['description']); 
        }

        if( array_key_exists('author_id', $request->filter) ){
            $query['author_id'] =  $request->filter['author_id']; 
        }
        if( array_key_exists('status', $request->filter) ){
            $query['status'] =  $request->filter['status']; 
        }

        $expense = $this->model->find($params, $query);
        return response()->json(['status' => 'expense retrieved successfully', 'data' => $expense], 200); 
        

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if($this->model->validateUpdate($request->all()) ){
            $total = 0;
            if($request->items){
                foreach ($request->items as $item ) {
                   $total+=$item["unit_price"] * $item["quantity"];
                }  
            }
            
            $data["plugin_id"] = $request->plugin_id;
            $data["organization_id"] = $request->organization_id;
            $data["bulk_write"]= $request->filter ? true  : false;
            $data["object_id"]= $request->object_id ? $request->object_id  : "";
            $data["filter"] = $request->filter ? $request->filter  : json_decode("{}");
            $data["payload"] = [
                    "title" => $request->title,
                    "description" => $request->description,
                    "total" =>$total,
                    "items" => $request->items,
                    "room_id" => $request->room_id,
                    "author_id" => $request->author_id,
                    "updated_at" => time(),
            ];
            // return $data;
            try {
                $expense = $this->model->save($data);
                return response()->json(['status' => 'expense list updated successfully', 'data' => $expense], 201); 
            } catch (Exception $e) {
                return $e;
            }
        }else{
            $errors = $this->model->errors();
            return response()->json(['status' => 'error', 'message' => $errors], 422); 
       }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {   
        if($this->model->validateDelete($request->all()) ){
            $params["plugin_id"] = $request->plugin_id;
            $params["organization_id"] = $request->organization_id;
            $params["bulk_delete"]= $request->filter ? true  : false;
            $params["object_id"]= $request->object_id ? $request->object_id  : "";
            $params["filter"] = $request->filter ? $request->filter  : json_decode("{}");

            $expense = $this->model->delete($params);
            return response()->json(['status' => 'expense list deleted successfully', 'data' => $expense], 201); 
        }else{
            $errors = $this->model->errors();
            return response()->json(['status' => 'error', 'message' => $errors], 422); 
        }
    }
}
