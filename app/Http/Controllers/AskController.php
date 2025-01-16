<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AskController extends Controller
{
    public function index()
    {
        $models = (new ChatService())->getModels();
        $selectedModel = ChatService::DEFAULT_MODEL;
        $conversations = Conversation::where('user_id', auth()->id())->get();

        return Inertia::render('Ask/Index', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => auth()->user(),
            'conversations' => $conversations,
        ]);
    }

    public function show(Conversation $conversation)
    {

        $models = (new ChatService())->getModels();
        $selectedModel = ChatService::DEFAULT_MODEL;
        $conversations = Conversation::where('user_id', auth()->id())->get();

        $conversation->load('messages');
        return Inertia::render('Ask/Show', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => auth()->user(),
            'messages' => $conversation->messages,
            'conversations' => $conversations,
            'conversation' => $conversation,
        ]);
    }

    public function ask(Request $request)
    {



        $request->validate([
            'message' => 'required|string',
            'model' => 'required|string',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        try {
            //save the message in the database
            if ($request->conversation_id) {
                //create de user message
                Message::create([
                    'role' => 'user',
                    'content' => $request->message,
                    'conversation_id' => $request->conversation_id,
                ]);

                // take history of the conversation
                $conversation = Conversation::find($request->conversation_id);
                $messages = $conversation->messages()
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(fn($msg) => [
                        'role'    => $msg->role,
                        'content' => $msg->content,
                    ])
                    ->toArray();

                $response = (new ChatService())->sendMessage(
                    messages: $messages,
                    model: $request->model
                );

                //create de AI message
                Message::create([
                    'role' => 'assistant',
                    'content' => $response,
                    'conversation_id' => $request->conversation_id,
                ]);

                //update the user's current_llm
                auth()->user()->update(['current_llm' => $request->model]);

                return redirect()->back()->with([
                    'message' => $response,
                    'conversationId' => $request->conversation_id,
                ]);
            } else {

                $messages = [[
                    'role' => 'user',
                    'content' => $request->message,
                ]];

                $response = (new ChatService())->sendMessage(
                    messages: $messages,
                    model: $request->model
                );

                //update the user's current_llm
                auth()->user()->update(['current_llm' => $request->model]);

                //create the conversation
                $conversation = Conversation::create([
                    'user_id' => auth()->id(),
                    'title' => $request->message,
                ]);

                //create de user message
                Message::create([
                    'role' => 'user',
                    'content' => $request->message,
                    'conversation_id' => $conversation->id,
                ]);

                //create de AI message
                Message::create([
                    'role' => 'assistant',
                    'content' => $response,
                    'conversation_id' => $conversation->id,
                ]);

                // return redirect()->back()->with([
                //     'message' => $response,
                //     'conversationId' => $conversation->id,
                // ]);

                return redirect()->route('ask.show', $conversation->id);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}
