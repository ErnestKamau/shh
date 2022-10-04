<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\User;
use App\ChatMessage;
use App\Conversation;
use App\RequestEntityItem;
use App\Supplier;
use App\SupplierQuote;
use Illuminate\Http\Request;
use Response;

class ChatMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
   
    
    public function add(Request $request){
        $chat = new ChatMessage();
        $chat->from_user_id = auth()->user()->id;
        $chat->to_user_id = $request->to_user;
        $chat->company_id = auth()->user()->company_id;
        $chat->message = $request->chat;
        $chat->is_new = 1;

        $convotry = Conversation::where('from_user_id',auth()->user()->id)->where('to_user_id',$request->to_user)->get();
        $convocheck = Conversation::where('from_user_id',$request->to_user)->where('to_user_id',auth()->user()->id)->get();
        if(isset($convotry[0]->id)){
            $chat->conversation_id = $convotry[0]->id;
        }elseif(isset($convocheck[0]->id)){
            $chat->conversation_id = $convocheck[0]->id;
        }else{
            // return response()->json($chat->conversation_id, 200);
            $convo = new Conversation();
            $convo->from_user_id = auth()->user()->id;
            $convo->to_user_id = $request->to_user;
            $convo->company_id = auth()->user()->company_id;
            $convo->save();
            $chat->conversation_id = $convo->id;
        }
        

        $chat->save();
        $from = getUserById($chat->from_user_id);              
        $chat['from_user_name'] = $from->name;
        $chat['created_date'] = date("m/d/Y h:i:s",strtotime($chat->created_at));
        $chat['current_user_id'] = auth()->user()->id;

        return Response::json($chat);

    }
    public function userChat(Request $request){
        $user = auth()->user();
        $usernow = getUserById($request->to_user_id);
        $convo_try =Conversation::where('from_user_id',auth()->user()->id)->where('to_user_id',$usernow->id)->get();
        $convocheck = Conversation::where('from_user_id',$usernow->id)->where('to_user_id',auth()->user()->id)->get();
        
        if(isset($convo_try[0]->id)){
            $convo = $convo_try[0]->id;
            
        }elseif(isset($convocheck[0]->id)){
            $convo = $convocheck[0]->id;
        }else{
            $convo = 0;
        }
        // return response()->json($convo, 200);
        
        if($convo > 0){

            $chats = ChatMessage::where('conversation_id',$convo)->orderBy('id','asc')->get();
            foreach($chats as $chat){
                if($chat->to_user_id == auth()->user()->id){
                    $chat->is_new = 0;
                };
                $from = getUserById($chat->from_user_id);
                $chat->save();
                $chat['from_user_name'] = $from->name;
                $chat['created_date'] = date("m/d/Y h:i:s",strtotime($chat->created_at));
                $chat['current_user_id'] = auth()->user()->id;
            }
        }else{
            $chats = array();
        };
        // return response()->json($chats,200);
        return Response::json($chats);
        
    }
    public function userChat_supplier($id){
        $supplier = User::find(auth()->user()->id);
        $usernow = getUserById($id);
        $convo_try =Conversation::where('from_user_id',auth()->user()->id)->where('to_user_id',$usernow->id)->pluck('id');
        $convocheck = Conversation::where('from_user_id',$usernow->id)->where('to_user_id',auth()->user()->id)->pluck('id');
        if(isset($convo_try[0])){
            $convo = $convo_try[0];
            
        }elseif(isset($convocheck[0])){
            $convo = $convocheck[0];    
        }elseif(isset($convocheck[0])){
            $convo = $convocheck[0];
        }else{
            $convo = 0;
        }
        // return response()->json($convo, 200);
        
        if($convo > 0){

            $chats = ChatMessage::where('conversation_id',$convo)->get();
            foreach($chats as $chat){
                $chat->is_new = 0;
                $chat->save();
            }
        }else{
            $chats = array();
        }
        $login_users = User::where('is_client',0)->where('id','!=',auth()->user()->id)->get();
       
        return view('layouts.inventory.suppliers.chat',compact('login_users','chats','supplier','usernow'));
        
    }
    
    public function getRequestItems($id){

        $rfqs_items = RequestEntityItem::where('request_id',$id)->get();
        $supplier = Supplier::find(auth()->user()->supplier_id);
        $current = getRfqsById($id);
        return view('layouts.inventory.suppliers.dashboard.rfqs_items',compact('rfqs_items','supplier','current'));
    }
    public function getSingleRequestItem($id){
        $request_item = RequestEntityItem::find($id);
        $current = getRfqsById($request_item->request_id);
        $supplier = auth()->user();
        $quote = SupplierQuote::where('supplier_id',auth()->user()->supplier_id)->where('request_item_id',$request_item->id)->get();
       
        return view('layouts.inventory.suppliers.dashboard.quote',compact('request_item','current','supplier','quote'));
    }
    public function addSupplierQuote(Request $request,$id){
        if($request->request_item_id == $id){
            $new_quote = new SupplierQuote();
            $new_quote->quote_amount = $request->amount;
            $new_quote->request_id = $request->request_id;
            $new_quote->request_item_id = $request->request_item_id;
            $new_quote->supplier_id = auth()->user()->supplier_id;
            $new_quote->registered_by = auth()->user()->id;
            $new_quote->save();

            return redirect()->back()->with('success','Supplier quote added successfully!');
        }
    }
    
}
